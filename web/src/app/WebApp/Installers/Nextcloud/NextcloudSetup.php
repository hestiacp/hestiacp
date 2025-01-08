<?php

namespace Hestia\WebApp\Installers\Nextcloud;

use Hestia\WebApp\InstallationTarget;
use Hestia\WebApp\Installers\BaseSetup;

class NextcloudSetup extends BaseSetup {
	protected $appInfo = [
		"name" => "Nextcloud",
		"group" => "cloud",
		"enabled" => true,
		"version" => "latest",
		"thumbnail" => "nextcloud-thumb.png",
	];

	protected $config = [
		"form" => [
			"username" => ["value" => "admin"],
			"password" => "password",
		],
		"database" => true,
		"resources" => [
			"archive" => ["src" => "https://download.nextcloud.com/server/releases/latest.tar.bz2"],
		],
		"server" => [
			"nginx" => [
				"template" => "owncloud",
			],
			"php" => [
				"supported" => ["8.0", "8.1", "8.2", "8.3"],
			],
		],
	];

	public function install(InstallationTarget $target, array $options = null): void {
		parent::install($target, $options);
		parent::setup($options);

		$this->appcontext->runPHP(
			$options["php_version"],
			$target->getDocRoot("occ"),
			[
				"maintenance:install",
				"--database",
				"mysql",
				"--database-name",
				$this->appcontext->user() . "_" . $options["database_name"],
				"--database-host",
				$options["database_host"],
				"--database-user",
				$this->appcontext->user() . "_" . $options["database_user"],
				"--database-pass",
				$options["database_password"],
				"--admin-user",
				$options["username"],
				"--admin-pass",
				$options["password"],
			]
		);

		$this->appcontext->runPHP(
			$options["php_version"],
			$target->getDocRoot("occ"),
			[
				"config:system:set",
				"trusted_domains",
				"2",
				"--value=" . $target->domainName,
			]
		);

		// Bump minimum memory limit to 512M
		$phpIni = $target->getDocRoot(".user.ini");

		$contents = $this->appcontext->readFile($phpIni);
		$contents .= "memory_limit=512M\r\n";

		$this->appcontext->createFile($phpIni, $contents);
	}
}
