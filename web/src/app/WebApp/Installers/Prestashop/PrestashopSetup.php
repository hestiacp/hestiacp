<?php

namespace Hestia\WebApp\Installers\Prestashop;

use \Hestia\WebApp\Installers\BaseSetup as BaseSetup;

class PrestashopSetup extends BaseSetup {

    protected $appInfo = [ 
        'name' => 'Prestashop',
        'group' => 'ecommerce',
        'enabled' => true,
        'version' => '1.7.7.1',
        'thumbnail' => 'prestashop-thumb.png'
    ];
    
    protected $appname = 'prestashop';
    protected $extractsubdir="/tmp-prestashop";

    protected $config = [
        'form' => [
            'prestashop_account_first_name' => ['value'=>'John'],
            'prestashop_account_last_name' => ['value'=>'Doe'],
            'prestashop_account_email' => 'text',
            'prestashop_account_password' => 'password',
            ],
        'database' => true,
        'resources' => [
            'archive'  => [ 'src' => 'https://github.com/PrestaShop/PrestaShop/releases/download/1.7.7.1/prestashop_1.7.7.1.zip' ],
        ],

    ];
    
    public function info(){
        return $this -> appInfo;
    }

    public function install(array $options=null) : bool
    {
        parent::install($options);
        $this->appcontext->archiveExtract($this->getDocRoot($this->extractsubdir . '/prestashop.zip'), $this->getDocRoot());
        //check if ssl is enabled 
        $this->appcontext->run('v-list-web-domain',[$this -> appcontext->user(),$this -> domain,'json'],$status);
        if($status->code !== 0) {
            throw new \Exception("Cannot list domain");
        }
        
        if ($status -> json == 'no'){ $ssl_enabled = 0; }else{ $ssl_enabled = 1;}
        
        $this->appcontext->runUser('v-run-cli-cmd', [
            "/usr/bin/php",
            $this->getDocRoot("/install/index_cli.php"),
            "--db_user=" . $this->appcontext->user() . '_' .$options['database_user'],
            "--db_password=" . $options['database_password'],
            "--db_name="     . $this->appcontext->user() . '_' .$options['database_name'],
            "--firstname="   . $options['prestashop_account_first_name'],
            "--lastname="    . $options['prestashop_account_last_name'],
            "--password="    . $options['prestashop_account_password'],
            "--email="       . $options['prestashop_account_email'],
            "--domain="      . $this->domain,
            "--ssl="         . $ssl_enabled,],  $status);
        
        // remove install folder
        $this->appcontext->runUser('v-delete-fs-directory', [$this->getDocRoot("/install")]);
        $this->cleanup();

        return ($status->code === 0);
    }
}
