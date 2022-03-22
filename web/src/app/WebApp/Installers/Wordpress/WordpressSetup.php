<?php

namespace Hestia\WebApp\Installers\Wordpress;

use Hestia\System\Util;
use \Hestia\WebApp\Installers\BaseSetup as BaseSetup;

class WordpressSetup extends BaseSetup {

    protected $appInfo = [ 
        'name' => 'Wordpress',
        'group' => 'cms',
        'enabled' => true,
        'version' => 'latest',
        'thumbnail' => 'wp-thumb.png'
    ];
    
    protected $appname = 'wordpress';
    protected $config = [
        'form' => [
            //'protocol' => [ 
            //    'type' => 'select',
            //    'options' => ['http','https'],
            //],
            'install_directory' => ['type'=>'text', 'value'=>'', 'placeholder'=>'/'],
            'site_name' => ['type'=>'text', 'value'=>'WordPress Blog'],
            'wordpress_account_username' => ['value'=>'wpadmin'],
            'wordpress_account_email' => 'text',
            'wordpress_account_password' => 'password',
            ],
        'database' => true,
        'resources' => [
            'archive'  => [ 'src' => 'https://es.wordpress.org/latest-es_ES.tar.gz' ],
        ],
        'server' => [
            'nginx' => [
                'template' => 'wordpress',
            ],
        ],
        
    ];
    
    public function install(array $options = null)
    {
        $this->setAppDirInstall($options['install_directory']);
        parent::install($options);
        parent::setup($options);
        $this->appcontext->runUser('v-open-fs-file',[$this->getDocRoot("wp-config-sample.php")], $result);

        $distconfig = preg_replace( [
                '/database_name_here/', '/username_here/', '/password_here/',,'/utf8/','/wp_/'
            ], [
                $this->appcontext->user() . '_' . $options['database_name'],
                $this->appcontext->user() . '_' . $options['database_user'],
                $options['database_password'],
                'utf8mb4',
                 Util::generate_string(3,'no').'_'
            ],
            $result->text);

        while (strpos($distconfig, 'put your unique phrase here') !== false) {
            $distconfig = preg_replace( '/put your unique phrase here/', Util::generate_string(64), $distconfig, 1);
        }

        $tmp_configpath = $this->saveTempFile($distconfig);

        if(!$this->appcontext->runUser('v-move-fs-file',[$tmp_configpath, $this->getDocRoot("wp-config.php")], $result)) {
            throw new \Exception("Error installing config file in: " . $tmp_configpath . " to:" . $this->getDocRoot("wp-config.php") . $result->text );
        }

        exec("/usr/bin/curl --location --post301 --insecure --resolve ".$this->domain.":80:".$this->appcontext->getWebDomainIp($this->domain)." "
            . escapeshellarg("http://".$this->domain."/".$options['install_directory']."/wp-admin/install.php?step=2")
            . " -d " . escapeshellarg(
                "weblog_title=" . rawurlencode($options['site_name'])
            . "&user_name="      . rawurlencode($options['wordpress_account_username'])
            . "&admin_password=" . rawurlencode($options['wordpress_account_password'])
            . "&admin_password2=". rawurlencode($options['wordpress_account_password'])
            . "&admin_email="    . rawurlencode($options['wordpress_account_email'])), $output, $return_var);
        
    
        return ($return_var === 0);
    }
}
