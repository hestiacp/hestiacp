<?php

namespace Hestia\WebApp\Installers\Nextcloud;

use Hestia\WebApp\Installers\BaseSetup as BaseSetup;

class NextcloudSetup extends BaseSetup
{
    protected $appInfo = [
        'name' => 'Nextcloud',
        'group' => 'cloud',
        'enabled' => true,
        'version' => '23.0.2',
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
            'archive'  => [ 'src' => 'https://download.nextcloud.com/server/releases/nextcloud-23.0.2.tar.bz2' ]
        ],
        'server' => [
            'nginx' => [
                'template' => 'owncloud'
            ],
            'php' => [ 
                'supported' => [ '7.3','7.4','8.0' ],
            ]
        ], 
    ];

    public function install(array $options = null): bool
    {
        parent::install($options);
        parent::setup($options);
        
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

        $this->appcontext->runUser(
            'v-run-cli-cmd',
            ['/usr/bin/php',
                $this->getDocRoot('occ'),
                'config:system:set',
                'trusted_domains 2 --value='.$this->domain
            ],
            $status);
        return ($status->code === 0);
    }
}
