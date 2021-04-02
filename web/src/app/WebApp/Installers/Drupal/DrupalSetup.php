<?php

namespace Hestia\WebApp\Installers\Drupal;

use \Hestia\WebApp\Installers\BaseSetup as BaseSetup;

class DrupalSetup extends BaseSetup {

    protected $appname = 'drupal';
    
    protected $appInfo = [ 
        'name' => 'Drupal',
        'group' => 'cms',
        'enabled' => false,
        'version' => 'latest',
        'thumbnail' => 'drupal-thumb.png'
    ];
    
    protected $config = [
        'form' => [
        ],
        'database' => true,
        'resources' => [
           
        ],
    ];
    
    public function info(){
        return $this -> appInfo;
    }

    public function install(array $options=null) : bool
    {
        exit( "Installer missing" );
    }
}
