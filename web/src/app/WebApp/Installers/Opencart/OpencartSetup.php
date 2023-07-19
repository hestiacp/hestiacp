<?php

namespace Hestia\WebApp\Installers\Opencart;

use Hestia\WebApp\Installers\BaseSetup as BaseSetup;

class OpencartSetup extends BaseSetup {
	protected $appInfo = [
		"name" => "Opencart",
		"group" => "ecommerce",
		"enabled" => true,
		"version" => "4.0.2.2",
		"thumbnail" => "opencart-thumb.png",
	];

	protected $appname = "opencart";
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
				"supported" => ["7.4", "8.0", "8.1", "8.2"],
			],
		],
	];

	public function install(array $options = null): bool {
		parent::install($options);
		parent::setup($options);

		$this->appcontext->runUser(
			"v-copy-fs-directory",
			[$this->getDocRoot($this->extractsubdir . "/upload/."), $this->getDocRoot()],
			$result,
		);

		$this->appcontext->runUser("v-copy-fs-file", [
			$this->getDocRoot("config-dist.php"),
			$this->getDocRoot("config.php"),
		]);
		$this->appcontext->runUser("v-copy-fs-file", [
			$this->getDocRoot("admin/config-dist.php"),
			$this->getDocRoot("admin/config.php"),
		]);
		$this->appcontext->runUser("v-copy-fs-file", [
			$this->getDocRoot(".htaccess.txt"),
			$this->getDocRoot(".htaccess"),
		]);
		#Check if SSL is enabled
		$this->appcontext->run(
			"v-list-web-domain",
			[$this->appcontext->user(), $this->domain, "json"],
			$status,
		);
		if ($status->code !== 0) {
			throw new \Exception("Cannot list domain");
		}
		if ($status->json[$this->domain]["SSL"] == "no") {
			$protocol = "http://";
		} else {
			$protocol = "https://";
		}

		$this->appcontext->runUser(
			"v-run-cli-cmd",
			[
				"/usr/bin/php" . $options["php_version"],
				$this->getDocRoot("/install/cli_install.php"),
				"install",
				"--db_username " . $this->appcontext->user() . "_" . $options["database_user"],
				"--db_password " . $options["database_password"],
				"--db_database " . $this->appcontext->user() . "_" . $options["database_name"],
				"--username " . $options["opencart_account_username"],
				"--password " . $options["opencart_account_password"],
				"--email " . $options["opencart_account_email"],
				"--http_server " . $protocol . $this->domain . "/",
			],
			$status,
		);

		// After install, 'storage' folder must be moved to a location where the web server is not allowed to serve file
		// - Opencart Nginx template and Apache ".htaccess" forbids acces to /storage folder
		$this->appcontext->runUser(
			"v-move-fs-directory",
			[$this->getDocRoot("system/storage"), $this->getDocRoot()],
			$result,
		);
		$this->appcontext->runUser(
			"v-run-cli-cmd",
			["sed", "-i", "s/'storage\//'..\/storage\// ", $this->getDocRoot("config.php")],
			$status,
		);
		$this->appcontext->runUser(
			"v-run-cli-cmd",
			["sed", "-i", "s/'storage\//'..\/storage\// ", $this->getDocRoot("admin/config.php")],
			$status,
		);
		$this->appcontext->runUser(
			"v-run-cli-cmd",
			["sed", "-i", "s/\^system\/storage\//^\/storage\// ", $this->getDocRoot(".htaccess")],
			$status,
		);

		$this->appcontext->runUser("v-change-fs-file-permission", [
			$this->getDocRoot("config.php"),
			"640",
		]);
		$this->appcontext->runUser("v-change-fs-file-permission", [
			$this->getDocRoot("admin/config.php"),
			"640",
		]);

		// remove install folder
		$this->appcontext->runUser("v-delete-fs-directory", [$this->getDocRoot("/install")]);
		$this->cleanup();

		return $status->code === 0;
	}
}
