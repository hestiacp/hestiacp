<?php

namespace Hestia\WebApp\Installers\Prestashop;

use Hestia\WebApp\Installers\BaseSetup as BaseSetup;

class PrestashopSetup extends BaseSetup {
	protected $appInfo = [
		"name" => "Prestashop",
		"group" => "ecommerce",
		"enabled" => true,
		"version" => "8.1.0",
		"thumbnail" => "prestashop-thumb.png",
	];

	protected $appname = "prestashop";
	protected $extractsubdir = "/tmp-prestashop";

	protected $config = [
		"form" => [
			"prestashop_account_first_name" => ["value" => "John"],
			"prestashop_account_last_name" => ["value" => "Doe"],
			"prestashop_account_email" => "text",
			"prestashop_account_password" => "password",
		],
		"database" => true,
		"resources" => [
			"archive" => [
				"src" =>
					"https://github.com/PrestaShop/PrestaShop/releases/download/8.1.0/prestashop_8.1.0.zip",
			],
		],
		"server" => [
			"nginx" => [
				"template" => "prestashop",
			],
			"php" => [
				"supported" => ["8.0", "8.1"],
			],
		],
	];

	public function install(array $options = null): bool {
		parent::install($options);
		parent::setup($options);
		$this->appcontext->archiveExtract(
			$this->getDocRoot($this->extractsubdir . "/prestashop.zip"),
			$this->getDocRoot(),
		);
		//check if ssl is enabled
		$this->appcontext->run(
			"v-list-web-domain",
			[$this->appcontext->user(), $this->domain, "json"],
			$status,
		);
		if ($status->code !== 0) {
			throw new \Exception("Cannot list domain");
		}

		if ($status->json[$this->domain]["SSL"] == "no") {
			$ssl_enabled = 0;
		} else {
			$ssl_enabled = 1;
		}

		$php_version = $this->appcontext->getSupportedPHP(
			$this->config["server"]["php"]["supported"],
		);

		$this->appcontext->runUser(
			"v-run-cli-cmd",
			[
				"/usr/bin/php" . $options["php_version"],
				$this->getDocRoot("/install/index_cli.php"),
				"--db_user=" . $this->appcontext->user() . "_" . $options["database_user"],
				"--db_password=" . $options["database_password"],
				"--db_name=" . $this->appcontext->user() . "_" . $options["database_name"],
				"--firstname=" . $options["prestashop_account_first_name"],
				"--lastname=" . $options["prestashop_account_last_name"],
				"--password=" . $options["prestashop_account_password"],
				"--email=" . $options["prestashop_account_email"],
				"--domain=" . $this->domain,
				"--ssl=" . $ssl_enabled,
			],
			$status,
		);

		// remove install folder
		$this->appcontext->runUser("v-delete-fs-directory", [$this->getDocRoot("/install")]);
		$this->cleanup();
		return $status->code === 0;
	}
}
