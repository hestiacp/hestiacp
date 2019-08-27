<?php
require_once("BaseSetup.php");

class OpencartSetup extends BaseSetup {

    protected $appname = 'opencart';
    protected $extractsubdir="/tmp-opencart";

    protected $config = [
        'form' => [
            'protocol' => [
                'type' => 'select',
                'options' => ['http','https'],
            ],
            'opencart_account_username' => ['value'=>'ocadmin'],
            'opencart_account_email' => 'text',
            'opencart_account_password' => 'password',
            ],
        'database' => true,
        'url' => 'https://github.com/opencart/opencart/releases/download/3.0.3.2/opencart-3.0.3.2.zip'
    ];

    public function install(array $options) : bool {
        parent::install($options);

        $this->appcontext->runUser('v-copy-fs-directory',[
            $this->getDocRoot($this->extractsubdir . "/upload/."),
            $this->getDocRoot()], $result);

        $this->appcontext->runUser('v-copy-fs-file',[$this->getDocRoot("config-dist.php"), $this->getDocRoot("config.php")]);
        $this->appcontext->runUser('v-copy-fs-file',[$this->getDocRoot("admin/config-dist.php"), $this->getDocRoot("admin/config.php")]);
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

        $this->appcontext->runUser('v-change-fs-file-permission',[$this->getDocRoot("config.php"), '640']);
        $this->appcontext->runUser('v-change-fs-file-permission',[$this->getDocRoot("admin/config.php"), '640']);

        // remove install folder
        $this->appcontext->runUser('v-delete-fs-directory', [$this->getDocRoot("/install")]);
        $this->cleanup();

        return ($status->code === 0);
    }
}
