<?php

namespace Hestia\WebApp\Installers;

use Hestia\System\Util;

class WordpressSetup extends BaseSetup {

    protected $appname = 'wordpress';
    protected $config = [
        'form' => [
            //'protocol' => [ 
            //    'type' => 'select',
            //    'options' => ['http','https'],
            //],
            'site_name' => ['type'=>'text', 'value'=>'WordPress Blog'],
            'site_description' => ['value'=>'Another WordPress site'],
            'wordpress_account_username' => ['value'=>'wpadmin'],
            'wordpress_account_email' => 'text',
            'wordpress_account_password' => 'password',
            ],
        'database' => true,
        'resources' => [
            'archive'  => [ 'src' => 'https://wordpress.org/latest.tar.gz' ],
        ],
        
    ];

    public function install(array $options = null)
    {
        parent::install($options);

        $this->appcontext->runUser('v-open-fs-file',[$this->getDocRoot("wp-config-sample.php")], $result);

        $distconfig = preg_replace( [
                '/database_name_here/', '/username_here/', '/password_here/'
            ], [
                $this->appcontext->user() . '_' . $options['database_name'],
                $this->appcontext->user() . '_' . $options['database_user'],
                $options['database_password']
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
            . escapeshellarg("http://".$this->domain."/wp-admin/install.php?step=2")
            . " -d " . escapeshellarg(
                "weblog_title=" . rawurlencode($options['site_name'])
            . "&user_name="      . rawurlencode($options['wordpress_account_username'])
            . "&admin_password=" . rawurlencode($options['wordpress_account_password'])
            . "&admin_password2=". rawurlencode($options['wordpress_account_password'])
            . "&admin_email="    . rawurlencode($options['wordpress_account_email'])), $output, $return_var);

        return ($return_var === 0);
    }
}
