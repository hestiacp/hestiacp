<?php

declare(strict_types=1);

namespace Hestia\System;

class HestiaApp
{
    protected const TMPDIR_DOWNLOADS="/tmp/hestia-webapp";
    protected $phpsupport =  false;

    public function __construct()
    {
        @mkdir(self::TMPDIR_DOWNLOADS);
    }

    public function run(string $cmd, $args, &$cmd_result=null): bool
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

        exec($cli_script . ' ' . $cli_arguments . ' 2>&1', $output, $exit_code);

        $result['code'] = $exit_code;
        $result['args'] = $cli_arguments;
        $result['raw']  = $output;
        $result['text'] = implode(PHP_EOL, $output);
        $result['json'] = json_decode($result['text'], true);
        $cmd_result = (object)$result;
        if ($exit_code > 0) {
            //log error message in nginx-error.log
            trigger_error($result['text']);
            //throw exception if command fails
            throw new \Exception($result['text']);
        }
        return ($exit_code === 0);
    }

    public function runUser(string $cmd, $args, &$cmd_result=null): bool
    {
        if (!empty($args) && is_array($args)) {
            array_unshift($args, $this->user());
        } else {
            $args = [$this->user(), $args];
        }
        return $this->run($cmd, $args, $cmd_result);
    }

    public function installComposer($version)
    {
        exec("curl https://composer.github.io/installer.sig", $output);

        $signature = implode(PHP_EOL, $output);
        if (empty($signature)) {
            throw new \Exception("Error reading composer signature");
        }

        $composer_setup = self::TMPDIR_DOWNLOADS . DIRECTORY_SEPARATOR . 'composer-setup-' . $signature . '.php';

        exec("wget https://getcomposer.org/installer --quiet -O " . escapeshellarg($composer_setup), $output, $return_code);
        if ($return_code !== 0) {
            throw new \Exception("Error downloading composer");
        }

        if ($signature !== hash_file('sha384', $composer_setup)) {
            unlink($composer_setup);
            throw new \Exception("Invalid composer signature");
        }

        $install_folder = $this->getUserHomeDir() . DIRECTORY_SEPARATOR . '.composer';

        if (!file_exists($install_folder)) {
            exec(HESTIA_CMD .'v-rebuild-user '.$this -> user(), $output, $return_code);
            if ($return_code !== 0) {
                throw new \Exception("Unable to rebuild user");
            }
        }

        $this->runUser('v-run-cli-cmd', ["/usr/bin/php", $composer_setup, "--quiet", "--install-dir=".$install_folder, "--filename=composer", "--$version" ], $status);

        unlink($composer_setup);

        if ($status->code !== 0) {
            throw new \Exception("Error installing composer");
        }
    }

    public function updateComposer($version)
    {
        $this->runUser('v-run-cli-cmd', ["composer", "selfupdate","--$version"]);
    }

    public function runComposer($args, &$cmd_result=null, $version=1): bool
    {
        $composer = $this->getUserHomeDir() . DIRECTORY_SEPARATOR . '.composer' . DIRECTORY_SEPARATOR . 'composer';
        if (!is_file($composer)) {
            $this->installComposer($version);
        } else {
            $this->updateComposer($version);
        }

        if (!empty($args) && is_array($args)) {
            array_unshift($args, 'composer');
        } else {
            $args = ['composer', $args];
        }

        return $this->runUser('v-run-cli-cmd', $args, $cmd_result);
    }

    public function runWp($args, &$cmd_result=null): bool
    {
        $wp = $this->getUserHomeDir() . DIRECTORY_SEPARATOR . '.wp-cli' . DIRECTORY_SEPARATOR . 'wp';
        if (!is_file($wp)) {
            $this -> runUser('v-add-user-wp-cli', []);
        } else {
            $this->runUser('v-run-cli-cmd', [$wp, 'cli', 'update']);
        }
        array_unshift($args, $wp);

        return $this->runUser('v-run-cli-cmd', $args, $cmd_result);
    }

    // Logged in user
    public function realuser(): string
    {
        return $_SESSION['user'];
    }

    // Effective user
    public function user(): string
    {
        $user = $this->realuser();
        if ($_SESSION['userContext'] === 'admin' && !empty($_SESSION['look'])) {
            $user = $_SESSION['look'];
        }

        if (strpos($user, DIRECTORY_SEPARATOR) !== false) {
            throw new \Exception("illegal characters in username");
        }
        return $user;
    }

    public function getUserHomeDir()
    {
        $info = posix_getpwnam($this->user());
        return $info['dir'];
    }

    public function userOwnsDomain(string $domain): bool
    {
        return $this->runUser('v-list-web-domain', [$domain, 'json']);
    }

    public function checkDatabaseLimit(){
        $status = $this -> runUser('v-list-user', ['json'], $result);
        $result -> json[$this -> user()];
        if($result -> json[$this -> user()]['DATABASES'] != "unlimited" ){
            if($result -> json[$this -> user()]['DATABASES'] - $result -> json[$this -> user()]['U_DATABASES'] < 1){
                return false;
            } 
        } 
        return true;
    }
    public function databaseAdd(string $dbname, string $dbuser, string $dbpass, string $charset = 'utf8mb4')
    {
        $v_password = tempnam("/tmp", "hst");
        $fp = fopen($v_password, "w");
        fwrite($fp, $dbpass."\n");
        fclose($fp);
        $status = $this->runUser('v-add-database', [$dbname, $dbuser, $v_password, 'mysql', 'localhost', $charset]);
        if(!$status){
            $this->errors[] = _('Unable to add databse!');
        }
        unlink($v_password);
        return $status;
    }

    public function changeWebTemplate(string $domain, string $template)
    {
        $status = $this->runUser('v-change-web-domain-tpl', [$domain, $template]);
    }
    public function changeBackendTemplate(string $domain, string $template)
    {
        $status = $this->runUser('v-change-web-domain-backend-tpl', [$domain, $template]);
    }

    public function listSuportedPHP()
    {
        if (!$this -> phpsupport) {
            $status = $this -> run('v-list-sys-php', 'json', $result);
            $this -> phpsupport = $result -> json;
        }
        return $this -> phpsupport;
    }

    /*
        Return highest available supported php version
        Eg: Package requires: 7.3 or 7.4 and system has 8.0 and 7.4 it will return 7.4
            Package requires: 8.0 or 8.1 and system has 8.0 and 7.4 it will return 8.0
            Package requires: 7.4 or 8.0 and system has 8.0 and 7.4 it will return 8.0
            If package isn't supported by the available php version false will returned
    */
    public function getSupportedPHP($support)
    {
        $versions = $this -> listSuportedPHP();
        $supported = false;
        $supported_versions = array();

        foreach ($versions as $version) {
            if (in_array($version, $support)) {
                $supported = true;
                $supported_versions[] = $version;
            }
        }
        if ($supported) {
            return $supported_versions[count($supported_versions) - 1];
        } else {
            return false;
        }
    }

    public function getWebDomainIp(string $domain)
    {
        $this->runUser('v-list-web-domain', [$domain, 'json'], $result);
        $ip = $result->json[$domain]['IP'];
        return filter_var($ip, FILTER_VALIDATE_IP);
    }

    public function getWebDomainPath(string $domain)
    {
        return Util::join_paths($this->getUserHomeDir(), "web", $domain);
    }

    public function downloadUrl(string $src, $path=null, &$result=null)
    {
        if (strpos($src, 'http://') !== 0 &&
            strpos($src, 'https://')!== 0) {
            return false;
        }

        exec("/usr/bin/wget --tries 3 --timeout=30 --no-dns-cache -nv " . escapeshellarg($src). " -P " . escapeshellarg(self::TMPDIR_DOWNLOADS) . ' 2>&1', $output, $return_var);
        if ($return_var !== 0) {
            return false;
        }

        if (!preg_match('/URL:\s*(.+?)\s*\[(.+?)\]\s*->\s*"(.+?)"/', implode(PHP_EOL, $output), $matches)) {
            return false;
        }

        if (empty($matches) || count($matches) != 4) {
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
        } else {
            if (!$this->downloadUrl($src, null, $download_result)) {
                throw new \Exception("Error downloading archive");
            }
            $archive_file = $download_result->file;
        }
        
        $result = $this->runUser('v-extract-fs-archive', [$archive_file, $path, null, $skip_components]);
        unlink($archive_file);
        
        return $result;
    }
}
