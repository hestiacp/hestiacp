<?php

namespace Hestia\WebApp\Installers\Joomla;

use \Hestia\WebApp\Installers\BaseSetup as BaseSetup;

class JoomlaSetup extends BaseSetup {

    protected $appname = 'joomla';
    
    protected $appInfo = [ 
        'name' => 'Joomla',
        'group' => 'cms',
        'enabled' => false,
        'version' => 'latest',
        'thumbnail' => 'joomla-thumb.png'
    ];
    
    protected $config = [
        'form' => [
        ],
        'database' => true,
        'resources' => [
        
        ],
    ];

    public function install(array $options=null) : bool
    {
        exit( "Installer missing" );
    }
}
