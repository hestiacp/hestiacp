<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check token
verify_csrf($_GET);

$snapshot = quoteshellarg($_GET["snapshot"]);

if (empty($_GET["object"])) {
	$_GET["object"] = "";
}

if (empty($_GET["type"])) {
	exec(
		HESTIA_CMD . "v-schedule-user-restore-restic " . $user . " " . $snapshot,
		$output,
		$return_var,
	);
} else {
	exec(
		HESTIA_CMD .
			"v-schedule-user-restore-restic " .
			$user .
			" " .
			$snapshot .
			" " .
			quoteshellarg($_GET["type"]) .
			" " .
			quoteshellarg($_GET["object"]),
		$output,
		$return_var,
	);
}

header("Location: /list/backup/incremental/?snapshot=" . $_GET["snapshot"]);
