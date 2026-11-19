<?php

use function Hestiacp\quoteshellarg\quoteshellarg;

$TAB = "SERVER";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check user
if ($_SESSION["userContext"] != "admin") {
	header("Location: /list/user/");
	exit();
}

// Load installed PHP versions
exec(HESTIA_CMD . "v-list-sys-php json", $output, $return_var);
$v_php_versions = json_decode(implode("", $output), true);
if (!is_array($v_php_versions)) {
	$v_php_versions = [];
}
unset($output);

// Resolve requested PHP version against installed versions
$requested_version = $_POST["v_php_version"] ?? ($_GET["version"] ?? "");
if (!is_string($requested_version)) {
	$requested_version = "";
}
$v_php_version = "";
if (
	$requested_version !== "" &&
	preg_match('/^[0-9]+\.[0-9]+$/', $requested_version) &&
	in_array($requested_version, $v_php_versions, true)
) {
	$v_php_version = $requested_version;
}

// Check POST request
if (!empty($_POST["save"])) {
	// Check token
	verify_csrf($_POST);

	// Reject unknown/invalid PHP versions rather than updating default PHP
	if ($requested_version !== "" && $v_php_version === "") {
		$_SESSION["error_msg"] = _("Invalid PHP version.");
	}

	// Set restart flag
	$v_restart = "yes";
	if (empty($_POST["v_restart"])) {
		$v_restart = "no";
	}

	// Update config
	if (empty($_SESSION["error_msg"]) && !empty($_POST["v_config"])) {
		exec("mktemp", $mktemp_output, $return_var);
		if ($return_var !== 0 || empty($mktemp_output[0])) {
			$_SESSION["error_msg"] = _("Unable to create temporary configuration file.");
		} else {
			$new_conf = $mktemp_output[0];
			$fp = fopen($new_conf, "w");
			if ($fp === false) {
				$_SESSION["error_msg"] = _("Unable to write temporary configuration file.");
			} else {
				fwrite($fp, str_replace("\r\n", "\n", $_POST["v_config"]));
				fclose($fp);

				$v_target_service = "php";
				if (
					$v_php_version !== "" &&
					(($_SESSION["WEB_BACKEND"] ?? "") === "php-fpm" ||
						is_dir("/etc/php/" . $v_php_version . "/fpm"))
				) {
					$v_target_service = "php" . $v_php_version . "-fpm";
				}

				exec(
					HESTIA_CMD .
						"v-change-sys-service-config " .
						quoteshellarg($new_conf) .
						" " .
						quoteshellarg($v_target_service) .
						" " .
						quoteshellarg($v_restart),
					$output,
					$return_var,
				);
				check_return_code($return_var, $output);
				unset($output);
			}
			if (file_exists($new_conf)) {
				unlink($new_conf);
			}
		}
	}

	// Set success message
	if (empty($_SESSION["error_msg"])) {
		$_SESSION["ok_msg"] = _("Changes have been saved.");
	}
}

// List config
$list_cmd = HESTIA_CMD . "v-list-sys-php-config json";
if ($v_php_version !== "") {
	$list_cmd .= " " . quoteshellarg($v_php_version);
}
exec($list_cmd, $output, $return_var);
if ($return_var !== 0 && empty($_SESSION["error_msg"])) {
	check_return_code($return_var, $output);
}
$data = json_decode(implode("", $output), true);
unset($output);
$config_data = is_array($data["CONFIG"] ?? null) ? $data["CONFIG"] : [];
$v_memory_limit = $config_data["memory_limit"] ?? "";
$v_max_execution_time = $config_data["max_execution_time"] ?? "";
$v_max_input_time = $config_data["max_input_time"] ?? "";
$v_upload_max_filesize = $config_data["upload_max_filesize"] ?? "";
$v_post_max_size = $config_data["post_max_size"] ?? "";
$v_display_errors = $config_data["display_errors"] ?? "";
$v_error_reporting = $config_data["error_reporting"] ?? "";
$v_config_path = $config_data["config_path"] ?? "";

if (
	$v_php_version === "" &&
	preg_match("#^/etc/php/([0-9]+\.[0-9]+)/#", $v_config_path, $matches)
) {
	$v_php_version = $matches[1];
}
$v_service_name = $v_php_version !== "" ? "PHP " . $v_php_version : "PHP";

// Read config
$v_config = "";
if ($v_config_path !== "") {
	exec(
		HESTIA_CMD . "v-open-fs-config " . quoteshellarg($v_config_path),
		$config_output,
		$return_var,
	);
	if ($return_var === 0) {
		$v_config = implode("\n", $config_output);
	} elseif (empty($_SESSION["error_msg"])) {
		check_return_code($return_var, $config_output);
	}
	unset($config_output);
}

// Render page
render_page($user, $TAB, "edit_server_php");

// Flush session messages
unset($_SESSION["error_msg"]);
unset($_SESSION["ok_msg"]);
