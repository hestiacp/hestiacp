<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check token
verify_csrf($_POST);

if (empty($_POST["package"])) {
	header("Location: /list/package");
	exit();
}
if (empty($_POST["action"])) {
	header("Location: /list/package");
	exit();
}

$package = $_POST["package"];
$action = $_POST["action"];

if ($_SESSION["userContext"] === "admin") {
	switch ($action) {
		case "delete":
			$cmd = "v-delete-user-package";
			break;
		default:
			header("Location: /list/package/");
			exit();
	}
} else {
	header("Location: /list/package/");
	exit();
}

foreach ($package as $value) {
	$value = quoteshellarg($value);
	exec(HESTIA_CMD . $cmd . " " . $value, $output, $return_var);
	$restart = "yes";
}

header("Location: /list/package/");
