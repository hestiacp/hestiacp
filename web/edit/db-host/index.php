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

if (empty($_GET["type"]) || empty($_GET["host"])) {
	header("Location: /list/db-host/");
	exit();
}

$v_type = $_GET["type"];
$v_host = $_GET["host"];
$v_port = $_GET["port"] ?? "";

exec(
	HESTIA_CMD .
		"v-list-database-host " .
		quoteshellarg($v_type) .
		" " .
		quoteshellarg($v_host) .
		" json " .
		quoteshellarg($v_port),
	$output,
	$return_var,
);
check_return_code_redirect($return_var, $output, "/list/db-host/");
$data = json_decode(implode("", $output), true);
$host_data = is_array($data) ? reset($data) : [];
unset($output);

$v_endpoint =
	$host_data["ENDPOINT"] ??
	$v_host .
		":" .
		($v_port ?: ($v_type === "pgsql" ? "5432" : ($v_type === "redis" ? "6379" : "3306")));
$v_port = $host_data["PORT"] ?? $v_port;
$v_dbuser = $host_data["USER"] ?? "root";
$v_max_db = $host_data["MAX_DB"] ?? "500";
$v_charsets = $host_data["CHARSETS"] ?? "";
$v_template = $host_data["TPL"] ?? "template1";
$v_suspended = $host_data["SUSPENDED"] ?? "no";
$v_password = "";

if (!empty($_POST["save"])) {
	verify_csrf($_POST);

	if (!empty($_POST["v_password"])) {
		exec(
			HESTIA_CMD .
				"v-change-database-host-password " .
				quoteshellarg($v_type) .
				" " .
				quoteshellarg($v_host) .
				" " .
				quoteshellarg($v_dbuser) .
				" " .
				quoteshellarg($_POST["v_password"]) .
				" " .
				quoteshellarg($v_port),
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
	}

	if (empty($_SESSION["error_msg"])) {
		$_SESSION["ok_msg"] = _("Changes have been saved.");
		header("Location: /list/db-host/");
		exit();
	}
}

render_page($user, $TAB, "edit_db_host");

unset($_SESSION["error_msg"]);
unset($_SESSION["ok_msg"]);
