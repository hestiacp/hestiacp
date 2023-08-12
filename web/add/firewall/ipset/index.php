<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
$TAB = "FIREWALL";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check user
if ($_SESSION["userContext"] != "admin") {
	header("Location: /list/user");
	exit();
}

// Check POST request
if (!empty($_POST["ok"])) {
	// Check token
	verify_csrf($_POST);

	// Check empty fields
	if (empty($_POST["v_ipname"])) {
		$errors[] = _("Name");
	}
	if (empty($_POST["v_datasource"])) {
		$errors[] = _("Data Source");
	}
	if (empty($_POST["v_ipver"])) {
		$errors[] = _("IP Version");
	}
	if (empty($_POST["v_autoupdate"])) {
		$errors[] = _("Auto Update");
	}

	if (!empty($errors[0])) {
		foreach ($errors as $i => $error) {
			if ($i == 0) {
				$error_msg = $error;
			} else {
				$error_msg = $error_msg . ", " . $error;
			}
		}
		$_SESSION["error_msg"] = sprintf(_('Field "%s" can not be blank.'), $error_msg);
	}

	$v_ipname = $_POST["v_ipname"];
	$v_datasource = $_POST["v_datasource"];
	$v_ipver = $_POST["v_ipver"];
	$v_autoupdate = $_POST["v_autoupdate"];

	// Add firewall ipset list
	if (empty($_SESSION["error_msg"])) {
		exec(
			HESTIA_CMD .
				"v-add-firewall-ipset " .
				quoteshellarg($v_ipname) .
				" " .
				quoteshellarg($v_datasource) .
				" " .
				quoteshellarg($v_ipver) .
				" " .
				quoteshellarg($v_autoupdate),
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
	}

	// Flush field values on success
	if (empty($_SESSION["error_msg"])) {
		$_SESSION["ok_msg"] = _("IP list has been created successfully.");
	}
}
if (empty($v_ipname)) {
	$v_ipname = "";
}
if (empty($v_datasource)) {
	$v_datasource = "";
}
if (empty($v_ipver)) {
	$v_ipver = "";
}

// Render
render_page($user, $TAB, "add_firewall_ipset");

// Flush session messages
unset($_SESSION["error_msg"]);
unset($_SESSION["ok_msg"]);
