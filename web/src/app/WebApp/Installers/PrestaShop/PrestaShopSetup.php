<?php

namespace Hestia\WebApp\Installers\PrestaShop;

use Hestia\WebApp\InstallationTarget;
use Hestia\WebApp\Installers\BaseSetup;

class PrestaShopSetup extends BaseSetup {
	protected $appInfo = [
		"name" => "PrestaShop",
		"group" => "ecommerce",
		"enabled" => true,
		"version" => "8.1.0",
		"thumbnail" => "prestashop-thumb.png",
	];

	protected $extractsubdir = "/tmp-prestashop";

	protected $config = [
		"form" => [
			"prestashop_account_first_name" => ["value" => ""],
			"prestashop_account_last_name" => ["value" => ""],
			"prestashop_account_email" => "text",
			"prestashop_account_password" => "password",
		],
		"database" => true,
		"resources" => [
			"archive" => [
				"src" => "https://github.com/PrestaShop/PrestaShop/releases/download/8.2.0/prestashop_8.2.0.zip",
			],
		],
		"server" => [
			"nginx" => [
				"template" => "prestashop",
			],
			"php" => [
				"supported" => ["8.0", "8.1", "8.2", "8.3"],
			],
		],
	];

	public function install(InstallationTarget $target, array $options = null): void {
		parent::install($target, $options);
		parent::setup($options);

		$this->appcontext->archiveExtract(
			$target->getDocRoot($this->extractsubdir . "/prestashop.zip"),
			$target->getDocRoot(),
		);

		$this->appcontext->runPHP(
			$options["php_version"],
			$target->getDocRoot("/install/index_cli.php"),
			[
				"--db_server=" . $options["database_host"],
				"--db_user=" . $this->appcontext->user() . "_" . $options["database_user"],
				"--db_password=" . $options["database_password"],
				"--db_name=" . $this->appcontext->user() . "_" . $options["database_name"],
				"--firstname=" . $options["prestashop_account_first_name"],
				"--lastname=" . $options["prestashop_account_last_name"],
				"--password=" . $options["prestashop_account_password"],
				"--email=" . $options["prestashop_account_email"],
				"--domain=" . $target->domainName,
				"--ssl=" . $target->isSslEnabled ? 1 : 0,
			],
		);

		// remove install folder
		$this->appcontext->deleteDirectory($target->getDocRoot("/install"));
		$this->appcontext->deleteDirectory($target->getDocRoot($this->extractsubdir));
	}
}
