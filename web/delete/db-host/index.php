<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

if ($_SESSION["userContext"] !== "admin") {
	header("Location: /list/user");
	exit();
}

verify_csrf($_GET);

if (!empty($_GET["type"]) && !empty($_GET["host"])) {
	$_GET["port"] = $_GET["port"] ?? "";
	$host_args =
		quoteshellarg($_GET["type"]) .
		" " .
		quoteshellarg($_GET["host"]) .
		" json " .
		quoteshellarg($_GET["port"]);
	exec(HESTIA_CMD . "v-list-database-host " . $host_args, $output, $return_var);
	check_return_code($return_var, $output);
	$host_data = json_decode(implode("", $output), true);
	$host_data = is_array($host_data) ? reset($host_data) : [];
	unset($output);

	if (empty($_SESSION["error_msg"]) && (int) ($host_data["U_DB_BASES"] ?? 0) > 0) {
		$_SESSION["error_msg"] = _(
			"Database server can not be deleted while databases exist on it.",
		);
	}

	if (empty($_SESSION["error_msg"])) {
		exec(
			HESTIA_CMD .
				"v-delete-database-host " .
				quoteshellarg($_GET["type"]) .
				" " .
				quoteshellarg($_GET["host"]) .
				" " .
				quoteshellarg($_GET["port"]),
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
	}
}

header("Location: /list/db-host/");
exit();
