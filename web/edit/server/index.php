<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

$TAB = "SERVER";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check user
if ($_SESSION["userContext"] != "admin") {
	header("Location: /list/user");
	exit();
}

// Get server hostname
$v_hostname = exec("hostname");

// List available timezones and get current one
exec(HESTIA_CMD . "v-get-sys-timezone", $output, $return_var);
$v_timezone = $output[0];
unset($output);

exec(HESTIA_CMD . "v-get-sys-timezones json", $output, $return_var);
$v_timezones = json_decode(implode("", $output), true);
unset($output);

// List supported php versions
exec(HESTIA_CMD . "v-list-web-templates-backend json", $output, $return_var);
$backend_templates = json_decode(implode("", $output), true);
unset($output);

$v_php_versions = [
	"php-5.6",
	"php-7.0",
	"php-7.1",
	"php-7.2",
	"php-7.3",
	"php-7.4",
	"php-8.0",
	"php-8.1",
	"php-8.2",
];
sort($v_php_versions);

if (empty($backend_templates)) {
	$v_php_versions = [];
}

$backends_active = backendtpl_with_webdomains();
$v_php_versions = array_map(function ($php_version) use ($backend_templates, $backends_active) {
	// Mark installed php versions

	if (stripos($php_version, "php") !== 0) {
		return false;
	}

	$phpinfo = (object) [
		"name" => $php_version,
		"tpl" => strtoupper(str_replace(".", "_", $php_version)),
		"version" => str_ireplace("php-", "", $php_version),
		"usedby" => [],
		"installed" => false,
		"protected" => false,
	];

	if (in_array($phpinfo->tpl, $backend_templates)) {
		$phpinfo->installed = true;
	}

	if (array_key_exists($phpinfo->tpl, $backends_active)) {
		// Prevent used php version to be removed
		if ($phpinfo->installed) {
			$phpinfo->protected = true;
		}
		$phpinfo->usedby = $backends_active[$phpinfo->tpl];
	}

	if ($phpinfo->name == DEFAULT_PHP_VERSION) {
		// Prevent default php version to be removed
		if ($phpinfo->installed) {
			$phpinfo->protected = true;
		}

		if (!empty($backends_active["default"])) {
			$phpinfo->usedby = array_merge_recursive($phpinfo->usedby, $backends_active["default"]);
		}
	}

	return $phpinfo;
}, $v_php_versions);

// List languages
exec(HESTIA_CMD . "v-list-sys-languages json", $output, $return_var);
$language = json_decode(implode("", $output), true);
foreach ($language as $lang) {
	$languages[$lang] = translate_json($lang);
}
asort($languages);
unset($output);

// List themes
exec(HESTIA_CMD . "v-list-sys-themes json", $output, $return_var);
$theme = json_decode(implode("", $output), true);
unset($output);

// List dns cluster hosts
exec(HESTIA_CMD . "v-list-remote-dns-hosts json", $output, $return_var);
$dns_cluster = json_decode(implode("", $output), true);
unset($output);
if (is_array($dns_cluster)) {
	foreach ($dns_cluster as $key => $value) {
		$v_dns_cluster = "yes";
	}
}
if (empty($v_dns_cluster)) {
	$v_dns_cluster = "";
}
$v_release_branch = $_SESSION["RELEASE_BRANCH"];

// List smtp relay settings
if (!empty($_SESSION["SMTP_RELAY"])) {
	$v_smtp_relay = $_SESSION["SMTP_RELAY"];
} else {
	$v_smtp_relay = "";
}
if (!empty($_SESSION["SMTP_RELAY_HOST"])) {
	$v_smtp_relay_host = $_SESSION["SMTP_RELAY_HOST"];
} else {
	$v_smtp_relay_host = "";
}
if (!empty($_SESSION["SMTP_RELAY_PORT"])) {
	$v_smtp_relay_port = $_SESSION["SMTP_RELAY_PORT"];
} else {
	$v_smtp_relay_port = "";
}
if (!empty($_SESSION["SMTP_RELAY_USER"])) {
	$v_smtp_relay_user = $_SESSION["SMTP_RELAY_USER"];
} else {
	$v_smtp_relay_user = "";
}
$v_smtp_relay_pass = "";
if (empty($_POST["v_experimental_features"])) {
	$_POST["v_experimental_features"] = "false";
}
if (empty($_POST["v_policy_user_view_suspended"])) {
	$_POST["v_policy_user_view_suspended"] = "false";
}

// List Database hosts
exec(HESTIA_CMD . "v-list-database-hosts json", $output, $return_var);
$db_hosts = json_decode(implode("", $output), true);
unset($output);
$v_mysql_hosts = array_values(
	array_filter($db_hosts, function ($host) {
		return $host["TYPE"] === "mysql";
	}),
);
$v_mysql = count($v_mysql_hosts) ? "yes" : "no";
$v_pgsql_hosts = array_values(
	array_filter($db_hosts, function ($host) {
		return $host["TYPE"] === "pgsql";
	}),
);
$v_pgsql = count($v_pgsql_hosts) ? "yes" : "no";
unset($db_hosts);

// List backup settings
$v_backup_dir = "/backup";
if (!empty($_SESSION["BACKUP"])) {
	$v_backup_dir = $_SESSION["BACKUP"];
}
$v_backup_gzip = "5";
if (!empty($_SESSION["BACKUP_GZIP"])) {
	$v_backup_gzip = $_SESSION["BACKUP_GZIP"];
}
$v_backup_mode = "gzip";
if (!empty($_SESSION["BACKUP_MODE"])) {
	$v_backup_mode = $_SESSION["BACKUP_MODE"];
}
$backup_types = explode(",", $_SESSION["BACKUP_SYSTEM"]);
foreach ($backup_types as $backup_type) {
	if ($backup_type == "local") {
		$v_backup = "yes";
	} else {
		exec(
			HESTIA_CMD . "v-list-backup-host " . quoteshellarg($backup_type) . " json",
			$output,
			$return_var,
		);
		$v_remote_backup = json_decode(implode("", $output), true);
		unset($output);
		if (in_array($backup_type, ["ftp", "sftp"])) {
			$v_backup_host = $v_remote_backup[$backup_type]["HOST"];
			$v_backup_type = $v_remote_backup[$backup_type]["TYPE"];
			$v_backup_username = $v_remote_backup[$backup_type]["USERNAME"] ?? "";
			$v_backup_password = "";
			$v_backup_port = $v_remote_backup[$backup_type]["PORT"] ?? "";
			$v_backup_bpath = $v_remote_backup[$backup_type]["BPATH"];
			$v_backup_remote_adv = "yes";
		} elseif (in_array($backup_type, ["b2"])) {
			$v_backup_bucket = $v_remote_backup[$backup_type]["BUCKET"];
			$v_backup_type = $v_remote_backup[$backup_type]["TYPE"];
			$v_backup_application_id = $v_remote_backup[$backup_type]["B2_KEY_ID"];
			$v_backup_application_key = "";
			$v_backup_remote_adv = "yes";
		} elseif (in_array($backup_type, ["rclone"])) {
			$v_backup_type = $v_remote_backup[$backup_type]["TYPE"];
			$v_rclone_host = $v_remote_backup[$backup_type]["HOST"];
			$v_rclone_path = $v_remote_backup[$backup_type]["BPATH"];
			$v_backup_remote_adv = "yes";
		}
	}
}
if (empty($v_backup)) {
	$v_backup = "";
}
if (empty($v_backup_host)) {
	$v_backup_host = "";
}
if (empty($v_backup_type)) {
	$v_backup_type = "";
}
if (empty($v_backup_username)) {
	$v_backup_username = "";
}
if (empty($v_backup_password)) {
	$v_backup_password = "";
}
if (empty($v_backup_port)) {
	$v_backup_port = "";
}
if (empty($v_backup_bpath)) {
	$v_backup_bpath = "";
}
if (empty($v_backup_bucket)) {
	$v_backup_bucket = "";
}
if (empty($v_backup_application_id)) {
	$v_backup_application_id = "";
}
if (empty($v_backup_application_key)) {
	$v_backup_application_key = "";
}
if (empty($v_backup_remote_adv)) {
	$v_backup_remote_adv = "";
}
if (empty($v_rclone_host)) {
	$v_rclone_host = "";
}
if (empty($v_rclone_path)) {
	$v_rclone_path = "";
}

// List ssl certificate info
exec(HESTIA_CMD . "v-list-sys-hestia-ssl json", $output, $return_var);
$ssl_str = json_decode(implode("", $output), true);
unset($output);
$v_ssl_crt = $ssl_str["HESTIA"]["CRT"];
$v_ssl_key = $ssl_str["HESTIA"]["KEY"];
$v_ssl_ca = $ssl_str["HESTIA"]["CA"];
$v_ssl_subject = $ssl_str["HESTIA"]["SUBJECT"];
$v_ssl_aliases = $ssl_str["HESTIA"]["ALIASES"];
$v_ssl_not_before = $ssl_str["HESTIA"]["NOT_BEFORE"];
$v_ssl_not_after = $ssl_str["HESTIA"]["NOT_AFTER"];
$v_ssl_signature = $ssl_str["HESTIA"]["SIGNATURE"];
$v_ssl_pub_key = $ssl_str["HESTIA"]["PUB_KEY"];
$v_ssl_issuer = $ssl_str["HESTIA"]["ISSUER"];

// Check POST request
if (!empty($_POST["save"])) {
	$require_refresh = false;
	// Check token
	verify_csrf($_POST);

	// Change hostname
	if (!empty($_POST["v_hostname"]) && $v_hostname != $_POST["v_hostname"]) {
		exec(
			HESTIA_CMD . "v-change-sys-hostname " . quoteshellarg($_POST["v_hostname"]),
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
		$v_hostname = $_POST["v_hostname"];
	}
	if ($_SESSION["WEB_BACKEND"] == "php-fpm") {
		// Install/remove php versions
		if (empty($_SESSION["error_msg"])) {
			if (!empty($v_php_versions)) {
				if (!empty($_POST["v_php_versions"])) {
					$post_php = $_POST["v_php_versions"];
				}
				if (empty($post_php)) {
					$post_php = [];
				}
				array_map(function ($php_version) use ($post_php) {
					if (array_key_exists($php_version->tpl, $post_php)) {
						if (!$php_version->installed) {
							exec(
								HESTIA_CMD .
									"v-add-web-php " .
									quoteshellarg($php_version->version),
								$output,
								$return_var,
							);
							check_return_code($return_var, $output);
							unset($output);
							if (empty($_SESSION["error_msg"])) {
								$php_version->installed = true;
							}
						}
					} else {
						if ($php_version->installed && !$php_version->protected) {
							exec(
								HESTIA_CMD .
									"v-delete-web-php " .
									quoteshellarg($php_version->version),
								$output,
								$return_var,
							);
							check_return_code($return_var, $output);
							unset($output);
							if (empty($_SESSION["error_msg"])) {
								$php_version->installed = false;
							}
						}
					}

					return $php_version;
				}, $v_php_versions);
			}
		}

		if (empty($_SESSION["error_msg"])) {
			if ("php-" . $_POST["v_php_default_version"] != DEFAULT_PHP_VERSION) {
				exec(
					HESTIA_CMD .
						"v-change-sys-php " .
						quoteshellarg($_POST["v_php_default_version"]),
					$output,
					$return_var,
				);
				check_return_code($return_var, $output);
				unset($output);
				//force reload
				$require_refresh = true;
			}
		}
	}

	// Change timezone
	if (empty($_SESSION["error_msg"])) {
		if (!empty($_POST["v_timezone"])) {
			if ($v_timezone != $_POST["v_timezone"]) {
				exec(
					HESTIA_CMD . "v-change-sys-timezone " . quoteshellarg($_POST["v_timezone"]),
					$output,
					$return_var,
				);
				check_return_code($return_var, $output);
				$v_timezone = $_POST["v_timezone"];
				unset($output);
			}
		}
	}

	// Change default language
	if (empty($_SESSION["error_msg"])) {
		if (!empty($_POST["v_language"]) && $_SESSION["LANGUAGE"] != $_POST["v_language"]) {
			if (isset($_POST["v_language_update"])) {
				exec(
					HESTIA_CMD .
						"v-change-sys-language " .
						quoteshellarg($_POST["v_language"]) .
						" yes",
					$output,
					$return_var,
				);
				if (empty($_SESSION["error_msg"])) {
					$_SESSION["LANGUAGE"] = $_POST["v_language"];
				}
			}
			exec(
				HESTIA_CMD . "v-change-sys-language " . quoteshellarg($_POST["v_language"]),
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
			if (empty($_SESSION["error_msg"])) {
				$_SESSION["LANGUAGE"] = $_POST["v_language"];
			}
		}
	}

	// Update theme
	if (empty($_SESSION["error_msg"])) {
		if ($_POST["v_theme"] != $_SESSION["THEME"]) {
			exec(
				HESTIA_CMD . "v-change-sys-config-value THEME " . quoteshellarg($_POST["v_theme"]),
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
		}
	}

	// Update debug mode status
	if (empty($_SESSION["error_msg"])) {
		if (!empty($_POST["v_debug_mode"])) {
			if ($_POST["v_debug_mode"] == "on") {
				$_POST["v_debug_mode"] = "true";
			} else {
				$_POST["v_debug_mode"] = "false";
			}
		} else {
			$_POST["v_debug_mode"] = "false";
		}

		if ($_POST["v_debug_mode"] != $_SESSION["DEBUG_MODE"]) {
			exec(
				HESTIA_CMD .
					"v-change-sys-config-value DEBUG_MODE " .
					quoteshellarg($_POST["v_debug_mode"]),
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
			$v_debug_mode_adv = "yes";
		}
	}

	// Enable/Disable Quick App Installer
	if (empty($_SESSION["error_msg"])) {
		if ($_POST["v_plugin_app_installer"] != $_SESSION["PLUGIN_APP_INSTALLER"]) {
			if ($_POST["v_plugin_app_installer"] == "true") {
				$_POST["v_plugin_app_installer"] = "true";
			} else {
				$_POST["v_plugin_app_installer"] = "false";
			}
			exec(
				HESTIA_CMD .
					"v-change-sys-config-value PLUGIN_APP_INSTALLER " .
					quoteshellarg($_POST["v_plugin_app_installer"]),
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
		}
	}

	// Update experimental features status
	if (
		empty($_SESSION["error_msg"]) &&
		$_POST["v_experimental_features"] != $_SESSION["POLICY_SYSTEM_ENABLE_BACON"]
	) {
		if ($_POST["v_experimental_features"] == "on") {
			$_POST["v_experimental_features"] = "true";
		} else {
			$_POST["v_experimental_features"] = "false";
		}
		if ($_POST["v_experimental_features"] != $_SESSION["POLICY_SYSTEM_ENABLE_BACON"]) {
			exec(
				HESTIA_CMD .
					"v-change-sys-config-value POLICY_SYSTEM_ENABLE_BACON " .
					quoteshellarg($_POST["v_experimental_features"]),
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
			$v_debug_mode_adv = "yes";
		}
		if (
			$_POST["v_policy_user_view_suspended"] != $_SESSION["POLICY_SYSTEM_ENABLE_BACON"] &&
			$_POST["v_experimental_features"] == "false"
		) {
			//disable preview mode
			exec(
				HESTIA_CMD .
					"v-change-sys-config-value POLICY_USER_VIEW_SUSPENDED " .
					quoteshellarg($_POST["v_policy_user_view_suspended"]),
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
		}
	}

	// Set File Manager support
	if (empty($_SESSION["error_msg"])) {
		if (
			!empty($_POST["v_filemanager"]) &&
			$_SESSION["FILE_MANAGER"] != $_POST["v_filemanager"]
		) {
			if ($_POST["v_filemanager"] == "true") {
				exec(HESTIA_CMD . "v-add-sys-filemanager", $output, $return_var);
				check_return_code($return_var, $output);
				unset($output);
				if (empty($_SESSION["error_msg"])) {
					$_SESSION["FILE_MANAGER"] = "true";
				}
			} else {
				exec(HESTIA_CMD . "v-delete-sys-filemanager", $output, $return_var);
				check_return_code($return_var, $output);
				unset($output);
				if (empty($_SESSION["error_msg"])) {
					$_SESSION["FILE_MANAGER"] = "false";
				}
			}
		}
	}
	// Set phpMyAdmin SSO key
	if (empty($_SESSION["error_msg"])) {
		if (!empty($_POST["v_phpmyadmin_key"])) {
			if ($_POST["v_phpmyadmin_key"] == "yes" && $_SESSION["PHPMYADMIN_KEY"] == "") {
				exec(HESTIA_CMD . "v-add-sys-pma-sso quiet", $output, $return_var);
				check_return_code($return_var, $output);
				unset($output);
				if (empty($_SESSION["error_msg"])) {
					$_SESSION["PHPMYADMIN_KEY"] != "";
				}
			} elseif ($_POST["v_phpmyadmin_key"] == "no" && $_SESSION["PHPMYADMIN_KEY"] != "") {
				exec(HESTIA_CMD . "v-delete-sys-pma-sso quiet", $output, $return_var);
				check_return_code($return_var, $output);
				unset($output);
				if (empty($_SESSION["error_msg"])) {
					$_SESSION["PHPMYADMIN_KEY"] = "";
				}
			}
		}
	}

	// Set disk_quota support
	if (empty($_SESSION["error_msg"])) {
		if (!empty($_POST["v_quota"]) && $_SESSION["DISK_QUOTA"] != $_POST["v_quota"]) {
			if ($_POST["v_quota"] == "yes") {
				exec(HESTIA_CMD . "v-add-sys-quota", $output, $return_var);
				check_return_code($return_var, $output);
				unset($output);
				if (empty($_SESSION["error_msg"])) {
					$_SESSION["DISK_QUOTA"] = "yes";
				}
			} else {
				exec(HESTIA_CMD . "v-delete-sys-quota", $output, $return_var);
				check_return_code($return_var, $output);
				unset($output);
				if (empty($_SESSION["error_msg"])) {
					$_SESSION["DISK_QUOTA"] = "no";
				}
			}
		}
	}

	// Set firewall support
	if (empty($_SESSION["error_msg"])) {
		if ($_SESSION["FIREWALL_SYSTEM"] == "iptables") {
			$v_firewall = "yes";
		}
		if ($_SESSION["FIREWALL_SYSTEM"] != "iptables") {
			$v_firewall = "no";
		}
		if (!empty($_POST["v_firewall"]) && $v_firewall != $_POST["v_firewall"]) {
			if ($_POST["v_firewall"] == "yes") {
				exec(HESTIA_CMD . "v-add-sys-firewall", $output, $return_var);
				check_return_code($return_var, $output);
				unset($output);
				if (empty($_SESSION["error_msg"])) {
					$_SESSION["FIREWALL_SYSTEM"] = "iptables";
				}
			} else {
				exec(HESTIA_CMD . "v-delete-sys-firewall", $output, $return_var);
				check_return_code($return_var, $output);
				unset($output);
				if (empty($_SESSION["error_msg"])) {
					$_SESSION["FIREWALL_SYSTEM"] = "";
				}
			}
		}
	}

	// Update mysql pasword
	if (empty($_SESSION["error_msg"])) {
		if (!empty($_POST["v_mysql_password"])) {
			exec(
				HESTIA_CMD .
					"v-change-database-host-password mysql localhost root " .
					quoteshellarg($_POST["v_mysql_password"]),
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
			$v_db_adv = "yes";
		}
	}
	if (!empty($_SESSION["MAIL_SYSTEM"])) {
		// Update webmail url
		if (empty($_SESSION["error_msg"])) {
			if ($_SESSION["WEBMAIL_SYSTEM"] != "") {
				if ($_POST["v_webmail_alias"] != $_SESSION["WEBMAIL_ALIAS"]) {
					exec(
						HESTIA_CMD .
							"v-change-sys-webmail " .
							quoteshellarg($_POST["v_webmail_alias"]),
						$output,
						$return_var,
					);
					check_return_code($return_var, $output);
					unset($output);
					$v_mail_adv = "yes";
				}
			}
		}
	}

	// Update system wide smtp relay
	if (empty($_SESSION["error_msg"])) {
		if (isset($_POST["v_smtp_relay"]) && !empty($_POST["v_smtp_relay_host"])) {
			if (
				$_POST["v_smtp_relay_host"] != $v_smtp_relay_host ||
				$_POST["v_smtp_relay_user"] != $v_smtp_relay_user ||
				$_POST["v_smtp_relay_port"] != $v_smtp_relay_port ||
				!empty($_POST["v_smtp_relay_pass"])
			) {
				$v_smtp_relay = true;
				$v_smtp_relay_host = quoteshellarg($_POST["v_smtp_relay_host"]);
				$v_smtp_relay_user = quoteshellarg($_POST["v_smtp_relay_user"]);
				$v_smtp_relay_pass = quoteshellarg($_POST["v_smtp_relay_pass"]);
				if (!empty($_POST["v_smtp_relay_port"])) {
					$v_smtp_relay_port = quoteshellarg($_POST["v_smtp_relay_port"]);
				} else {
					$v_smtp_relay_port = "587";
				}
				exec(
					HESTIA_CMD .
						"v-add-sys-smtp-relay " .
						$v_smtp_relay_host .
						" " .
						$v_smtp_relay_user .
						" " .
						$v_smtp_relay_pass .
						" " .
						$v_smtp_relay_port,
					$output,
					$return_var,
				);
				check_return_code($return_var, $output);
				unset($output);
			}
		}
		if (!isset($_POST["v_smtp_relay"]) && $v_smtp_relay == true) {
			$v_smtp_relay = false;
			$v_smtp_relay_host = $v_smtp_relay_user = $v_smtp_relay_pass = $v_smtp_relay_port = "";
			exec(HESTIA_CMD . "v-delete-sys-smtp-relay", $output, $return_var);
			check_return_code($return_var, $output);
			unset($output);
		}
	}

	// Update phpMyAdmin url
	if (empty($_SESSION["error_msg"])) {
		if ($_POST["v_mysql_url"] != $_SESSION["DB_PMA_ALIAS"]) {
			exec(
				HESTIA_CMD . "v-change-sys-db-alias pma " . quoteshellarg($_POST["v_mysql_url"]),
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
			$v_db_adv = "yes";
		}
	}

	// Update phpPgAdmin url
	if (empty($_SESSION["error_msg"])) {
		if (empty($_POST["v_pgsql_url"])) {
			$_POST["v_pgsql_url"] = "";
		}
		if ($_POST["v_pgsql_url"] != $_SESSION["DB_PGA_ALIAS"]) {
			exec(
				HESTIA_CMD . "v-change-sys-db-alias pga " . quoteshellarg($_POST["v_pgsql_url"]),
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
			$v_db_adv = "yes";
		}
	}

	// Update send notification setting
	if (empty($_SESSION["error_msg"])) {
		if ($_SESSION["UPGRADE_SEND_EMAIL"] == "true") {
			$ugrade_send_mail = "on";
		} else {
			$ugrade_send_mail = "";
		}
		if (empty($_POST["v_upgrade_send_notification_email"])) {
			$_POST["v_upgrade_send_notification_email"] = "";
		}

		if ($_POST["v_upgrade_send_notification_email"] != $ugrade_send_mail) {
			if ($_POST["v_upgrade_send_notification_email"] == "on") {
				$_POST["v_upgrade_send_notification_email"] = "true";
			} else {
				$_POST["v_upgrade_send_notification_email"] = "false";
			}
			exec(
				HESTIA_CMD .
					"v-change-sys-config-value UPGRADE_SEND_EMAIL " .
					quoteshellarg($_POST["v_upgrade_send_notification_email"]),
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
			$v_upgrade_notification_adv = "yes";
		}
	}

	// Update send log by email setting
	if (empty($_SESSION["error_msg"])) {
		if ($_SESSION["UPGRADE_SEND_EMAIL_LOG"] == "true") {
			$send_email_log = "on";
		} else {
			$send_email_log = "";
		}
		if (empty($_POST["v_upgrade_send_email_log"])) {
			$_POST["v_upgrade_send_email_log"] = "";
		}
		if ($_POST["v_upgrade_send_email_log"] != $send_email_log) {
			if ($_POST["v_upgrade_send_email_log"] == "on") {
				$_POST["v_upgrade_send_email_log"] = "true";
			} else {
				$_POST["v_upgrade_send_email_log"] = "false";
			}
			exec(
				HESTIA_CMD .
					"v-change-sys-config-value UPGRADE_SEND_EMAIL_LOG " .
					quoteshellarg($_POST["v_upgrade_send_email_log"]),
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
			$v_upgrade_send_log_adv = "yes";
		}
	}

	// Disable local backup
	if (empty($_SESSION["error_msg"])) {
		if ($_POST["v_backup"] == "no" && $v_backup == "yes") {
			exec(HESTIA_CMD . "v-delete-backup-host local", $output, $return_var);
			check_return_code($return_var, $output);
			unset($output);
			if (empty($_SESSION["error_msg"])) {
				$v_backup = "no";
			}
			$v_backup_adv = "yes";
		}
	}

	// Enable local backups
	if (empty($_SESSION["error_msg"])) {
		if ($_POST["v_backup"] == "yes" && $v_backup != "yes") {
			exec(HESTIA_CMD . "v-add-backup-host local", $output, $return_var);
			check_return_code($return_var, $output);
			unset($output);
			if (empty($_SESSION["error_msg"])) {
				$v_backup = "yes";
			}
			$v_backup_adv = "yes";
		}
	}

	// Change backup gzip level
	if (empty($_SESSION["error_msg"])) {
		if ($_POST["v_backup_gzip"] != $v_backup_gzip) {
			if ($_POST["v_backup_mode"] == "gzip") {
				$_POST["v_backup_gzip"] = 9;
			}
			exec(
				HESTIA_CMD .
					"v-change-sys-config-value BACKUP_GZIP " .
					quoteshellarg($_POST["v_backup_gzip"]),
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
			if (empty($_SESSION["error_msg"])) {
				$v_backup_gzip = $_POST["v_backup_gzip"];
			}
			$v_backup_adv = "yes";
		}
	}

	// Change backup mode
	if (empty($_SESSION["error_msg"])) {
		if ($_POST["v_backup_mode"] != $v_backup_mode) {
			exec(
				HESTIA_CMD .
					"v-change-sys-config-value BACKUP_MODE " .
					quoteshellarg($_POST["v_backup_mode"]),
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
			if (empty($_SESSION["error_msg"])) {
				$v_backup_mode = $_POST["v_backup_mode"];
			}
			$v_backup_adv = "yes";
			if ($_POST["v_backup_mode"] == "gzip") {
				$_POST["v_backup_gzip"] = 9;
				if (empty($_SESSION["error_msg"])) {
					$v_backup_gzip = $_POST["v_backup_gzip"];
				}
				exec(
					HESTIA_CMD .
						"v-change-sys-config-value BACKUP_GZIP " .
						quoteshellarg($_POST["v_backup_gzip"]),
					$output,
					$return_var,
				);
			}
		}
	}

	// Change backup path
	if (empty($_SESSION["error_msg"])) {
		if (empty($_POST["v_backup_dir"])) {
			$_POST["v_backup_dir"] = "";
		}
		if ($_POST["v_backup_dir"] != $v_backup_dir) {
			/*
				See #1655
				exec (HESTIA_CMD."v-change-sys-config-value BACKUP ".quoteshellarg($_POST['v_backup_dir']), $output, $return_var);
				check_return_code($return_var,$output);
				unset($output);
				*/
			if (empty($_SESSION["error_msg"])) {
				$v_backup_dir = $_POST["v_backup_dir"];
			}
			#$v_backup_adv = 'yes';
		}
	}

	// Add remote backup host
	if (empty($_SESSION["error_msg"])) {
		if (
			$v_backup_host == "" &&
			$v_backup_bucket == "" &&
			(!empty($_POST["v_backup_host"]) || !empty($_POST["v_backup_bucket"]))
		) {
			if (in_array($_POST["v_backup_type"], ["ftp", "sftp"])) {
				$v_backup_host = quoteshellarg($_POST["v_backup_host"]);
				$v_backup_port = quoteshellarg($_POST["v_backup_port"]);
				$v_backup_type = quoteshellarg($_POST["v_backup_type"]);
				$v_backup_username = quoteshellarg($_POST["v_backup_username"]);
				$v_backup_password = quoteshellarg($_POST["v_backup_password"]);
				$v_backup_bpath = quoteshellarg($_POST["v_backup_bpath"]);
				exec(
					HESTIA_CMD .
						"v-add-backup-host " .
						$v_backup_type .
						" " .
						$v_backup_host .
						" " .
						$v_backup_username .
						" " .
						$v_backup_password .
						" " .
						$v_backup_bpath .
						" " .
						$v_backup_port,
					$output,
					$return_var,
				);
				check_return_code($return_var, $output);
				unset($output);
				if (empty($_SESSION["error_msg"])) {
					$v_backup_host = $_POST["v_backup_host"];
				}
				if (empty($_SESSION["error_msg"])) {
					$v_backup_type = $_POST["v_backup_type"];
				}
				if (empty($_SESSION["error_msg"])) {
					$v_backup_username = $_POST["v_backup_username"];
				}
				if (empty($_SESSION["error_msg"])) {
					$v_backup_password = $_POST["v_backup_password"];
				}
				if (empty($_SESSION["error_msg"])) {
					$v_backup_bpath = $_POST["v_backup_bpath"];
				}
				if (empty($_SESSION["error_msg"])) {
					$v_backup_port = $_POST["v_backup_port"];
				}
				$v_backup_new = "yes";
				$v_backup_adv = "yes";
				$v_backup_remote_adv = "yes";
			} elseif (in_array($_POST["v_backup_type"], ["b2"])) {
				$v_backup_type = quoteshellarg($_POST["v_backup_type"]);
				$v_backup_bucket = quoteshellarg($_POST["v_backup_bucket"]);
				$v_backup_application_id = quoteshellarg($_POST["v_backup_application_id"]);
				$v_backup_application_key = quoteshellarg($_POST["v_backup_application_key"]);
				exec(
					HESTIA_CMD .
						"v-add-backup-host " .
						$v_backup_type .
						" " .
						$v_backup_bucket .
						" " .
						$v_backup_application_id .
						" " .
						$v_backup_application_key,
					$output,
					$return_var,
				);
				check_return_code($return_var, $output);
				unset($output);
				if (empty($_SESSION["error_msg"])) {
					$v_backup_bucket = quoteshellarg($_POST["v_backup_bucket"]);
				}
				if (empty($_SESSION["error_msg"])) {
					$v_backup_application_id = quoteshellarg($_POST["v_backup_application_id"]);
				}
				if (empty($_SESSION["error_msg"])) {
					$v_backup_application_key = quoteshellarg($_POST["v_backup_application_key"]);
				}
				$v_backup_new = "yes";
				$v_backup_adv = "yes";
				$v_backup_remote_adv = "yes";
			}
		}
		if (
			$v_rclone_host == "" &&
			!empty($_POST["v_rclone_host"]) &&
			$_POST["v_backup_type"] == "rclone"
		) {
			$v_rclone_host = quoteshellarg($_POST["v_rclone_host"]);
			$v_backup_type = quoteshellarg($_POST["v_backup_type"]);
			$v_rclone_path = quoteshellarg($_POST["v_rclone_path"]);
			exec(
				HESTIA_CMD .
					"v-add-backup-host " .
					$v_backup_type .
					" " .
					$v_rclone_host .
					" '' '' " .
					$v_rclone_path,
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
			$v_backup_new = "yes";
			$v_backup_adv = "yes";
			$v_backup_remote_adv = "yes";
		}
	}

	// Change remote backup host type
	if (empty($_SESSION["error_msg"])) {
		if (
			!empty($_POST["v_backup_host"]) &&
			$_POST["v_backup_type"] != $v_backup_type &&
			$v_backup_type != ""
		) {
			exec(
				HESTIA_CMD . "v-delete-backup-host " . quoteshellarg($v_backup_type),
				$output,
				$return_var,
			);
			unset($output);
			if (in_array($_POST["v_backup_type"], ["ftp", "sftp"])) {
				$v_backup_host = quoteshellarg($_POST["v_backup_host"]);
				$v_backup_port = quoteshellarg($_POST["v_backup_port"]);
				$v_backup_type = quoteshellarg($_POST["v_backup_type"]);
				$v_backup_username = quoteshellarg($_POST["v_backup_username"]);
				$v_backup_password = quoteshellarg($_POST["v_backup_password"]);
				$v_backup_bpath = quoteshellarg($_POST["v_backup_bpath"]);
				exec(
					HESTIA_CMD .
						"v-add-backup-host " .
						$v_backup_type .
						" " .
						$v_backup_host .
						" " .
						$v_backup_username .
						" " .
						$v_backup_password .
						" " .
						$v_backup_bpath .
						" " .
						$v_backup_port,
					$output,
					$return_var,
				);
				check_return_code($return_var, $output);
				unset($output);
				if (empty($_SESSION["error_msg"])) {
					$v_backup_host = $_POST["v_backup_host"];
				}
				if (empty($_SESSION["error_msg"])) {
					$v_backup_type = $_POST["v_backup_type"];
				}
				if (empty($_SESSION["error_msg"])) {
					$v_backup_username = $_POST["v_backup_username"];
				}
				if (empty($_SESSION["error_msg"])) {
					$v_backup_password = $_POST["v_backup_password"];
				}
				if (empty($_SESSION["error_msg"])) {
					$v_backup_bpath = $_POST["v_backup_bpath"];
				}
				if (empty($_SESSION["error_msg"])) {
					$v_backup_port = $_POST["v_backup_port"];
				}
				$v_backup_adv = "yes";
				$v_backup_remote_adv = "yes";
			} elseif (in_array($_POST["v_backup_type"], ["b2"])) {
				$v_backup_bucket = quoteshellarg($_POST["v_backup_bucket"]);
				$v_backup_application_id = quoteshellarg($_POST["v_backup_application_id"]);
				$v_backup_application_key = quoteshellarg($_POST["v_backup_application_key"]);
				exec(
					HESTIA_CMD .
						"v-add-backup-host " .
						$v_backup_type .
						" " .
						$v_backup_bucket .
						" " .
						$v_backup_application_id .
						" " .
						$v_backup_application_key,
					$output,
					$return_var,
				);
				check_return_code($return_var, $output);
				unset($output);
				$v_backup_type = quoteshellarg($_POST["v_backup_type"]);
				if (empty($_SESSION["error_msg"])) {
					$v_backup_bucket = quoteshellarg($_POST["v_backup_bucket"]);
				}
				if (empty($_SESSION["error_msg"])) {
					$v_backup_application_id = quoteshellarg($_POST["v_backup_application_id"]);
				}
				if (empty($_SESSION["error_msg"])) {
					$v_backup_application_key = quoteshellarg($_POST["v_backup_application_key"]);
				}
				$v_backup_adv = "yes";
				$v_backup_remote_adv = "yes";
			}
		}
	}

	// Change remote backup host
	if (empty($_SESSION["error_msg"])) {
		if ($_POST["v_backup_type"] == $v_backup_type && !isset($v_backup_new)) {
			if (in_array($_POST["v_backup_type"], ["ftp", "sftp"])) {
				if (
					$_POST["v_backup_host"] != $v_backup_host ||
					$_POST["v_backup_username"] != $v_backup_username ||
					$_POST["v_backup_password"] != $v_backup_password ||
					($_POST["v_backup_bpath"] != $v_backup_bpath ||
						$_POST["v_backup_port"] != $v_backup_port)
				) {
					$v_backup_host = quoteshellarg($_POST["v_backup_host"]);
					$v_backup_port = quoteshellarg($_POST["v_backup_port"]);
					$v_backup_type = quoteshellarg($_POST["v_backup_type"]);
					$v_backup_username = quoteshellarg($_POST["v_backup_username"]);
					$v_backup_password = quoteshellarg($_POST["v_backup_password"]);
					$v_backup_bpath = quoteshellarg($_POST["v_backup_bpath"]);
					exec(
						HESTIA_CMD .
							"v-add-backup-host " .
							$v_backup_type .
							" " .
							$v_backup_host .
							" " .
							$v_backup_username .
							" " .
							$v_backup_password .
							" " .
							$v_backup_bpath .
							" " .
							$v_backup_port,
						$output,
						$return_var,
					);
					check_return_code($return_var, $output);
					unset($output);
					if (empty($_SESSION["error_msg"])) {
						$v_backup_host = $_POST["v_backup_host"];
					}
					if (empty($_SESSION["error_msg"])) {
						$v_backup_type = $_POST["v_backup_type"];
					}
					if (empty($_SESSION["error_msg"])) {
						$v_backup_username = $_POST["v_backup_username"];
					}
					if (empty($_SESSION["error_msg"])) {
						$v_backup_password = $_POST["v_backup_password"];
					}
					if (empty($_SESSION["error_msg"])) {
						$v_backup_bpath = $_POST["v_backup_bpath"];
					}
					if (empty($_SESSION["error_msg"])) {
						$v_backup_port = $_POST["v_backup_port"];
					}
					$v_backup_adv = "yes";
					$v_backup_remote_adv = "yes";
				}
			} elseif (in_array($_POST["v_backup_type"], ["b2"])) {
				if (
					$_POST["v_backup_bucket"] != $v_backup_bucket ||
					$_POST["v_backup_application_key"] != $v_backup_application_key ||
					$_POST["v_backup_application_id"] != $v_backup_application_id
				) {
					$v_backup_type = quoteshellarg($_POST["v_backup_type"]);
					$v_backup_bucket = quoteshellarg($_POST["v_backup_bucket"]);
					$v_backup_application_id = quoteshellarg($_POST["v_backup_application_id"]);
					$v_backup_application_key = quoteshellarg($_POST["v_backup_application_key"]);
					exec(
						HESTIA_CMD .
							"v-add-backup-host " .
							$v_backup_type .
							" " .
							$v_backup_bucket .
							" " .
							$v_backup_application_id .
							" " .
							$v_backup_application_key,
						$output,
						$return_var,
					);
					check_return_code($return_var, $output);
					unset($output);
					if (empty($_SESSION["error_msg"])) {
						$v_backup_bucket = quoteshellarg($_POST["v_backup_bucket"]);
					}
					if (empty($_SESSION["error_msg"])) {
						$v_backup_application_id = quoteshellarg($_POST["v_backup_application_id"]);
					}
					if (empty($_SESSION["error_msg"])) {
						$v_backup_application_key = quoteshellarg(
							$_POST["v_backup_application_key"],
						);
					}
					$v_backup_adv = "yes";
					$v_backup_remote_adv = "yes";
				}
			}
		}
	}

	// Delete remote backup host
	if (empty($_SESSION["error_msg"])) {
		if (empty($_POST["v_backup_remote_adv"]) && $v_backup_remote_adv != "") {
			exec(
				HESTIA_CMD . "v-delete-backup-host " . quoteshellarg($v_backup_type),
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
			if (empty($_SESSION["error_msg"])) {
				$v_backup_host = "";
			}
			if (empty($_SESSION["error_msg"])) {
				$v_backup_type = "";
			}
			if (empty($_SESSION["error_msg"])) {
				$v_backup_username = "";
			}
			if (empty($_SESSION["error_msg"])) {
				$v_backup_password = "";
			}
			if (empty($_SESSION["error_msg"])) {
				$v_backup_bpath = "";
			}
			if (empty($_SESSION["error_msg"])) {
				$v_backup_bucket = "";
			}
			if (empty($_SESSION["error_msg"])) {
				$v_backup_application_id = "";
			}
			if (empty($_SESSION["error_msg"])) {
				$v_backup_application_key = "";
			}
			$v_backup_adv = "";
			$v_backup_remote_adv = "";
		}
	}

	// Change INACTIVE_SESSION_TIMEOUT
	if (empty($_SESSION["error_msg"])) {
		if ($_POST["v_inactive_session_timeout"] != $_SESSION["INACTIVE_SESSION_TIMEOUT"]) {
			if ($_POST["v_inactive_session_timeout"] < 1) {
				$_SESSION["error_msg"] = _("Inactive session timeout can not lower than 1 minute.");
			} else {
				exec(
					HESTIA_CMD .
						"v-change-sys-config-value INACTIVE_SESSION_TIMEOUT " .
						quoteshellarg($_POST["v_inactive_session_timeout"]),
					$output,
					$return_var,
				);
				check_return_code($return_var, $output);
				unset($output);
				if (empty($_SESSION["error_msg"])) {
					$v_inactive_session_timeout = $_POST["v_inactive_session_timeout"];
				}
			}
			$v_security_adv = "yes";
		}
	}

	// Change POLICY_CSRF_STRICTNESS
	if (empty($_SESSION["error_msg"])) {
		if ($_POST["v_policy_csrf_strictness"] != $_SESSION["POLICY_CSRF_STRICTNESS"]) {
			exec(
				HESTIA_CMD .
					"v-change-sys-config-value POLICY_CSRF_STRICTNESS " .
					quoteshellarg($_POST["v_policy_csrf_strictness"]),
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
			if (empty($_SESSION["error_msg"])) {
				$v_policy_csrf_strictness = $_POST["v_inactive_session_timeout"];
			}
			$v_security_adv = "yes";
		}
	}

	// Change ENFORCE_SUBDOMAIN_OWNERSHIP
	if (empty($_SESSION["error_msg"])) {
		if ($_POST["v_enforce_subdomain_ownership"] != $_SESSION["ENFORCE_SUBDOMAIN_OWNERSHIP"]) {
			exec(
				HESTIA_CMD .
					"v-change-sys-config-value ENFORCE_SUBDOMAIN_OWNERSHIP " .
					quoteshellarg($_POST["v_enforce_subdomain_ownership"]),
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
			if (empty($_SESSION["error_msg"])) {
				$v_enforce_subdomain_ownership = $_POST["v_enforce_subdomain_ownership"];
			}
			$v_security_adv = "yes";
		}
	}

	// Change POLICY_USER_EDIT_DETAILS
	if (empty($_SESSION["error_msg"])) {
		if ($_POST["v_policy_user_edit_details"] != $_SESSION["POLICY_USER_EDIT_DETAILS"]) {
			exec(
				HESTIA_CMD .
					"v-change-sys-config-value POLICY_USER_EDIT_DETAILS " .
					quoteshellarg($_POST["v_policy_user_edit_details"]),
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
			if (empty($_SESSION["error_msg"])) {
				$v_policy_user_edit_details = $_POST["v_policy_user_edit_details"];
			}
			$v_security_adv = "yes";
		}
	}

	// Change POLICY_USER_EDIT_WEB_TEMPLATES
	if (empty($_SESSION["error_msg"])) {
		if (
			$_POST["v_policy_user_edit_web_templates"] !=
			$_SESSION["POLICY_USER_EDIT_WEB_TEMPLATES"]
		) {
			exec(
				HESTIA_CMD .
					"v-change-sys-config-value POLICY_USER_EDIT_WEB_TEMPLATES " .
					quoteshellarg($_POST["v_policy_user_edit_web_templates"]),
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
			if (empty($_SESSION["error_msg"])) {
				$v_policy_user_edit_details = $_POST["v_policy_user_edit_web_templates"];
			}
			$v_security_adv = "yes";
		}
	}

	// Change POLICY_USER_EDIT_DNS_TEMPLATES
	if (empty($_SESSION["error_msg"])) {
		if (
			$_POST["v_policy_user_edit_dns_templates"] !=
			$_SESSION["POLICY_USER_EDIT_DNS_TEMPLATES"]
		) {
			exec(
				HESTIA_CMD .
					"v-change-sys-config-value POLICY_USER_EDIT_DNS_TEMPLATES " .
					quoteshellarg($_POST["v_policy_user_edit_dns_templates"]),
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
			if (empty($_SESSION["error_msg"])) {
				$v_policy_user_edit_details = $_POST["v_policy_user_edit_dns_templates"];
			}
			$v_security_adv = "yes";
		}
	}

	if (
		$_POST["v_api_system"] != $_SESSION["API_SYSTEM"] ||
		$_POST["v_api"] != $_SESSION["API"] ||
		$_POST["v_api_allowed_ip"] != $_SESSION["API_ALLOWED_IP"]
	) {
		if (empty($_SESSION["error_msg"])) {
			if ($_POST["v_api"] == "no" && $_POST["v_api_system"] === 0) {
				exec(HESTIA_CMD . "v-change-sys-api 'disable'", $output, $return_var);
				check_return_code($return_var, $output);
				unset($output);
			}
			if (
				$_POST["v_api"] == "yes" ||
				($_POST["v_api_system"] !== 0 &&
					$_POST["v_api_system"] != $_SESSION["API_SYSTEM"]) ||
				$_POST["v_api"] != $_SESSION["API"]
			) {
				exec(HESTIA_CMD . "v-change-sys-api 'enable'", $output, $return_var);
				check_return_code($return_var, $output);
				unset($output);
			}
		}
		if (empty($_SESSION["error_msg"])) {
			if ($_POST["v_api_system"] != $_SESSION["API_SYSTEM"]) {
				exec(
					HESTIA_CMD .
						"v-change-sys-config-value API_SYSTEM " .
						quoteshellarg($_POST["v_api_system"]),
					$output,
					$return_var,
				);
				check_return_code($return_var, $output);
				unset($output);
				if (empty($_SESSION["error_msg"])) {
					$v_policy_user_edit_details = $_POST["v_api_system"];
				}
				$v_security_adv = "yes";
			}
		}

		// Change API access
		if (empty($_SESSION["error_msg"])) {
			if ($_POST["v_api"] != $_SESSION["API"]) {
				$api_status = "no";
				if ($_POST["v_api"] == "yes") {
					$api_status = "yes";
				}
				exec(
					HESTIA_CMD . "v-change-sys-config-value API " . quoteshellarg($api_status),
					$output,
					$return_var,
				);
				check_return_code($return_var, $output);
				unset($output);
				if (empty($_SESSION["error_msg"])) {
					$v_api = $_POST["v_api"];
				}
				$v_security_adv = "yes";
			}
		}

		// Change API allowed IPs
		if (empty($_SESSION["error_msg"])) {
			if ($_POST["v_api_allowed_ip"] != $_SESSION["API_ALLOWED_IP"]) {
				$ips = [];
				foreach (explode("\n", $_POST["v_api_allowed_ip"]) as $ip) {
					if (trim($ip) != "allow-all") {
						if (filter_var(trim($ip), FILTER_VALIDATE_IP)) {
							$ips[] = trim($ip);
						}
					} else {
						$ips[] = trim($ip);
					}
				}
				if (implode(",", $ips) != $_SESSION["API_ALLOWED_IP"]) {
					exec(
						HESTIA_CMD .
							"v-change-sys-config-value API_ALLOWED_IP " .
							quoteshellarg(implode(",", $ips)),
						$output,
						$return_var,
					);
					check_return_code($return_var, $output);
					unset($output);
					if (empty($_SESSION["error_msg"])) {
						$v_api_allowed_ip = $_POST["v_api_allowed_ip"];
					}
					$v_security_adv = "yes";
				}
			}
		}
	}

	// Change POLICY_USER_VIEW_LOGS
	if (empty($_SESSION["error_msg"])) {
		if ($_POST["v_policy_user_view_logs"] != $_SESSION["POLICY_USER_VIEW_LOGS"]) {
			exec(
				HESTIA_CMD .
					"v-change-sys-config-value POLICY_USER_VIEW_LOGS " .
					quoteshellarg($_POST["v_policy_user_view_logs"]),
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
			if (empty($_SESSION["error_msg"])) {
				$v_policy_user_view_logs = $_POST["v_policy_user_view_logs"];
			}
			$v_security_adv = "yes";
		}
	}

	// Change POLICY_USER_DELETE_LOGS
	if (empty($_SESSION["error_msg"])) {
		if ($_POST["v_policy_user_delete_logs"] != $_SESSION["POLICY_USER_DELETE_LOGS"]) {
			exec(
				HESTIA_CMD .
					"v-change-sys-config-value POLICY_USER_DELETE_LOGS " .
					quoteshellarg($_POST["v_policy_user_delete_logs"]),
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
			if (empty($_SESSION["error_msg"])) {
				$v_policy_user_delete_logs = $_POST["v_policy_user_delete_logs"];
			}
			$v_security_adv = "yes";
		}
	}

	// Change POLICY_SYSTEM_PASSWORD_RESET
	if (empty($_SESSION["error_msg"])) {
		if ($_POST["v_policy_system_password_reset"] != $_SESSION["POLICY_SYSTEM_PASSWORD_RESET"]) {
			exec(
				HESTIA_CMD .
					"v-change-sys-config-value POLICY_SYSTEM_PASSWORD_RESET " .
					quoteshellarg($_POST["v_policy_system_password_reset"]),
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
			if (empty($_SESSION["error_msg"])) {
				$v_policy_system_password_reset = $_POST["v_policy_system_password_reset"];
			}
			$v_security_adv = "yes";
		}
	}

	// Change POLICY_SYSTEM_PROTECTED_ADMIN
	if (empty($_SESSION["error_msg"])) {
		if (!empty($_POST["v_policy_system_protected_admin"])) {
			if (
				$_POST["v_policy_system_protected_admin"] !=
				$_SESSION["POLICY_SYSTEM_PROTECTED_ADMIN"]
			) {
				exec(
					HESTIA_CMD .
						"v-change-sys-config-value POLICY_SYSTEM_PROTECTED_ADMIN " .
						quoteshellarg($_POST["v_policy_system_protected_admin"]),
					$output,
					$return_var,
				);
				check_return_code($return_var, $output);
				unset($output);
				if (empty($_SESSION["error_msg"])) {
					$v_policy_system_protected_admin = $_POST["v_policy_system_protected_admin"];
				}
				$v_security_adv = "yes";
			}
		}
	}

	// Change POLICY_USER_VIEW_SUSPENDED
	if (empty($_SESSION["error_msg"])) {
		if (!empty($_POST["v_policy_user_view_suspended"])) {
			if (
				$_POST["v_policy_user_view_suspended"] != $_SESSION["POLICY_USER_VIEW_SUSPENDED"] &&
				!empty($_SESSION["POLICY_USER_VIEW_SUSPENDED"])
			) {
				exec(
					HESTIA_CMD .
						"v-change-sys-config-value POLICY_USER_VIEW_SUSPENDED " .
						quoteshellarg($_POST["v_policy_user_view_suspended"]),
					$output,
					$return_var,
				);
				check_return_code($return_var, $output);
				unset($output);
				if (empty($_SESSION["error_msg"])) {
					$v_policy_user_view_suspended = $_POST["v_policy_user_view_suspended"];
				}
				$v_security_adv = "yes";
			}
		}
	}

	// Change POLICY_USER_CHANGE_THEME
	if (empty($_SESSION["error_msg"])) {
		if (empty($_POST["v_policy_user_change_theme"])) {
			$_POST["v_policy_user_change_theme"] = "";
		}
		if ($_POST["v_policy_user_change_theme"] == "on") {
			$_POST["v_policy_user_change_theme"] = "no";
		} else {
			$_POST["v_policy_user_change_theme"] = "yes";
		}
		if ($_POST["v_policy_user_change_theme"] != $_SESSION["POLICY_USER_CHANGE_THEME"]) {
			exec(
				HESTIA_CMD .
					"v-change-sys-config-value POLICY_USER_CHANGE_THEME " .
					quoteshellarg($_POST["v_policy_user_change_theme"]),
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
			if ($_POST["v_policy_user_change_theme"]) {
				unset($_SESSION["userTheme"]);
				$require_refresh = true;
			}
			if (empty($_SESSION["error_msg"])) {
				$v_policy_user_change_theme = $_POST["v_policy_user_change_theme"];
			}
		}
	}

	// Change POLICY_SYSTEM_HIDE_ADMIN
	if (empty($_SESSION["error_msg"])) {
		if (!empty($_POST["v_policy_system_hide_admin"])) {
			if ($_POST["v_policy_system_hide_admin"] != $_SESSION["POLICY_SYSTEM_HIDE_ADMIN"]) {
				exec(
					HESTIA_CMD .
						"v-change-sys-config-value POLICY_SYSTEM_HIDE_ADMIN " .
						quoteshellarg($_POST["v_policy_system_hide_admin"]),
					$output,
					$return_var,
				);
				check_return_code($return_var, $output);
				unset($output);
				if (empty($_SESSION["error_msg"])) {
					$v_policy_system_hide_admin = $_POST["v_policy_system_hide_admin"];
				}
				$v_security_adv = "yes";
			}
		}
	}

	// Change POLICY_SYSTEM_HIDE_SERVICES
	if (empty($_SESSION["error_msg"])) {
		if (!empty($_POST["v_policy_system_hide_services"])) {
			if (
				$_POST["v_policy_system_hide_services"] != $_SESSION["POLICY_SYSTEM_HIDE_SERVICES"]
			) {
				exec(
					HESTIA_CMD .
						"v-change-sys-config-value POLICY_SYSTEM_HIDE_SERVICES " .
						quoteshellarg($_POST["v_policy_system_hide_services"]),
					$output,
					$return_var,
				);
				check_return_code($return_var, $output);
				unset($output);
				if (empty($_SESSION["error_msg"])) {
					$v_policy_system_hide_services = $_POST["v_policy_system_hide_services"];
				}
				$v_security_adv = "yes";
			}
		}
	}
	// Change POLICY_SYSTEM_HIDE_SERVICES
	if (empty($_SESSION["error_msg"])) {
		if (
			$_POST["v_policy_backup_suspended_users"] != $_SESSION["POLICY_BACKUP_SUSPENDED_USERS"]
		) {
			exec(
				HESTIA_CMD .
					"v-change-sys-config-value POLICY_BACKUP_SUSPENDED_USERS " .
					quoteshellarg($_POST["v_policy_backup_suspended_users"]),
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
			if (empty($_SESSION["error_msg"])) {
				$v_policy_system_hide_services = $_POST["v_policy_backup_suspended_users"];
			}
			$v_security_adv = "yes";
		}
	}

	if (empty($_SESSION["error_msg"])) {
		if ($_POST["v_policy_sync_error_documents"] != $_SESSION["POLICY_SYNC_ERROR_DOCUMENTS"]) {
			exec(
				HESTIA_CMD .
					"v-change-sys-config-value POLICY_SYNC_ERROR_DOCUMENTS " .
					quoteshellarg($_POST["v_policy_sync_error_documents"]),
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
			if (empty($_SESSION["error_msg"])) {
				$v_policy_sync_error_documents = $_POST["v_policy_sync_error_documents"];
			}
			$v_security_adv = "yes";
		}
	}
	if (empty($_SESSION["error_msg"])) {
		if ($_POST["v_policy_sync_skeleton"] != $_SESSION["POLICY_SYNC_SKELETON"]) {
			exec(
				HESTIA_CMD .
					"v-change-sys-config-value POLICY_SYNC_SKELETON " .
					quoteshellarg($_POST["v_policy_sync_skeleton"]),
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
			if (empty($_SESSION["error_msg"])) {
				$v_policy_sync_skeleton = $_POST["v_policy_sync_skeleton"];
			}
			$v_security_adv = "yes";
		}
	}

	// Change login style
	if (empty($_SESSION["error_msg"])) {
		if ($_POST["v_login_style"] != $_SESSION["LOGIN_STYLE"]) {
			exec(
				HESTIA_CMD .
					"v-change-sys-config-value LOGIN_STYLE " .
					quoteshellarg($_POST["v_login_style"]),
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
			if (empty($_SESSION["error_msg"])) {
				$v_login_style = $_POST["v_login_style"];
			}
			$v_security_adv = "yes";
		}
	}

	// Update SSL certificate
	if (!empty($_POST["v_ssl_crt"]) && empty($_SESSION["error_msg"])) {
		if (
			$v_ssl_crt != str_replace("\r\n", "\n", $_POST["v_ssl_crt"]) ||
			$v_ssl_key != str_replace("\r\n", "\n", $_POST["v_ssl_key"])
		) {
			exec("mktemp -d", $mktemp_output, $return_var);
			$tmpdir = $mktemp_output[0];

			// Certificate
			if (!empty($_POST["v_ssl_crt"])) {
				$fp = fopen($tmpdir . "/certificate.crt", "w");
				fwrite($fp, str_replace("\r\n", "\n", $_POST["v_ssl_crt"]));
				fwrite($fp, "\n");
				fclose($fp);
			}

			// Key
			if (!empty($_POST["v_ssl_key"])) {
				$fp = fopen($tmpdir . "/certificate.key", "w");
				fwrite($fp, str_replace("\r\n", "\n", $_POST["v_ssl_key"]));
				fwrite($fp, "\n");
				fclose($fp);
			}

			exec(HESTIA_CMD . "v-change-sys-hestia-ssl " . $tmpdir, $output, $return_var);
			check_return_code($return_var, $output);
			unset($output);

			// List ssl certificate info
			exec(HESTIA_CMD . "v-list-sys-hestia-ssl json", $output, $return_var);
			$ssl_str = json_decode(implode("", $output), true);
			unset($output);
			$v_ssl_crt = $ssl_str["HESTIA"]["CRT"];
			$v_ssl_key = $ssl_str["HESTIA"]["KEY"];
			$v_ssl_ca = $ssl_str["HESTIA"]["CA"];
			$v_ssl_subject = $ssl_str["HESTIA"]["SUBJECT"];
			$v_ssl_aliases = $ssl_str["HESTIA"]["ALIASES"];
			$v_ssl_not_before = $ssl_str["HESTIA"]["NOT_BEFORE"];
			$v_ssl_not_after = $ssl_str["HESTIA"]["NOT_AFTER"];
			$v_ssl_signature = $ssl_str["HESTIA"]["SIGNATURE"];
			$v_ssl_pub_key = $ssl_str["HESTIA"]["PUB_KEY"];
			$v_ssl_issuer = $ssl_str["HESTIA"]["ISSUER"];

			// Cleanup certificate tempfiles
			if (file_exists($tmpdir . "/certificate.crt")) {
				unlink($tmpdir . "/certificate.crt");
			}
			if (file_exists($tmpdir . "/certificate.key")) {
				unlink($tmpdir . "/certificate.key");
			}
			rmdir($tmpdir);
		}
	}

	// Flush field values on success
	if (empty($_SESSION["error_msg"])) {
		$_SESSION["ok_msg"] = _("Changes have been saved.");
	}
	if ($require_refresh == true) {
		$refresh = $_SERVER["REQUEST_URI"];
		$_SESSION["ok_msg"] = _("Changes have been saved.");
		header("Location: $refresh");
		die();
	}
}

// Check system configuration
exec(HESTIA_CMD . "v-list-sys-config json", $output, $return_var);
$data = json_decode(implode("", $output), true);
unset($output);

$sys_arr = $data["config"];
foreach ($sys_arr as $key => $value) {
	$_SESSION[$key] = $value;
}

// Render page
render_page($user, $TAB, "edit_server");

// Flush session messages
unset($_SESSION["error_msg"]);
unset($_SESSION["ok_msg"]);
