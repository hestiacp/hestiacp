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

if (empty($_POST["ipchain"])) {
	header("Location: /list/firewall/banlist/");
	exit();
}
if (empty($_POST["action"])) {
	header("Location: /list/firewall/banlist/");
	exit();
}

$ipchain = $_POST["ipchain"];
$action = $_POST["action"];

switch ($action) {
	case "delete":
		$cmd = "v-delete-firewall-ban";
		break;
	default:
		header("Location: /list/firewall/banlist/");
		exit();
}

foreach ($ipchain as $value) {
	[$ip, $chain] = explode(":", $value);
	$v_ip = quoteshellarg($ip);
	$v_chain = quoteshellarg($chain);
	exec(HESTIA_CMD . $cmd . " " . $v_ip . " " . $v_chain, $output, $return_var);
}

header("Location: /list/firewall/banlist");
