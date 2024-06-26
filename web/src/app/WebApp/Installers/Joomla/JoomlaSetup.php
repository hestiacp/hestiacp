<?php

namespace Hestia\WebApp\Installers\Joomla;

use Hestia\System\Util;
use Hestia\WebApp\Installers\BaseSetup as BaseSetup;

class JoomlaSetup extends BaseSetup {
	protected $appInfo = [
		"name" => "Joomla",
		"group" => "cms",
		"enabled" => true,
		"version" => "5.1.1",
		"thumbnail" => "joomla_thumb.png",
	];

	protected $appname = "joomla";
	protected $config = [
		"form" => [
			"site_name" => [
				"type" => "text",
				"value" => "Joomla Site",
				"placeholder" => "Joomla Site",
			],
			"admin_username" => [
				"type" => "text",
				"value" => "admin",
				"placeholder" => "Admin Username",
			],
			"admin_password" => [
				"type" => "password",
				"value" => "",
				"placeholder" => "Admin Password",
			],
			"admin_email" => [
				"type" => "text",
				"value" => "admin@example.com",
				"placeholder" => "Admin Email",
			],
			"install_directory" => [
				"type" => "text",
				"value" => "",
				"placeholder" => "/",
			],
		],
		"database" => true,
		"resources" => [
			"archive" => [
				"src" =>
					"https://downloads.joomla.org/cms/joomla5/5-1-1/Joomla_5-1-1-Stable-Full_Package.zip?format=zip",
			],
		],
		"server" => [
			"php" => [
				"supported" => ["7.4", "8.0", "8.1", "8.2"],
			],
		],
	];

	public function install(array $options = null): bool {
		$installDir =
			rtrim($this->getDocRoot(), "/") . "/" . ltrim($options["install_directory"] ?? "", "/");
		parent::setAppDirInstall($options["install_directory"] ?? "");
		parent::install($options);
		parent::setup($options);

		if (!is_dir($installDir)) {
			throw new \Exception("Installation directory does not exist: " . $installDir);
		}

		// Database credentials
		$dbName = $this->appcontext->user() . "_" . $options["database_name"];
		$dbUser = $this->appcontext->user() . "_" . $options["database_user"];
		$dbPass = $options["database_password"];

		// Site and admin credentials
		$siteName = $options["site_name"];
		$adminUsername = $options["admin_username"];
		$adminPassword = $options["admin_password"];
		$adminEmail = $options["admin_email"];

		// Initialize Joomla using the CLI
		$cliCmd = [
			"/usr/bin/php",
			"$installDir/installation/joomla.php",
			"install",
			"--site-name=" . $siteName,
			"--admin-user=" . $adminUsername,
			"--admin-username=" . $adminUsername,
			"--admin-password=" . $adminPassword,
			"--admin-email=" . $adminEmail,
			"--db-user=" . $dbUser,
			"--db-pass=" . $dbPass,
			"--db-name=" . $dbName,
			"--db-prefix=" . Util::generate_string(5, false) . "_",
			"--db-host=localhost",
			"--db-type=mysqli",
		];

		$status = null;
		$this->appcontext->runUser("v-run-cli-cmd", $cliCmd, $status);

		if ($status->code !== 0) {
			throw new \Exception("Failed to install Joomla using CLI: " . $status->text);
		}

		return true;
	}
}
