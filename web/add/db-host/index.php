<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

$TAB = "SERVER";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check user
if ($_SESSION["userContext"] !== "admin") {
	header("Location: /list/user");
	exit();
}

$db_types = array_values(
	array_unique(
		array_filter(
			array_merge(explode(",", $_SESSION["DB_SYSTEM"] ?? ""), ["mysql", "pgsql", "redis"]),
		),
	),
);

function get_db_host_default_port(string $type): string {
	if ($type === "pgsql") {
		return "5432";
	}
	if ($type === "redis") {
		return "6379";
	}
	return "3306";
}

function get_db_host_default_charsets(string $type): string {
	if ($type === "redis") {
		return "";
	}
	return $type === "pgsql" ? "UTF8" : "UTF8,LATIN1,WIN1250,WIN1251,WIN1252,WIN1256,WIN1258,KOI8";
}

if (!empty($_POST["ok"])) {
	verify_csrf($_POST);

	$v_type = $_POST["v_type"] ?? "mysql";
	$v_host = trim($_POST["v_host"] ?? "");
	$v_port = trim($_POST["v_port"] ?? "");
	$v_dbuser = trim($_POST["v_dbuser"] ?? "");
	$v_password = $_POST["v_password"] ?? "";
	$v_max_db = trim($_POST["v_max_db"] ?? "500");
	$v_charsets = trim($_POST["v_charsets"] ?? "");
	$v_template = trim($_POST["v_template"] ?? "template1");

	if ($v_port === "") {
		$v_port = get_db_host_default_port($v_type);
	}
	if ($v_charsets === "") {
		$v_charsets = get_db_host_default_charsets($v_type);
	}
	if ($v_template === "") {
		$v_template = "template1";
	}
	$_POST["v_port"] = $v_port;

	if (empty($v_type)) {
		$errors[] = _("Type");
	}
	if (empty($v_host)) {
		$errors[] = _("Host");
	}
	if (empty($v_port)) {
		$errors[] = _("Port");
	}
	if (empty($v_dbuser)) {
		$errors[] = _("Username");
	}
	if (empty($v_password)) {
		$errors[] = _("Password");
	}
	if (empty($v_max_db)) {
		$errors[] = _("Maximum Number of Databases");
	}
	if (!empty($errors[0])) {
		$_SESSION["error_msg"] = sprintf(_('Field "%s" can not be blank.'), implode(", ", $errors));
	}

	if (empty($_SESSION["error_msg"])) {
		exec(
			HESTIA_CMD .
				"v-add-database-host " .
				quoteshellarg($_POST["v_type"]) .
				" " .
				quoteshellarg($_POST["v_host"]) .
				" " .
				quoteshellarg($_POST["v_dbuser"]) .
				" " .
				quoteshellarg($_POST["v_password"]) .
				" " .
				quoteshellarg($v_max_db) .
				" " .
				quoteshellarg($v_charsets) .
				" " .
				quoteshellarg($v_template) .
				" " .
				quoteshellarg($v_port),
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
	}

	if (empty($_SESSION["error_msg"])) {
		$_SESSION["ok_msg"] = _("Database server has been added successfully.");
		header("Location: /list/db-host/");
		exit();
	}
} else {
	$v_type = $db_types[0] ?? "mysql";
	$v_host = "";
	$v_port = get_db_host_default_port($v_type);
	$v_dbuser = "root";
	$v_password = "";
	$v_max_db = "500";
	$v_charsets = get_db_host_default_charsets($v_type);
	$v_template = "template1";
}

render_page($user, $TAB, "add_db_host");

unset($_SESSION["error_msg"]);
unset($_SESSION["ok_msg"]);
