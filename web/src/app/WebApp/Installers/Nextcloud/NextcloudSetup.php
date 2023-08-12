<?php

namespace Hestia\WebApp\Installers\Nextcloud;

use Hestia\WebApp\Installers\BaseSetup as BaseSetup;

class NextcloudSetup extends BaseSetup {
	protected $appInfo = [
		"name" => "Nextcloud",
		"group" => "cloud",
		"enabled" => true,
		"version" => "latest",
		"thumbnail" => "nextcloud-thumb.png",
	];

	protected $appname = "nextcloud";

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
				"supported" => ["8.0", "8.1", "8.2"],
			],
		],
	];

	public function install(array $options = null): bool {
		parent::install($options);
		parent::setup($options);

		// install nextcloud
		$php_version = $this->appcontext->getSupportedPHP(
			$this->config["server"]["php"]["supported"],
		);

		$this->appcontext->runUser(
			"v-run-cli-cmd",
			[
				"/usr/bin/php" . $options["php_version"],
				$this->getDocRoot("occ"),
				"maintenance:install",
				"--database mysql",
				"--database-name " . $this->appcontext->user() . "_" . $options["database_name"],
				"--database-user " . $this->appcontext->user() . "_" . $options["database_user"],
				"--database-pass " . $options["database_password"],
				"--admin-user " . $options["username"],
				"--admin-pass " . $options["password"],
			],
			$status,
		);

		$this->appcontext->runUser(
			"v-run-cli-cmd",
			[
				"/usr/bin/php" . $options["php_version"],
				$this->getDocRoot("occ"),
				"config:system:set",
				"trusted_domains 2 --value=" . $this->domain,
			],
			$status,
		);

		// Bump minimum memory limit to 512M
		$result = null;
		$file = $this->getDocRoot(".user.ini");
		$this->appcontext->runUser("v-open-fs-file", [$file], $result);
		array_push($result->raw, "memory_limit=512M");
		$tmp = $this->saveTempFile(implode("\r\n", $result->raw));
		if (!$this->appcontext->runUser("v-move-fs-file", [$tmp, $file], $result)) {
			throw new \Exception("Error updating file in: " . $tmp . " " . $result->text);
		}

		return $status->code === 0;
	}
}
