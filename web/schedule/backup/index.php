<?php

ob_start();
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check token
verify_csrf($_GET);

exec(HESTIA_CMD . "v-schedule-user-backup " . $user, $output, $return_var);
if ($return_var == 0) {
	$_SESSION["error_msg"] = _(
		"Task has been added to the queue. You will receive an email notification when your backup is ready for download.",
	);
} else {
	$_SESSION["error_msg"] = implode("<br>", $output);
	if (empty($_SESSION["error_msg"])) {
		$_SESSION["error_msg"] = _("Error: Hestia did not return any output.");
	}

	if ($return_var == 4) {
		$_SESSION["error_msg"] = _(
			"An existing backup task is already running, please wait for it to complete.",
		);
	}
}
unset($output);
header("Location: /list/backup/");
exit();
