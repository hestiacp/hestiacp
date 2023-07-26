<?php

namespace Hestia\WebApp\Installers\MediaWiki;

use Hestia\System\Util;
use Hestia\WebApp\Installers\BaseSetup as BaseSetup;

class MediaWikiSetup extends BaseSetup {
	protected $appInfo = [
		"name" => "MediaWiki",
		"group" => "cms",
		"enabled" => true,
		"version" => "1.40.0",
		"thumbnail" => "MediaWiki-2020-logo.svg", //Max size is 300px by 300px
	];

	protected $appname = "mediawiki";
	protected $extractsubdir = "/tmp-mediawiki";

	protected $config = [
		"form" => [
			"admin_username" => ["type" => "text", "value" => "admin"],
			"admin_password" => "password",
			"language" => ["type" => "text", "value" => "en"],
		],
		"database" => true,
		"resources" => [
			"archive" => [
				"src" => "https://releases.wikimedia.org/mediawiki/1.40/mediawiki-1.40.0.zip",
			],
		],
		"server" => [
			"nginx" => [
				"template" => "default",
			],
			"php" => [
				"supported" => ["7.4", "8.0"],
			],
		],
	];

	public function install(array $options = null) {
		parent::install($options);
		parent::setup($options);

		//check if ssl is enabled
		$this->appcontext->run(
			"v-list-web-domain",
			[$this->appcontext->user(), $this->domain, "json"],
			$status,
		);

		if ($status->code !== 0) {
			throw new \Exception("Cannot list domain");
		}

		$sslEnabled = $status->json[$this->domain]["SSL"] == "no" ? 0 : 1;

		$webDomain = ($sslEnabled ? "https://" : "http://") . $this->domain;

		$this->appcontext->runUser(
			"v-copy-fs-directory",
			[$this->getDocRoot($this->extractsubdir . "/mediawiki-1.39.2/."), $this->getDocRoot()],
			$result,
		);

		$this->appcontext->runUser(
			"v-run-cli-cmd",
			[
				"/usr/bin/php" . $options["php_version"],
				$this->getDocRoot("maintenance/install.php"),
				"--dbserver=localhost",
				"--dbname=" . $this->appcontext->user() . "_" . $options["database_name"],
				"--installdbuser=" . $this->appcontext->user() . "_" . $options["database_user"],
				"--installdbpass=" . $options["database_password"],
				"--dbuser=" . $this->appcontext->user() . "_" . $options["database_user"],
				"--dbpass=" . $options["database_password"],
				"--server=" . $webDomain,
				"--scriptpath=", // must NOT be /
				"--lang=" . $options["language"],
				"--pass=" . $options["admin_password"],
				"MediaWiki", // A Space here would trigger the next argument and preemptively set the admin username
				$options["admin_username"],
			],
			$status,
		);

		$this->cleanup();

		return $status->code === 0;
	}
}
