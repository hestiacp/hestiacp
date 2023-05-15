<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
$TAB = "CRON";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check POST request
if (!empty($_POST["ok"])) {
	// Check token
	verify_csrf($_POST);

	// Check empty fields
	if (!isset($_POST["v_min"]) || $_POST["v_min"] == "") {
		$errors[] = _("Minute");
	}
	if (!isset($_POST["v_hour"]) || $_POST["v_hour"] == "") {
		$errors[] = _("Hour");
	}
	if (!isset($_POST["v_day"]) || $_POST["v_day"] == "") {
		$errors[] = _("Day");
	}
	if (!isset($_POST["v_month"]) || $_POST["v_month"] == "") {
		$errors[] = _("Month");
	}
	if (!isset($_POST["v_wday"]) || $_POST["v_wday"] == "") {
		$errors[] = _("Day of Week");
	}
	if (!isset($_POST["v_cmd"]) || $_POST["v_cmd"] == "") {
		$errors[] = _("Command");
	}
	if (!empty($errors[0])) {
		foreach ($errors as $i => $error) {
			if ($i == 0) {
				$error_msg = $error;
			} else {
				$error_msg = $error_msg . ", " . $error;
			}
		}
		$_SESSION["error_msg"] = sprintf(_('Field "%s" can not be blank.'), $error_msg);
	}

	// Protect input
	$v_min = quoteshellarg($_POST["v_min"]);
	$v_hour = quoteshellarg($_POST["v_hour"]);
	$v_day = quoteshellarg($_POST["v_day"]);
	$v_month = quoteshellarg($_POST["v_month"]);
	$v_wday = quoteshellarg($_POST["v_wday"]);
	$v_cmd = quoteshellarg($_POST["v_cmd"]);

	// Add cron job
	if (empty($_SESSION["error_msg"])) {
		exec(
			HESTIA_CMD .
				"v-add-cron-job " .
				$user .
				" " .
				$v_min .
				" " .
				$v_hour .
				" " .
				$v_day .
				" " .
				$v_month .
				" " .
				$v_wday .
				" " .
				$v_cmd,
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
	}

	// Flush field values on success
	if (empty($_SESSION["error_msg"])) {
		$_SESSION["ok_msg"] = _("Cron job has been created successfully.");
		unset($v_min);
		unset($v_hour);
		unset($v_day);
		unset($v_month);
		unset($v_wday);
		unset($v_cmd);
		unset($output);
	}
}

if (empty($v_cmd)) {
	$v_cmd = "";
}
if (empty($v_month)) {
	$v_month = "";
}
if (empty($v_day)) {
	$v_day = "";
}
if (empty($v_wday)) {
	$v_wday = "";
}
if (empty($v_hour)) {
	$v_hour = "";
}
if (empty($v_min)) {
	$v_min = "";
}
// Render
render_page($user, $TAB, "add_cron");

// Flush session messages
unset($_SESSION["error_msg"]);
unset($_SESSION["ok_msg"]);
