<?php

namespace Hestia\WebApp\Installers\NamelessMC;

use Hestia\System\Util;
use \Hestia\WebApp\Installers\BaseSetup as BaseSetup;

class NamelessMCSetup extends BaseSetup {

    protected $appInfo = [ 
        'name' => 'NamelessMC',
        'group' => 'cms',
        'enabled' => true,
        'version' => '2.1.2',
        'thumbnail' => 'NamelessMC.png'
    ];
    
    protected $appname = 'namelessmc';
    protected $config = [
        'form' => [
            'protocol' => [ 
                'type' => 'select',
                'options' => ['http','https'],
                'value' => 'https'
            ],
            'site_name' => ['type'=>'text', 'value'=>'NamelessMC'],
            'username' => ['value'=>'Username'],
            'email' => 'text',
            'password' => 'password',
        ],
        'database' => true,
        'resources' => [
            'archive'  => [ 'src' => 'https://github.com/NamelessMC/Nameless/releases/download/v2.1.2/nameless-deps-dist.zip' ],
        ], 
        'server' => [
            'nginx' => [
                'template' => 'namelessmc',
            ],
            'apache2' => [
                'template' => 'namelessmc',
            ],
            'php' => [
                'supported' => [ '7.4','8.0','8.1' ],
            ],
        ],
    ];
    
    public function install(array $options = null)
    {
        parent::install($options);

        $this->appcontext->runUser('v-run-cli-cmd', ['/usr/bin/php',
            $this->getDocRoot('command/to/run'), 
            'maintenance:install',
            '--database mysql',
            '--database-name '.$this->appcontext->user() . '_' .$options['database_name'],
            '--database-user '.$this->appcontext->user() . '_' .$options['database_user'],
            '--database-pass '.$options['database_password'],
            '--admin-user '.$options['username'],
            '--admin-pass '.$options['password']
            ], $status);
            
        return ($status === 0);
    }
}
