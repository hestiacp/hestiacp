<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check token
verify_csrf($_POST);

// Check user
if ($_SESSION["userContext"] != "admin") {
	header("Location: /list/user");
	exit();
}

if (empty($_POST["rule"])) {
	header("Location: /list/firewall/");
	exit();
}
if (empty($_POST["action"])) {
	header("Location: /list/firewall/");
	exit();
}

$rule = $_POST["rule"];
$action = $_POST["action"];

switch ($action) {
	case "delete":
		$cmd = "v-delete-firewall-rule";
		break;
	case "suspend":
		$cmd = "v-suspend-firewall-rule";
		break;
	case "unsuspend":
		$cmd = "v-unsuspend-firewall-rule";
		break;
	default:
		header("Location: /list/firewall/");
		exit();
}

foreach ($rule as $value) {
	$value = quoteshellarg($value);
	exec(HESTIA_CMD . $cmd . " " . $value, $output, $return_var);
	$restart = "yes";
}

header("Location: /list/firewall/");
