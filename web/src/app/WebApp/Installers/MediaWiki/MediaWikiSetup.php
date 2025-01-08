<?php

namespace Hestia\WebApp\Installers\MediaWiki;

use Hestia\WebApp\InstallationTarget;
use Hestia\WebApp\Installers\BaseSetup;

class MediaWikiSetup extends BaseSetup {
	protected $appInfo = [
		"name" => "MediaWiki",
		"group" => "cms",
		"enabled" => true,
		"version" => "1.43.0",
		"thumbnail" => "MediaWiki-2020-logo.svg", //Max size is 300px by 300px
	];

	protected $config = [
		"form" => [
			"admin_username" => ["type" => "text", "value" => "admin"],
			"admin_password" => "password",
			"language" => ["type" => "text", "value" => "en"],
		],
		"database" => true,
		"resources" => [
			"archive" => [
				"src" => "https://releases.wikimedia.org/mediawiki/1.43/mediawiki-1.43.0.zip",
			],
		],
		"server" => [
			"nginx" => [
				"template" => "default",
			],
			"php" => [
				"supported" => ["8.0", "8.1", "8.2", "8.3"],
			],
		],
	];

	public function install(InstallationTarget $target, array $options = null): void {
		parent::install($target, $options);
		parent::setup($options);

		$this->appcontext->copyDirectory(
			$target->getDocRoot("/mediawiki-1.43.0/."),
			$target->getDocRoot()
		);

		$this->appcontext->runPHP(
			$options["php_version"],
			$target->getDocRoot("maintenance/install.php"),
			[
				"--dbserver=" . $options["database_host"],
				"--dbname=" . $this->appcontext->user() . "_" . $options["database_name"],
				"--installdbuser=" . $this->appcontext->user() . "_" . $options["database_user"],
				"--installdbpass=" . $options["database_password"],
				"--dbuser=" . $this->appcontext->user() . "_" . $options["database_user"],
				"--dbpass=" . $options["database_password"],
				"--server=" . $target->getUrl(),
				"--scriptpath=", // must NOT be /
				"--lang=" . $options["language"],
				"--pass=" . $options["admin_password"],
				"Media Wiki",
				$options["admin_username"],
			],
		);

		$this->appcontext->deleteDirectory(
			$target->getDocRoot("/mediawiki-1.43.0/")
		);
	}
}
