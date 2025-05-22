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

// Here we detect the version
$type = (isset($_POST['ipver']) && $_POST['ipver'] == 'ipv6') ? 'ipv6' : 'ipv4';

$rule = $_POST["rule"];
$action = $_POST["action"];

// We choose the command based on IPv4 or IPv6
switch ($action) {
	case "delete":
		$cmd = ($type === 'ipv6') ? "v-delete-firewall-ipv6-rule" : "v-delete-firewall-rule";
		break;
	case "suspend":
		$cmd = ($type === 'ipv6') ? "v-suspend-firewall-rule-ipv6" : "v-suspend-firewall-rule";
		break;
	case "unsuspend":
		$cmd = ($type === 'ipv6') ? "v-unsuspend-firewall-rule-ipv6" : "v-unsuspend-firewall-rule";
		break;
	default:
		header("Location: /list/firewall/");
		exit();
}

foreach ($rule as $value) {
	$value = quoteshellarg($value);
	exec(HESTIA_CMD . $cmd . " " . $value, $output, $return_var);
}

header("Location: /list/firewall/?ipver=" . $type);
exit();
