<?php

namespace Hestia\WebApp\Installers\ThirtyBees;

use Hestia\WebApp\Installers\BaseSetup as BaseSetup;

class ThirtyBeesSetup extends BaseSetup {
	protected $appInfo = [
		"name" => "ThirtyBees",
		"group" => "ecommerce",
		"enabled" => true,
		"version" => "1.5.1",
		"thumbnail" => "thirtybees-thumb.png",
	];

	protected $appname = "thirtybees";
	protected $extractsubdir = ".";

	protected $config = [
		"form" => [
			"thirtybees_account_first_name" => ["value" => "John"],
			"thirtybees_account_last_name" => ["value" => "Doe"],
			"thirtybees_account_email" => "text",
			"thirtybees_account_password" => "password",
		],
		"database" => true,
		"resources" => [
			"archive" => [
				"src" =>
					"https://github.com/thirtybees/thirtybees/releases/download/1.5.1/thirtybees-v1.5.1-php7.4.zip",
			],
		],
		"server" => [
			"nginx" => [
				"template" => "prestashop",
			],
			"php" => [
				"supported" => ["7.4", "8.0"],
			],
		],
	];

	public function install(array $options = null): bool {
		parent::install($options);
		parent::setup($options);

		try {
			$this->retrieveResources($options);
		} catch (\Exception $e) {
			// Registrar el error pero continuar con la instalación
			error_log("Error durante la descarga o extracción: " . $e->getMessage());
		}

		// Verificación del estado SSL del dominio
		$status = null;
		$this->appcontext->runUser(
			"v-list-web-domain",
			[$this->domain, "json"],
			$status,
		);

		if ($status->code !== 0) {
			throw new \Exception("No se puede listar el dominio");
		}

		$ssl_enabled = $status->json[$this->domain]["SSL"] == "no" ? 0 : 1;

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
				"--firstname=" . $options["thirtybees_account_first_name"],
				"--lastname=" . $options["thirtybees_account_last_name"],
				"--password=" . $options["thirtybees_account_password"],
				"--email=" . $options["thirtybees_account_email"],
				"--domain=" . $this->domain,
				"--ssl=" . $ssl_enabled,
			],
			$status,
		);

		// Delete install directory
		$installDir = $this->getDocRoot() . "/install";
		if (is_dir($installDir)) {
			$this->appcontext->runUser("v-delete-fs-directory", [$installDir]);
		} else {
			error_log(
				"No se pudo encontrar el directorio de instalación para eliminar: " . $installDir,
			);
		}

		return $status->code === 0;
	}
}
