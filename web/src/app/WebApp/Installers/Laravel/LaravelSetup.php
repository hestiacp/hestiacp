<?php

namespace Hestia\WebApp\Installers\Laravel;

use Hestia\WebApp\Installers\BaseSetup as BaseSetup;

class LaravelSetup extends BaseSetup {
	protected $appname = "laravel";

	protected $appInfo = [
		"name" => "Laravel",
		"group" => "framework",
		"enabled" => true,
		"version" => "10.x",
		"thumbnail" => "laravel-thumb.png",
	];

	protected $config = [
		"form" => [],
		"database" => true,
		"resources" => [
			"composer" => ["src" => "laravel/laravel", "dst" => "/"],
		],
		"server" => [
			"nginx" => [
				"template" => "laravel",
			],
			"php" => [
				"supported" => ["8.1", "8.2"],
			],
		],
	];

	public function install(array $options = null): bool {
		parent::install($options);
		parent::setup($options);

		$result = null;

		$htaccess_rewrite = '
<IfModule mod_rewrite.c>
		RewriteEngine On
		RewriteRule ^(.*)$ public/$1 [L]
</IfModule>';

		$tmp_configpath = $this->saveTempFile($htaccess_rewrite);
		$this->appcontext->runUser(
			"v-move-fs-file",
			[$tmp_configpath, $this->getDocRoot(".htaccess")],
			$result,
		);

		return $result->code === 0;
	}
}
