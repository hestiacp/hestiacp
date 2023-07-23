<?php

namespace League\Flysystem\Sftp;

use InvalidArgumentException;
use League\Flysystem\Adapter\AbstractFtpAdapter;
use League\Flysystem\Adapter\Polyfill\StreamedCopyTrait;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use League\Flysystem\Util;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SFTP;
use phpseclib\System\SSH\Agent;

class SftpAdapter extends AbstractFtpAdapter
{
    use StreamedCopyTrait;

    /**
     * @var SFTP
     */
    protected $connection;

    /**
     * @var int
     */
    protected $port = 22;

    /**
     * @var string
     */
    protected $hostFingerprint;

    /**
     * @var string
     */
    protected $privateKey;

    /**
     * @var bool
     */
    protected $useAgent = false;

    /**
     * @var bool
     */
    protected $usePingForConnectivityCheck = false;

    /**
     * @var Agent
     */
    private $agent;

    /**
     * @var array
     */
    protected $configurable = ['host', 'hostFingerprint', 'port', 'username', 'password', 'useAgent', 'agent', 'timeout', 'root', 'privateKey', 'passphrase', 'permPrivate', 'permPublic', 'directoryPerm', 'NetSftpConnection', 'usePingForConnectivityCheck'];

    /**
     * @var array
     */
    protected $statMap = ['mtime' => 'timestamp', 'size' => 'size'];

    /**
     * @var int
     */
    protected $directoryPerm = 0744;

    /**
     * @var string
     */
    private $passphrase;

    /**
     * Prefix a path.
     *
     * @param string $path
     *
     * @return string
     */
    protected function prefix($path)
    {
        return $this->root.ltrim($path, $this->separator);
    }

    /**
     * Set the finger print of the public key of the host you are connecting to.
     *
     * If the key does not match the server identification, the connection will
     * be aborted.
     *
     * @param string $fingerprint Example: '88:76:75:96:c1:26:7c:dd:9f:87:50:db:ac:c4:a8:7c'.
     *
     * @return $this
     */
    public function setHostFingerprint($fingerprint)
    {
        $this->hostFingerprint = $fingerprint;

        return $this;
    }

    /**
     * Set the private key (string or path to local file).
     *
     * @param string $key
     *
     * @return $this
     */
    public function setPrivateKey($key)
    {
        $this->privateKey = $key;

        return $this;
    }

    /**
     * Set the passphrase for the privatekey.
     *
     * @param string $passphrase
     *
     * @return $this
     */
    public function setPassphrase($passphrase)
    {
        $this->passphrase = $passphrase;

        return $this;
    }

    protected function setUsePingForConnectivityCheck($useIt)
    {
        $this->usePingForConnectivityCheck = $useIt;

        return $this;
    }

    /**
     * @param boolean $useAgent
     *
     * @return $this
     */
    public function setUseAgent($useAgent)
    {
        $this->useAgent = (bool) $useAgent;

        return $this;
    }

    /**
     * @param Agent $agent
     *
     * @return $this
     */
    public function setAgent(Agent $agent)
    {
        $this->agent = $agent;

        return $this;
    }

    /**
     * Set permissions for new directory
     *
     * @param int $directoryPerm
     *
     * @return $this
     */
    public function setDirectoryPerm($directoryPerm)
    {
        $this->directoryPerm = $directoryPerm;

        return $this;
    }

    /**
     * Get permissions for new directory
     *
     * @return int
     */
    public function getDirectoryPerm()
    {
        return $this->directoryPerm;
    }

    /**
     * Inject the SFTP instance.
     *
     * @param SFTP $connection
     *
     * @return $this
     */
    public function setNetSftpConnection(SFTP $connection)
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * Connect.
     */
    public function connect()
    {
        $this->connection = $this->connection ?: new SFTP($this->host, $this->port, $this->timeout);
        $this->connection->disableStatCache();
        $this->login();
        $this->setConnectionRoot();
    }

    /**
     * Login.
     *
     * @throws ConnectionErrorException
     */
    protected function login()
    {
        if ($this->hostFingerprint) {
            $publicKey = $this->connection->getServerPublicHostKey();

            if ($publicKey === false) {
                throw new ConnectionErrorException('Could not connect to server to verify public key.');
            }

            $actualFingerprint = $this->getHexFingerprintFromSshPublicKey($publicKey);

            if (0 !== strcasecmp($this->hostFingerprint, $actualFingerprint)) {
                throw new ConnectionErrorException('The authenticity of host '.$this->host.' can\'t be established.');
            }
        }

        $authentication = $this->getAuthentication();


        if ($this->connection->login($this->getUsername(), $authentication)) {
            goto past_login;
        }

        // try double authentication, key is already given so now give password
        if ($authentication instanceof RSA && $this->connection->login($this->getUsername(), $this->getPassword())) {
            goto past_login;
        }

        throw new ConnectionErrorException('Could not login with username: '.$this->getUsername().', host: '.$this->host);

        past_login:

        if ($authentication instanceof Agent) {
            $authentication->startSSHForwarding($this->connection);
        }
    }

    /**
     * Convert the SSH RSA public key into a hex formatted fingerprint.
     *
     * @param string $publickey
     * @return string Hex formatted fingerprint, e.g. '88:76:75:96:c1:26:7c:dd:9f:87:50:db:ac:c4:a8:7c'.
     */
    private function getHexFingerprintFromSshPublicKey ($publickey)
    {
        $content = explode(' ', $publickey, 3);
        return implode(':', str_split(md5(base64_decode($content[1])), 2));
    }

    /**
     * Set the connection root.
     *
     * @throws InvalidRootException
     */
    protected function setConnectionRoot()
    {
        $root = $this->getRoot();

        if (! $root) {
            return;
        }

        if (! $this->connection->chdir($root)) {
            throw new InvalidRootException('Root is invalid or does not exist: '.$root);
        }

        $this->setRoot($this->connection->pwd());
    }

    /**
     * Get the password, either the private key or a plain text password.
     *
     * @return Agent|RSA|string
     */
    public function getAuthentication()
    {
        if ($this->useAgent) {
            return $this->getAgent();
        }

        if ($this->privateKey) {
            return $this->getPrivateKey();
        }

        return $this->getPassword();
    }

    /**
     * Get the private key with the password or private key contents.
     *
     * @return RSA
     */
    public function getPrivateKey()
    {
        if ("---" !== substr($this->privateKey, 0, 3) && is_file($this->privateKey)) {
            $this->privateKey = file_get_contents($this->privateKey);
        }

        $key = new RSA();

        if ($password = $this->getPassphrase()) {
            $key->setPassword($password);
        }

        $key->loadKey($this->privateKey);

        return $key;
    }

    /**
     * @return string
     */
    public function getPassphrase()
    {
        if ($this->passphrase === null) {
            //Added for backward compatibility
            return $this->getPassword();
        }
        return $this->passphrase;
    }

    /**
     * @return Agent|bool
     */
    public function getAgent()
    {
        if ( ! $this->agent instanceof Agent) {
            $this->agent = new Agent();
        }

        return $this->agent;
    }

    /**
     * List the contents of a directory.
     *
     * @param string $directory
     * @param bool   $recursive
     *
     * @return array
     */
    protected function listDirectoryContents($directory, $recursive = true)
    {
        $result = [];
        $connection = $this->getConnection();
        $location = $this->prefix($directory);
        $listing = $connection->rawlist($location);

        if ($listing === false) {
            return [];
        }

        foreach ($listing as $filename => $object) {
            // When directory entries have a numeric filename they are changed to int
            $filename = (string) $filename;
            if (in_array($filename, ['.', '..'])) {
                continue;
            }

            $path = empty($directory) ? $filename : ($directory.'/'.$filename);
            $result[] = $this->normalizeListingObject($path, $object);

            if ($recursive && isset($object['type']) && $object['type'] === NET_SFTP_TYPE_DIRECTORY) {
                $result = array_merge($result, $this->listDirectoryContents($path));
            }
        }

        return $result;
    }

    /**
     * Normalize a listing response.
     *
     * @param string $path
     * @param array  $object
     *
     * @return array
     */
    protected function normalizeListingObject($path, array $object)
    {
        $permissions = $this->normalizePermissions($object['permissions']);
        $type = isset($object['type']) && ($object['type'] === 2) ?  'dir' : 'file';

        $timestamp = $object['mtime'];

        if ($type === 'dir') {
            return compact('path', 'timestamp', 'type');
        }

        $visibility = $permissions & 0044 ? AdapterInterface::VISIBILITY_PUBLIC : AdapterInterface::VISIBILITY_PRIVATE;
        $size = (int) $object['size'];

        return compact('path', 'timestamp', 'type', 'visibility', 'size');
    }

    /**
     * Disconnect.
     */
    public function disconnect()
    {
        $this->connection = null;
    }

    /**
     * @inheritdoc
     */
    public function write($path, $contents, Config $config)
    {
        if ($this->upload($path, $contents, $config) === false) {
            return false;
        }

        return compact('contents', 'path');
    }

    /**
     * @inheritdoc
     */
    public function writeStream($path, $resource, Config $config)
    {
        if ($this->upload($path, $resource, $config) === false) {
            return false;
        }

        return compact('path');
    }

    /**
     * Upload a file.
     *
     * @param string          $path
     * @param string|resource $contents
     * @param Config          $config
     * @return bool
     */
    public function upload($path, $contents, Config $config)
    {
        $connection = $this->getConnection();
        $this->ensureDirectory(Util::dirname($path));
        $config = Util::ensureConfig($config);

        if (! $connection->put($path, $contents, SFTP::SOURCE_STRING)) {
            return false;
        }

        if ($config && $visibility = $config->get('visibility')) {
            $this->setVisibility($path, $visibility);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function read($path)
    {
        $connection = $this->getConnection();

        if (($contents = $connection->get($path)) === false) {
            return false;
        }

        return compact('contents', 'path');
    }

    /**
     * @inheritdoc
     */
    public function readStream($path)
    {
        $stream = tmpfile();
        $connection = $this->getConnection();

        if ($connection->get($path, $stream) === false) {
            fclose($stream);
            return false;
        }

        rewind($stream);

        return compact('stream', 'path');
    }

    /**
     * @inheritdoc
     */
    public function update($path, $contents, Config $config)
    {
        return $this->write($path, $contents, $config);
    }

    /**
     * @inheritdoc
     */
    public function updateStream($path, $contents, Config $config)
    {
        return $this->writeStream($path, $contents, $config);
    }

    /**
     * @inheritdoc
     */
    public function delete($path)
    {
        $connection = $this->getConnection();

        return $connection->delete($path);
    }

    /**
     * @inheritdoc
     */
    public function rename($path, $newpath)
    {
        $connection = $this->getConnection();

        return $connection->rename($path, $newpath);
    }

    /**
     * @inheritdoc
     */
    public function deleteDir($dirname)
    {
        $connection = $this->getConnection();

        return $connection->delete($dirname, true);
    }

    /**
     * @inheritdoc
     */
    public function has($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * @inheritdoc
     */
    public function getMetadata($path)
    {
        $connection = $this->getConnection();
        $info = $connection->stat($path);

        if ($info === false) {
            return false;
        }

        $result = Util::map($info, $this->statMap);
        $result['type'] = $info['type'] === NET_SFTP_TYPE_DIRECTORY ? 'dir' : 'file';
        $result['visibility'] = $info['permissions'] & $this->permPublic ? 'public' : 'private';

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getTimestamp($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * @inheritdoc
     */
    public function getMimetype($path)
    {
        if (! $data = $this->read($path)) {
            return false;
        }

        $data['mimetype'] = Util::guessMimeType($path, $data['contents']);

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function createDir($dirname, Config $config)
    {
        $connection = $this->getConnection();

        if (! $connection->mkdir($dirname, $this->directoryPerm, true)) {
            return false;
        }

        return ['path' => $dirname];
    }

    /**
     * @inheritdoc
     */
    public function getVisibility($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * @inheritdoc
     */
    public function setVisibility($path, $visibility)
    {
        $visibility = ucfirst($visibility);

        if (! isset($this->{'perm'.$visibility})) {
            throw new InvalidArgumentException('Unknown visibility: '.$visibility);
        }

        $connection = $this->getConnection();

        return $connection->chmod($this->{'perm'.$visibility}, $path);
    }

    /**
     * @inheritdoc
     */
    public function isConnected()
    {
        if ( ! $this->connection instanceof SFTP || ! $this->connection->isConnected()) {
            return false;
        }

        return $this->usePingForConnectivityCheck === false || $this->connection->ping();
    }
}
