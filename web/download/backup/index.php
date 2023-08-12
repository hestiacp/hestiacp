<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check token
verify_csrf($_GET);

$backup = $_GET["backup"];

if (!file_exists("/backup/" . $backup)) {
	$backup = quoteshellarg($_GET["backup"]);
	exec(
		HESTIA_CMD . "v-schedule-user-backup-download " . $user . " " . $backup,
		$output,
		$return_var,
	);
	if ($return_var == 0) {
		$_SESSION["error_msg"] = _("Download of remote backup file has been scheduled.");
	} else {
		$_SESSION["error_msg"] = implode("<br>", $output);
		if (empty($_SESSION["error_msg"])) {
			$_SESSION["error_msg"] = _("Error: Hestia did not return any output.");
		}
	}
	unset($output);
	header("Location: /list/backup/");
	exit();
} else {
	if ($_SESSION["userContext"] === "admin") {
		header("Content-type: application/gzip");
		header("Content-Disposition: attachment; filename=\"" . $backup . "\";");
		header("X-Accel-Redirect: /backup/" . $backup);
	}

	if (!empty($_SESSION["user"]) && $_SESSION["userContext"] != "admin") {
		if (strpos($backup, $_SESSION["user"] . ".") === 0) {
			header("Content-type: application/gzip");
			header("Content-Disposition: attachment; filename=\"" . $backup . "\";");
			header("X-Accel-Redirect: /backup/" . $backup);
		}
	}
}
