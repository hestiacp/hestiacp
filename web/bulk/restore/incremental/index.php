<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check token
verify_csrf($_POST);

$action = $_POST["action"];
$snapshot = quoteshellarg($_POST["snapshot"]);

$web = [];
$dns = [];
$mail = [];
$db = [];
$cron = [];
$udir = [];

if (!empty($_POST["web"])) {
	$web = quoteshellarg(implode(",", $_POST["web"]));
}
if (!empty($_POST["dns"])) {
	$dns = quoteshellarg(implode(",", $_POST["dns"]));
}
if (!empty($_POST["mail"])) {
	$mail = quoteshellarg(implode(",", $_POST["mail"]));
}
if (!empty($_POST["db"])) {
	$db = quoteshellarg(implode(",", $_POST["db"]));
}
if (!empty($_POST["cron"])) {
	$cron = "yes";
}
if (!empty($_POST["file"])) {
	$udir = quoteshellarg(implode(",", $_POST["file"]));
}

if ($action == "restore") {
	if (!empty($web)) {
		exec(
			HESTIA_CMD .
				"v-schedule-user-restore-restic " .
				$user .
				" " .
				$snapshot .
				" " .
				"web" .
				" " .
				$web,
			$output,
			$return_var,
		);
	}
	if (!empty($dns)) {
		exec(
			HESTIA_CMD .
				"v-schedule-user-restore-restic " .
				$user .
				" " .
				$snapshot .
				" " .
				"dns" .
				" " .
				$dns,
			$output,
			$return_var,
		);
	}
	if (!empty($mail)) {
		exec(
			HESTIA_CMD .
				"v-schedule-user-restore-restic " .
				$user .
				" " .
				$snapshot .
				" " .
				"db" .
				" " .
				$db,
			$output,
			$return_var,
		);
		if (!empty($dns)) {
			exec(
				HESTIA_CMD .
					"v-schedule-user-restore-restic " .
					$user .
					" " .
					$snapshot .
					" " .
					"dns" .
					" " .
					$dns,
				$output,
				$return_var,
			);
		}
	}
	if (!empty($cron)) {
		exec(
			HESTIA_CMD . "v-schedule-user-restore-restic " . $user . " " . $snapshot . " " . "cron",
			$output,
			$return_var,
		);
	}

	if (!empty($file)) {
		exec(
			HESTIA_CMD .
				"v-schedule-user-restore-restic " .
				$user .
				" " .
				$snapshot .
				" " .
				"file" .
				$file,
			$output,
			$return_var,
		);
	}
}
if ($return_var == 0) {
	$_SESSION["error_msg"] = _(
		"Task has been added to the queue. You will receive an email notification when your restore has been completed.",
	);
} else {
	$_SESSION["error_msg"] = implode("<br>", $output);
}
var_dump($_POST);
var_dump($output);
header("Location: /list/backup/incremental/?snapshot=" . $_POST["snapshot"]);
