<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check token
verify_csrf($_GET);

$backup = quoteshellarg($_GET["backup"]);

$web = "no";
$dns = "no";
$mail = "no";
$db = "no";
$cron = "no";
$udir = "no";

if ($_GET["type"] == "web") {
	$web = quoteshellarg($_GET["object"]);
}
if ($_GET["type"] == "dns") {
	$dns = quoteshellarg($_GET["object"]);
}
if ($_GET["type"] == "mail") {
	$mail = quoteshellarg($_GET["object"]);
}
if ($_GET["type"] == "db") {
	$db = quoteshellarg($_GET["object"]);
}
if ($_GET["type"] == "cron") {
	$cron = "yes";
}
if ($_GET["type"] == "udir") {
	$udir = quoteshellarg($_GET["object"]);
}

if (!empty($_GET["type"])) {
	$restore_cmd =
		HESTIA_CMD .
		"v-schedule-user-restore " .
		$user .
		" " .
		$backup .
		" " .
		$web .
		" " .
		$dns .
		" " .
		$mail .
		" " .
		$db .
		" " .
		$cron .
		" " .
		$udir;
} else {
	$restore_cmd = HESTIA_CMD . "v-schedule-user-restore " . $user . " " . $backup;
}

exec($restore_cmd, $output, $return_var);
if ($return_var == 0) {
	$_SESSION["error_msg"] = _(
		"Task has been added to the queue. You will receive an email notification when your restore has been completed.",
	);
} else {
	$_SESSION["error_msg"] = implode("<br>", $output);
	if (empty($_SESSION["error_msg"])) {
		$_SESSION["error_msg"] = _("Error: Hestia did not return any output.");
	}
	if ($return_var == 4) {
		$_SESSION["error_msg"] = _(
			"An existing restoration task is already running. Please wait for it to finish before launching it again.",
		);
	}
}

header("Location: /list/backup/?backup=" . $_GET["backup"]);
