<?php

$TAB = "SERVER";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check user
if ($_SESSION["userContext"] != "admin") {
	header("Location: /list/user");
	exit();
}

// Check POST request
if (!empty($_POST["save"])) {
	// Check token
	verify_csrf($_POST);

	// Set restart flag
	$v_restart = "yes";
	if (empty($_POST["v_restart"])) {
		$v_restart = "no";
	}

	// Update options
	if (!empty($_POST["v_options"])) {
		exec("mktemp", $mktemp_output, $return_var);
		$new_conf = $mktemp_output[0];
		$fp = fopen($new_conf, "w");
		fwrite($fp, str_replace("\r\n", "\n", $_POST["v_options"]));
		fclose($fp);
		exec(
			DevIT_CMD . "v-change-sys-service-config " . $new_conf . " bind9-opt " . $v_restart,
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
		unlink($new_conf);
	}

	// Update config
	if (empty($_SESSION["error_msg"]) && !empty($_POST["v_config"])) {
		exec("mktemp", $mktemp_output, $return_var);
		$new_conf = $mktemp_output[0];
		$fp = fopen($new_conf, "w");
		fwrite($fp, str_replace("\r\n", "\n", $_POST["v_config"]));
		fclose($fp);
		exec(
			DevIT_CMD . "v-change-sys-service-config " . $new_conf . " bind9 " . $v_restart,
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
		unlink($new_conf);
	}

	// Set success message
	if (empty($_SESSION["error_msg"])) {
		$_SESSION["ok_msg"] = _("Changes have been saved.");
	}
}

$v_options_path = "/etc/bind/named.conf.options";
$v_config_path = "/etc/bind/named.conf";
$v_service_name = strtoupper("bind9");

// Read config
$v_options = shell_exec(DevIT_CMD . "v-open-fs-config " . $v_options_path);
$v_config = shell_exec(DevIT_CMD . "v-open-fs-config " . $v_config_path);

// Render page
render_page($user, $TAB, "edit_server_bind9");

// Flush session messages
unset($_SESSION["error_msg"]);
unset($_SESSION["ok_msg"]);
