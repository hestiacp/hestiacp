<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check user
if ($_SESSION["userContext"] != "admin") {
	header("Location: /list/user/");
	exit();
}

// Check token
verify_csrf($_POST);

$plugin = $_POST["plugin"];
$action = $_POST["action"];

switch ($action) {
	case "delete":
		$cmd = "v-delete-plugin";
		break;

	case "disable":
		$cmd = "v-disable-plugin";
		break;

	case "enable":
		$cmd = "v-enable-plugin";
		break;

	default:
		header("Location: /list/plugin/");
		exit();
}

foreach ($plugin as $value) {
	$v_plugin = quoteshellarg(trim($value));
	exec(HESTIA_CMD . $cmd . " " . $v_plugin, $output, $return_var);
}

header("Location: /list/plugin/");
