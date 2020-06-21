<?php

namespace Hestia\WebApp\Installers;

class OpencartSetup extends BaseSetup {

    protected $appname = 'opencart';
    protected $extractsubdir="/tmp-opencart";

    protected $config = [
        'form' => [
            'opencart_account_username' => ['value'=>'ocadmin'],
            'opencart_account_email' => 'text',
            'opencart_account_password' => 'password',
            ],
        'database' => true,
        'resources' => [
            'archive'  => [ 'src' => 'https://github.com/opencart/opencart/releases/download/3.0.3.3/opencart-3.0.3.3.zip' ],
        ],
    ];

    public function install(array $options = null) : bool
    {
        parent::install($options);

        $this->appcontext->runUser('v-copy-fs-directory',[
            $this->getDocRoot($this->extractsubdir . "/upload/."),
            $this->getDocRoot()], $result);

        $this->appcontext->runUser('v-copy-fs-file',[$this->getDocRoot("config-dist.php"), $this->getDocRoot("config.php")]);
        $this->appcontext->runUser('v-copy-fs-file',[$this->getDocRoot("admin/config-dist.php"), $this->getDocRoot("admin/config.php")]);
        $this->appcontext->runUser('v-copy-fs-file',[$this->getDocRoot(".htaccess.txt"), $this->getDocRoot(".htaccess")]);
        $this->appcontext->runUser('v-run-cli-cmd', [
            "/usr/bin/php",
            $this->getDocRoot("/install/cli_install.php"),
            "install",
            "--db_username " . $this->appcontext->user() . '_' .$options['database_user'],
            "--db_password " . $options['database_password'],
            "--db_database " . $this->appcontext->user() . '_' .$options['database_name'],
            "--username "    . $options['opencart_account_username'],
            "--password "    . $options['opencart_account_password'],
            "--email "       . $options['opencart_account_email'],
            "--http_server " . "http://" . $this->domain . "/"], $status);

        // After install, 'storage' folder must be moved to a location where the web server is not allowed to serve file
        // - Opencart Nginx template and Apache ".htaccess" forbids acces to /storage folder
        $this->appcontext->runUser('v-move-fs-directory', [$this->getDocRoot("system/storage"), $this->getDocRoot()], $result);
        $this->appcontext->runUser('v-run-cli-cmd', [ "sed", "-i", "s/'storage\//'..\/storage\// ", $this->getDocRoot("config.php") ], $status);
        $this->appcontext->runUser('v-run-cli-cmd', [ "sed", "-i", "s/'storage\//'..\/storage\// ", $this->getDocRoot("admin/config.php") ], $status);
        $this->appcontext->runUser('v-run-cli-cmd', [ "sed", "-i", "s/\^system\/storage\//^\/storage\// ", $this->getDocRoot(".htaccess") ], $status);

        $this->appcontext->runUser('v-change-fs-file-permission',[$this->getDocRoot("config.php"), '640']);
        $this->appcontext->runUser('v-change-fs-file-permission',[$this->getDocRoot("admin/config.php"), '640']);

        // remove install folder
        $this->appcontext->runUser('v-delete-fs-directory', [$this->getDocRoot("/install")]);
        $this->cleanup();

        return ($status->code === 0);
    }
}
