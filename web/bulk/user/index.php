<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check token
verify_csrf($_POST);

if (empty($_POST["user"])) {
	header("Location: /list/user");
	exit();
}
if (empty($_POST["action"])) {
	header("Location: /list/user");
	exit();
}
$user = $_POST["user"];
$action = $_POST["action"];

if ($_SESSION["userContext"] === "admin") {
	switch ($action) {
		case "delete":
			$cmd = "v-delete-user";
			$restart = "no";
			break;
		case "suspend":
			$cmd = "v-suspend-user";
			$restart = "no";
			break;
		case "unsuspend":
			$cmd = "v-unsuspend-user";
			$restart = "no";
			break;
		case "update counters":
			$cmd = "v-update-user-counters";
			break;
		case "rebuild":
			$cmd = "v-rebuild-all";
			$restart = "no";
			break;
		case "rebuild user":
			$cmd = "v-rebuild-user";
			$restart = "no";
			break;
		case "rebuild web":
			$cmd = "v-rebuild-web-domains";
			$restart = "no";
			break;
		case "rebuild dns":
			$cmd = "v-rebuild-dns-domains";
			$restart = "no";
			break;
		case "rebuild mail":
			$cmd = "v-rebuild-mail-domains";
			break;
		case "rebuild db":
			$cmd = "v-rebuild-databases";
			break;
		case "rebuild cron":
			$cmd = "v-rebuild-cron-jobs";
			break;
		default:
			header("Location: /list/user/");
			exit();
	}
} else {
	switch ($action) {
		case "update counters":
			$cmd = "v-update-user-counters";
			break;
		default:
			header("Location: /list/user/");
			exit();
	}
}

foreach ($user as $value) {
	$value = quoteshellarg($value);
	exec(HESTIA_CMD . $cmd . " " . $value . " " . $restart, $output, $return_var);
	$changes = "yes";
}

header("Location: /list/user/");
