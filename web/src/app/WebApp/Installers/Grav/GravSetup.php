<?php

namespace Hestia\WebApp\Installers\Grav;

use Hestia\System\Util;
use \Hestia\WebApp\Installers\BaseSetup as BaseSetup;

class GravSetup extends BaseSetup {

	protected $appInfo = [ 
		'name' => 'Grav',
		'group' => 'cms',
		'enabled' => true,
		'version' => 'latest',
		'thumbnail' => 'grav-symbol.svg'
	];
	
	protected $appname = 'grav';
	
	protected $config = [
		'form' => [
			'admin' => ['type'=>'boolean', 'value'=>false, 'label' => "Create admin account"],
			'username' => ['text'=>'admin'],
			'password' => 'password',
			'email' => 'text'
		],
		'database' => false,
		'resources' => [
			'composer' => [ 'src' => 'getgrav/grav', 'dst' => '/']
		],
	];
	
	public function install(array $options = null)
	{
		parent::install($options);
		if ( $options['admin'] == true ){
			chdir($this->getDocRoot());
			$this -> appcontext -> runUser('v-run-cli-cmd', ['php', 
			$this->getDocRoot('/bin/gpm'),
				'install admin'
		    ], $status);
			$this -> appcontext -> runUser('v-run-cli-cmd', ['php', 
				$this->getDocRoot('/bin/plugin'),
				'login new-user',
				'-u '.$options['username'],
				'-p '.$options['password'],
				'-e '.$options['email'],
				'-P a',
				'-N '.$options['username']
			 ], $status);
		}
		return (1);
	}
}