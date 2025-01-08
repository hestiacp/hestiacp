<?php
declare(strict_types=1);

namespace Hestia\WebApp;

use Hestia\System\HestiaApp;
use function array_filter;
use function explode;
use function in_array;
use function var_dump;

class AppWizard {
	private $domain;
	private $appsetup;
	private $appcontext;
	private $formNamespace = "webapp";
	private $errors;

	private $database_config = [
		"database_create" => ["type" => "boolean", "value" => true],
		"database_host" => ["type" => "select"],
		"database_name" => ["type" => "text", "placeholder" => "auto"],
		"database_user" => ["type" => "text", "placeholder" => "auto"],
		"database_password" => ["type" => "password", "placeholder" => "auto"],
	];

	public function __construct(InstallerInterface $app, string $domain, HestiaApp $context) {
		$this->domain = $domain;
		$this->appcontext = $context;

		if (!$this->appcontext->userOwnsDomain($domain)) {
			throw new \Exception("User does not have access to domain [$domain]");
		}

		$this->appsetup = $app;
	}

	public function isDomainRootClean() {
		$installationTarget = $this->appsetup->getInstallationTarget();
		$files = $this->appcontext->listFiles($installationTarget->getDocRoot());

		$filteredFiles = array_filter(
			$files,
			fn (string $file) => in_array($file, ["index.html", "robots.txt"])
		);

		return count($filteredFiles) > 0;
	}

	public function formNs() {
		return $this->formNamespace;
	}

	public function getOptions() {
		$options = $this->appsetup->getOptions();

		$config = $this->appsetup->getConfig();
		$options = array_merge($options, [
			"php_version" => [
				"type" => "select",
				"value" => $this->appcontext->getCurrentBackendTemplate($this->domain),
				"options" => $this->appcontext->getSupportedPHP(
					$config["server"]["php"]["supported"],
				),
			],
		]);

		if ($this->appsetup->withDatabase()) {
			exec(HESTIA_CMD . "v-list-database-hosts json", $output, $return_var);
			$db_hosts_tmp1 = json_decode(implode("", $output), true, flags: JSON_THROW_ON_ERROR);
			$db_hosts_tmp2 = array_map(function ($host) {
				return $host["HOST"];
			}, $db_hosts_tmp1);
			$db_hosts = array_values(array_unique($db_hosts_tmp2));
			unset($output);
			unset($db_hosts_tmp1);
			unset($db_hosts_tmp2);

			$this->database_config["database_host"]["options"] = $db_hosts;

			$options = array_merge($options, $this->database_config);
		}
		return $options;
	}

	public function info() {
		return $this->appsetup->info();
	}

	public function filterOptions(array $options) {
		$filteredoptions = [];
		array_walk($options, function ($value, $key) use (&$filteredoptions) {
			if (strpos($key, $this->formNs() . "_") === 0) {
				$option = str_replace($this->formNs() . "_", "", $key);
				$filteredoptions[$option] = $value;
			}
		});
		return $filteredoptions;
	}

	public function execute(array $options): void {
		$target = $this->appsetup->getInstallationTarget();

		$options = $this->filterOptions($options);

		$random_num = (string) random_int(10000, 99999);
		if ($this->appsetup->withDatabase() && !empty($options["database_create"])) {
			if (empty($options["database_name"])) {
				$options["database_name"] = $random_num;
			}

			if (empty($options["database_user"])) {
				$options["database_user"] = $random_num;
			}

			if (empty($options["database_password"])) {
				$options["database_password"] = bin2hex(random_bytes(10));
			}

			if (!$this->appcontext->checkDatabaseLimit()) {
				$this->errors[] = _("Unable to add database! Limit reached!");

				return;
			}

			if (
				!$this->appcontext->databaseAdd(
					$options["database_name"],
					$options["database_user"],
					$options["database_password"],
					"mysql",
					$options["database_host"],
				)
			) {
				$this->errors[] = "Error adding database";

				return;
			}
		}

		if (empty($this->errors)) {
			$this->appsetup->install($target, $options);
		}
	}
}
