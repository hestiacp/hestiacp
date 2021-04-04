<?php

namespace Hestia\WebApp\Installers\Nextcloud;

use \Hestia\WebApp\Installers\BaseSetup as BaseSetup;

class NextcloudSetup extends BaseSetup {

    protected $appInfo = [ 
        'name' => 'Nextcloud',
        'group' => 'cloud',
        'enabled' => true,
        'version' => '21.0.0',
        'thumbnail' => 'nextcloud-thumb.png'
    ];
    
    protected $appname = 'nextcloud';
    
    protected $config = [
        'form' => [
            'username' => ['value'=>'admin'],
            'password' => 'password'
            ],
        'database' => true,
        'resources' => [
            'archive'  => [ 'src' => 'https://download.nextcloud.com/server/releases/nextcloud-21.0.0.tar.bz2' ]
        ],
    ];
    
    public function install(array $options = null) : bool
    {
        parent::install($options);
        
        $this->appcontext->runUser('v-copy-fs-file',[$this->getDocRoot(".htaccess.txt"), $this->getDocRoot(".htaccess")]);
        
        // install nextcloud 
        $this->appcontext->runUser('v-run-cli-cmd', ['/usr/bin/php',
            $this->getDocRoot('occ'), 
            'maintenance:install',
            '--database mysql',
            '--database-name '.$this->appcontext->user() . '_' .$options['database_name'],
            '--database-user '.$this->appcontext->user() . '_' .$options['database_user'],
            '--database-pass '.$options['database_password'],
            '--admin-user '.$options['username'],
            '--admin-pass '.$options['password']
            ], $status);
            
        $this->appcontext->runUser('v-run-cli-cmd',  
            ['/usr/bin/php',
                $this->getDocRoot('occ'), 
                'config:system:set',
                'trusted_domains 2 --value='.$this->domain
            ], $status2);
        return ($status->code === 0 && $status2->code === 0);
    }
}
