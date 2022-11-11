<?php

namespace Hestia\WebApp\Installers\Drupal;

use \Hestia\WebApp\Installers\BaseSetup as BaseSetup;

class DrupalSetup extends BaseSetup {

    protected $appname = 'drupal';

    protected $appInfo = [
        'name' => 'Drupal',
        'group' => 'cms',
        'enabled' => 'yes',
        'version' => 'latest',
        'thumbnail' => 'drupal-thumb.png'
    ];

    protected $config = [
        'form' => [
            'username' => ['type'=>'text', 'value'=>'admin'],
            'password' => 'password',
            'email' => 'text'
        ],
        'database' => true,
        'resources' => [
           'composer' => [ 'src' => 'drupal/recommended-project', 'dst' => '/' ],
        ],
        'server' => [
            'nginx' => [
                'template' => 'drupal-composer'
            ],
            'php' => [
                'supported' => [ '8.0','8.1' ],
            ]
        ],
    ];

    public function install(array $options=null) : bool
    {
        parent::install($options);
        parent::setup($options);

        $this->appcontext->runComposer(["require", "-d " . $this->getDocRoot(), "drush/drush:^10"]);

        $htaccess_rewrite = '
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ web/$1 [L]
</IfModule>';

        $tmp_configpath = $this->saveTempFile($htaccess_rewrite);
        $this->appcontext->runUser('v-move-fs-file',[$tmp_configpath, $this->getDocRoot(".htaccess")], $result);


        $this -> appcontext -> runUser('v-run-cli-cmd', [
            "/usr/bin/php".$options['php_version'],
            $this -> getDocRoot('/vendor/drush/drush/drush'),
            'site-install',
            'standard',
            '--db-url=mysql://'.$this->appcontext->user() . '_' . $options['database_user'].':' . $options['database_password'].'@localhost:3306/'.$this->appcontext->user() . '_' . $options['database_name'].'',
            '--account-name='.$options['username'].' --account-pass='.$options['password'],
            '--site-name=Drupal',
            '--site-mail='.$options['email']
            ], $status);
        return ($status->code === 0);
    }
}
