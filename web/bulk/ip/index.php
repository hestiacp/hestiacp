<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check token
verify_csrf($_POST);

if (empty($_POST["ip"])) {
	header("Location: /list/ip");
	exit();
}
if (empty($_POST["action"])) {
	header("Location: /list/ip");
	exit();
}

$ip = $_POST["ip"];
$action = $_POST["action"];

if ($_SESSION["userContext"] === "admin") {
	switch ($action) {
		case "reread IP":
			exec(HESTIA_CMD . "v-update-sys-ip", $output, $return_var);
			header("Location: /list/ip/");
			exit();
			break;
		case "delete":
			$cmd = "v-delete-sys-ip";
			break;
		default:
			header("Location: /list/ip/");
			exit();
	}
} else {
	header("Location: /list/ip/");
	exit();
}

foreach ($ip as $value) {
	$value = quoteshellarg($value);
	exec(HESTIA_CMD . $cmd . " " . $value, $output, $return_var);
}

header("Location: /list/ip/");
