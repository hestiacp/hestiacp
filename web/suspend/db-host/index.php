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
	exec(
		HESTIA_CMD .
			"v-suspend-database-host " .
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

header("Location: /list/db-host/");
exit();
