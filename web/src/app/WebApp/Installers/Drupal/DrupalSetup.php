<?php

namespace Hestia\WebApp\Installers\Drupal;

use Hestia\WebApp\InstallationTarget;
use Hestia\WebApp\Installers\BaseSetup;
use function sprintf;

class DrupalSetup extends BaseSetup {
	protected $appInfo = [
		"name" => "Drupal",
		"group" => "cms",
		"enabled" => "yes",
		"version" => "latest",
		"thumbnail" => "drupal-thumb.png",
	];

	protected $config = [
		"form" => [
			"username" => ["type" => "text", "value" => "admin"],
			"password" => "password",
			"email" => "text",
		],
		"database" => true,
		"resources" => [
			"composer" => ["src" => "drupal/recommended-project", "dst" => "/"],
		],
		"server" => [
			"apache2" => [
				"document_root" => "web",
			],
			"nginx" => [
				"template" => "drupal-composer",
			],
			"php" => [
				"supported" => ["8.1", "8.2", "8.3"],
			],
		],
	];

	public function install(InstallationTarget $target, array $options = null): void {
		parent::install($target, $options);
		parent::setup($options);

		$this->appcontext->runComposer(
			$options["php_version"],
			["require", "-d", $target->getDocRoot(), "drush/drush"],
		);

		$databaseUrl = sprintf(
			'mysql://%s:%s@%s:3306/%s',
			$this->appcontext->user() . "_" . $options["database_user"],
			$options["database_password"],
			$options["database_host"],
			$this->appcontext->user() . "_" . $options["database_name"],
		);

		$this->appcontext->runPHP(
			$options["php_version"],
			$target->getDocRoot("/vendor/drush/drush/drush.php"),
			[
				"site-install",
				"standard",
				"--db-url=" . $databaseUrl,
				"--account-name=" . $options["username"],
				"--account-pass=" . $options["password"],
				"--site-name=Drupal", // Sadly even when escaped spaces are splitted up
				"--site-mail=" . $options["email"],
			]
		);
	}
}
