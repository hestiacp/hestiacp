<?php

declare(strict_types=1);

namespace Hestia\System;

class HestiaApp {

    protected const TMPDIR_DOWNLOADS="/tmp/hestia-webapp";

    public function __construct() {
        @mkdir(self::TMPDIR_DOWNLOADS);
    }

    public function run(string $cmd, $args, &$cmd_result=null) : bool
    {
        $cli_script = HESTIA_CMD . '/' . basename($cmd);
        $cli_arguments = '';

        if (!empty($args) && is_array($args)) {
            foreach ($args as $arg) {
                $cli_arguments .= escapeshellarg((string)$arg) . ' ';
            }
        } else {
            $cli_arguments = escapeshellarg($args);
        }

        exec ($cli_script . ' ' . $cli_arguments . ' 2>&1', $output, $exit_code);

        $result['code'] = $exit_code;
        $result['args'] = $cli_arguments;
        $result['raw']  = $output;
        $result['text'] = implode( PHP_EOL, $output);
        $result['json'] = json_decode($result['text'], true);
        $cmd_result = (object)$result;

        return ($exit_code === 0);
    }

    public function runUser(string $cmd, $args, &$cmd_result=null) : bool
    {
        if (!empty($args) && is_array($args)) {
            array_unshift($args, $this->user());
        } else {
            $args = [$this->user(), $args];
        }
        return $this->run($cmd, $args, $cmd_result);
    }

    public function installComposer()
    {
        exec("curl https://composer.github.io/installer.sig", $output);

        $signature = implode(PHP_EOL, $output);
        if (empty($signature)) {
           throw new \Exception("Error reading composer signature");
        }

        $composer_setup = self::TMPDIR_DOWNLOADS . DIRECTORY_SEPARATOR . 'composer-setup-' . $signature . '.php';

        exec("wget https://getcomposer.org/installer --quiet -O " . escapeshellarg($composer_setup), $output, $return_code);
        if ($return_code !== 0 ) {
            throw new \Exception("Error downloading composer");
        }

        if ($signature !== hash_file('sha384', $composer_setup)) {
            unlink($composer_setup);
            throw new \Exception("Invalid composer signature");
        }

        $install_folder = $this->getUserHomeDir() . DIRECTORY_SEPARATOR . '.composer';
        $this->runUser('v-run-cli-cmd', ["/usr/bin/php", $composer_setup, "--quiet", "--install-dir=".$install_folder, "--filename=composer" ], $status);

        unlink($composer_setup);

        if ($status->code !== 0 ) {
            throw new \Exception("Error installing composer");
        }
    }

    public function runComposer($args, &$cmd_result=null) : bool
    {
        $composer = $this->getUserHomeDir() . DIRECTORY_SEPARATOR . '.composer' . DIRECTORY_SEPARATOR . 'composer';
        if(!is_file($composer)) {
            $this->installComposer();
        }

        if (!empty($args) && is_array($args)) {
            array_unshift($args, 'composer');
        } else {
            $args = ['composer', $args];
        }

        return $this->runUser('v-run-cli-cmd', $args, $cmd_result);
    }

    // Logged in user
    public function realuser() : string
    {
        return $_SESSION['user'];
    }

    // Effective user
    public function user() : string
    {
        $user = $this->realuser();
        if ($user == 'admin' && !empty($_SESSION['look'])) {
            $user = $_SESSION['look'];
        }

        if(strpos($user, DIRECTORY_SEPARATOR) !== false) {
            throw new \Exception("illegal characters in username");
        }
        return $user;
    }

    public function getUserHomeDir()
    {
        $info = posix_getpwnam($this->user());
        return $info['dir'];
    }

    public function userOwnsDomain(string $domain) : bool
    {
        return $this->runUser('v-list-web-domain', [$domain, 'json']);
    }

    public function databaseAdd(string $dbname, string $dbuser, string $dbpass)
    {
        $v_password = tempnam("/tmp","hst");
        $fp = fopen($v_password, "w");
        fwrite($fp, $dbpass."\n");
        fclose($fp);
        $status = $this->runUser('v-add-database', [$dbname, $dbuser, $v_password]);
        unlink($v_password);
        return $status;
    }

    public function getWebDomainIp(string $domain)
    {
        $this->runUser('v-list-web-domain', [$domain, 'json'], $result);
        $ip = $result->json[$domain]['IP'];
        return filter_var($ip, FILTER_VALIDATE_IP);
    }

    public function getWebDomainPath(string $domain)
    {
        return Util::join_paths( $this->getUserHomeDir() , "web", $domain);
    }

    public function downloadUrl(string $src, $path=null, &$result=null)
    {
        if (strpos($src,'http://') !== 0 &&
            strpos($src,'https://')!== 0 ) {
            return false;
        }

        exec("/usr/bin/wget --tries 3 --timeout=30 --no-dns-cache -nv " . escapeshellarg($src). " -P " . escapeshellarg(self::TMPDIR_DOWNLOADS) . ' 2>&1', $output, $return_var);
        if ($return_var !== 0) {
            return false;
        }

        if(!preg_match('/URL:\s*(.+?)\s*\[(.+?)\]\s*->\s*"(.+?)"/', implode(PHP_EOL, $output), $matches)) {
            return false;
        }

        if(empty($matches) || count($matches) != 4) {
            return false;
        }

        $status['url'] = $matches[1];
        $status['file'] = $matches[3];
        $result = (object)$status;
        return true;
    }

    public function archiveExtract(string $src, string $path, $skip_components=null)
    {
        if (empty($path)) {
            throw new \Exception("Error extracting archive: missing target folder");
        }

        if (realpath($src)) {
            $archive_file = $src;
        } else  {
            if( !$this->downloadUrl($src, null, $download_result) ) {
                throw new \Exception("Error downloading archive");
            }
            $archive_file = $download_result->file;
        }

        return $this->runUser('v-extract-fs-archive', [$archive_file, $path, null, $skip_components]);
    }
}
