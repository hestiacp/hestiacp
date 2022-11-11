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
		'server' => [
			'nginx' => [
				'template' => 'grav',
			],
			'php' => [
				'supported' => [ '7.4', '8.0','8.1' ],
			]
		],
	];

	public function install(array $options = null)
	{
		parent::install($options);
		parent::setup($options);

		if ( $options['admin'] == true ){
			chdir($this->getDocRoot());

			$this -> appcontext -> runUser('v-run-cli-cmd', ["/usr/bin/php".$options['php_version'],
			$this->getDocRoot('/bin/gpm'),
				'install admin'
		    ], $status);
			$this -> appcontext -> runUser('v-run-cli-cmd', ["/usr/bin/php".$options['php_version'],
				$this->getDocRoot('/bin/plugin'),
				'login new-user',
				'-u '.$options['username'],
				'-p '.$options['password'],
				'-e '.$options['email'],
				'-P a',
				'-N '.$options['username'],
				'-l en'
			 ], $status);
			 return ($status -> code === 0);
		}else{
			return true;
		}

	}
}