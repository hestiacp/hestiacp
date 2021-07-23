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
		'form' => [],
		'database' => false,
		'resources' => [
			'composer' => [ 'src' => 'getgrav/grav', 'dst' => '/']
		], 
	];
	
	public function install(array $options = null)
	{
		parent::install($options);
		return (1);
	}
}