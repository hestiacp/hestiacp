<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check token
verify_csrf($_POST);

if ($read_only === true) {
	header("Location: /list/backup/incremental/");
	exit();
}

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

// Initialized up front so we never hit "undefined variable" if no
// restore item below ends up matching.
$output = [];
$return_var = 0;
$errors = [];
$scheduled = false;

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
		$scheduled = true;
		if ($return_var != 0) {
			$errors = array_merge($errors, $output);
		}
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
		$scheduled = true;
		if ($return_var != 0) {
			$errors = array_merge($errors, $output);
		}
	}
	if (!empty($db)) {
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
		$scheduled = true;
		if ($return_var != 0) {
			$errors = array_merge($errors, $output);
		}
	}
	if (!empty($mail)) {
		exec(
			HESTIA_CMD .
				"v-schedule-user-restore-restic " .
				$user .
				" " .
				$snapshot .
				" " .
				"mail" .
				" " .
				$mail,
			$output,
			$return_var,
		);
		$scheduled = true;
		if ($return_var != 0) {
			$errors = array_merge($errors, $output);
		}
	}
	if (!empty($cron)) {
		exec(
			HESTIA_CMD . "v-schedule-user-restore-restic " . $user . " " . $snapshot . " " . "cron",
			$output,
			$return_var,
		);
		$scheduled = true;
		if ($return_var != 0) {
			$errors = array_merge($errors, $output);
		}
	}

	if (!empty($udir)) {
		exec(
			HESTIA_CMD .
				"v-schedule-user-restore-restic " .
				$user .
				" " .
				$snapshot .
				" " .
				"file" .
				" " .
				$udir,
			$output,
			$return_var,
		);
		$scheduled = true;
		if ($return_var != 0) {
			$errors = array_merge($errors, $output);
		}
	}
}

if (!$scheduled) {
	$_SESSION["error_msg"] = _("No items were selected to restore.");
} elseif (empty($errors)) {
	$_SESSION["error_msg"] = _(
		"Task has been added to the queue. You will receive an email notification when your restore has been completed.",
	);
} else {
	$_SESSION["error_msg"] = implode("<br>", $errors);
}
header("Location: /list/backup/incremental/?snapshot=" . $_POST["snapshot"]);
