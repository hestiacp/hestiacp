<?php

namespace Hestia\WebApp\Installers\OpenCart;

use Hestia\WebApp\InstallationTarget;
use Hestia\WebApp\Installers\BaseSetup;

class OpenCartSetup extends BaseSetup {
	protected $appInfo = [
		"name" => "OpenCart",
		"group" => "ecommerce",
		"enabled" => true,
		"version" => "4.0.2.2",
		"thumbnail" => "opencart-thumb.png",
	];

	protected $extractsubdir = "/tmp-opencart";

	protected $config = [
		"form" => [
			"opencart_account_username" => ["value" => "ocadmin"],
			"opencart_account_email" => "text",
			"opencart_account_password" => "password",
		],
		"database" => true,
		"resources" => [
			"archive" => [
				"src" =>
					"https://github.com/opencart/opencart/releases/download/4.0.2.2/opencart-4.0.2.2.zip",
			],
		],
		"server" => [
			"nginx" => [
				"template" => "opencart",
			],
			"php" => [
				"supported" => ["7.4", "8.0", "8.1", "8.2", "8.3"],
			],
		],
	];

	public function install(InstallationTarget $target, array $options = null): void {
		parent::install($target, $options);
		parent::setup($options);

		$this->appcontext->copyDirectory(
			$target->getDocRoot($this->extractsubdir . "/upload/."),
			$target->getDocRoot()
		);

		$this->appcontext->moveFile(
			$target->getDocRoot("config-dist.php"),
			$target->getDocRoot("config.php"),
		);

		$this->appcontext->moveFile(
			$target->getDocRoot("admin/config-dist.php"),
			$target->getDocRoot("admin/config.php"),
		);

		$this->appcontext->moveFile(
			$target->getDocRoot(".htaccess.txt"),
			$target->getDocRoot(".htaccess"),
		);

		$this->appcontext->runPHP(
			$options["php_version"],
			$target->getDocRoot("/install/cli_install.php"),
			[
				"install",
				"--db_hostname",
				$options["database_host"],
				"--db_username",
				$this->appcontext->user() . "_" . $options["database_user"],
				"--db_password",
				$options["database_password"],
				"--db_database",
				$this->appcontext->user() . "_" . $options["database_name"],
				"--username",
				$options["opencart_account_username"],
				"--password",
				$options["opencart_account_password"],
				"--email",
				$options["opencart_account_email"],
				"--http_server",
				$target->getUrl() . "/",
			]
		);

		$this->appcontext->deleteDirectory($target->getDocRoot("/install"));
		$this->appcontext->deleteDirectory($target->getDocRoot($this->extractsubdir));
	}
}
