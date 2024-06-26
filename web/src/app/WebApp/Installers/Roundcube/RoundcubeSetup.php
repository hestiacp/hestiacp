<?php

namespace Hestia\WebApp\Installers\Roundcube;

use Hestia\System\Util;
use Hestia\WebApp\Installers\BaseSetup as BaseSetup;

class RoundcubeSetup extends BaseSetup {
	protected $appInfo = [
		"name" => "Roundcube",
		"group" => "mail",
		"enabled" => true,
		"version" => "1.6.7",
		"thumbnail" => "roundcube_thumb.png",
	];

	protected $appname = "roundcube";
	protected $config = [
		"form" => [
			"protocol" => [
				"type" => "select",
				"options" => ["http", "https"],
				"value" => "https",
			],
			"site_name" => [
				"type" => "text",
				"value" => "Roundcube Webmail",
				"placeholder" => "Roundcube Webmail",
			],
		],
		"database" => true,
		"resources" => [
			"archive" => [
				"src" =>
					"https://github.com/roundcube/roundcubemail/releases/download/1.6.7/roundcubemail-1.6.7-complete.tar.gz",
			],
		],
		"server" => [
			"php" => [
				"supported" => ["7.4", "8.0", "8.1", "8.2"],
			],
		],
	];

	public function install(array $options = null): bool {
		parent::install($options);
		parent::setup($options);

		$installDir = $this->getDocRoot();

		// Ensure the installation directory exists
		if (!is_dir($installDir)) {
			throw new \Exception("Installation directory does not exist: " . $installDir);
		}

		// Database credentials
		$dbName = $this->appcontext->user() . "_" . $options["database_name"];
		$dbUser = $this->appcontext->user() . "_" . $options["database_user"];
		$dbPass = $options["database_password"];

		// Site name
		$siteName = $options["site_name"];

		// Copy and set up the Roundcube configuration file
		$configSource = $installDir . "/config/config.inc.php.sample";
		$configDest = $installDir . "/config/config.inc.php";

		if (!file_exists($configSource)) {
			throw new \Exception("Configuration sample file does not exist: " . $configSource);
		}

		$result = null;
		$this->appcontext->runUser("v-copy-fs-file", [$configSource, $configDest], $result);

		if ($result->code !== 0) {
			throw new \Exception("Failed to copy configuration file");
		}

		// Update database and product name configuration in config.inc.php
		$this->appcontext->runUser("v-open-fs-file", [$configDest], $result);

		foreach ($result->raw as $line_num => $line) {
			if (strpos($line, "mysql://roundcube:pass@localhost/roundcubemail") !== false) {
				$result->raw[$line_num] = str_replace(
					"mysql://roundcube:pass@localhost/roundcubemail",
					"mysql://$dbUser:$dbPass@localhost/$dbName",
					$line,
				);
			}
			if (strpos($line, "\$config['product_name'] = 'Roundcube Webmail';") !== false) {
				$result->raw[$line_num] = "\$config['product_name'] = '$siteName';";
			}
		}

		$tmp = $this->saveTempFile(implode("\r\n", $result->raw));
		$this->appcontext->runUser("v-move-fs-file", [$tmp, $configDest], $result);

		if ($result->code !== 0) {
			throw new \Exception("Error updating file in: " . $tmp . " " . $result->text);
		}

		// Initialize the database using Roundcube initdb.sh
		$this->appcontext->runUser(
			"v-run-cli-cmd",
			["/usr/bin/php", $installDir . "/bin/initdb.sh", "--dir=" . $installDir . "/SQL"],
			$status,
		);

		if ($status->code !== 0) {
			throw new \Exception("Failed to initialize the database using Roundcube initdb.sh");
		}

		return $status->code === 0;
	}
}
