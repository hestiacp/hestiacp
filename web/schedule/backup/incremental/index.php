<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check token
verify_csrf($_GET);

if (empty($_GET["object"])) {
	$_GET["object"] = "";
}

exec(HESTIA_CMD . "v-schedule-user-backup-restic " . $user, $output, $return_var);

if ($return_var == 0) {
	$_SESSION["error_msg"] = _("Snapshot has been sheduled");
} else {
	$_SESSION["error_msg"] = implode("\n", $output);
}
header("Location: /list/backup/incremental/");
