<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
$TAB = "BACKUP";

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Edit as someone else?
if ($_SESSION["userContext"] === "admin" && !empty($_GET["user"])) {
	$user = quoteshellarg($_GET["user"]);
}

// List backup exclustions
exec(HESTIA_CMD . "v-list-user-backup-exclusions " . $user . " 'json'", $output, $return_var);
check_return_code($return_var, $output);
$data = json_decode(implode("", $output), true);
unset($output);

$v_web = $v_mail = $v_db = $v_userdir = "";
// Parse web
$v_username = $user;
foreach ($data["WEB"] as $key => $value) {
	if (!empty($value)) {
		$v_web .= $key . ":" . str_replace(",", ":", $value) . "\n";
	} else {
		$v_web .= $key . "\n";
	}
}

// Parse mail
foreach ($data["MAIL"] as $key => $value) {
	if (!empty($value)) {
		$v_mail .= $key . ":" . $value . "\n";
	} else {
		$v_mail .= $key . "\n";
	}
}

// Parse databases
foreach ($data["DB"] as $key => $value) {
	if (!empty($value)) {
		$v_db .= $key . ":" . $value . "\n";
	} else {
		$v_db .= $key . "\n";
	}
}

// Parse user directories
foreach ($data["USER"] as $key => $value) {
	if (!empty($value)) {
		$v_userdir .= $key . ":" . $value . "\n";
	} else {
		$v_userdir .= $key . "\n";
	}
}

// Check POST request
if (!empty($_POST["save"])) {
	// Check token
	verify_csrf($_POST);

	$v_web = $_POST["v_web"] ?? "";
	$v_web_tmp = str_replace("\r\n", ",", $_POST["v_web"]);
	$v_web_tmp = rtrim($v_web_tmp, ",");
	$v_web_tmp = "WEB=" . quoteshellarg($v_web_tmp);

	$v_dns = $_POST["v_dns"] ?? "";
	$v_dns_tmp = str_replace("\r\n", ",", $_POST["v_dns"]);
	$v_dns_tmp = rtrim($v_dns_tmp, ",");
	$v_dns_tmp = "DNS=" . quoteshellarg($v_dns_tmp);

	$v_mail = $_POST["v_mail"] ?? "";
	$v_mail_tmp = str_replace("\r\n", ",", $_POST["v_mail"]);
	$v_mail_tmp = rtrim($v_mail_tmp, ",");
	$v_mail_tmp = "MAIL=" . quoteshellarg($v_mail_tmp);

	$v_db = $_POST["v_db"] ?? "";
	$v_db_tmp = str_replace("\r\n", ",", $_POST["v_db"]);
	$v_db_tmp = rtrim($v_db_tmp, ",");
	$v_db_tmp = "DB=" . quoteshellarg($v_db_tmp);

	$v_cron = $_POST["v_cron"] ?? "";
	$v_cron_tmp = str_replace("\r\n", ",", $_POST["v_cron"]);
	$v_cron_tmp = rtrim($v_cron_tmp, ",");
	$v_cron_tmp = "CRON=" . quoteshellarg($v_cron_tmp);

	$v_userdir = $_POST["v_userdir"] ?? "";
	$v_userdir_tmp = str_replace("\r\n", ",", $_POST["v_userdir"]);
	$v_userdir_tmp = rtrim($v_userdir_tmp, ",");
	$v_userdir_tmp = "USER=" . quoteshellarg($v_userdir_tmp);

	// Create temporary exeption list on a filesystem
	exec("mktemp", $mktemp_output, $return_var);
	$tmp = $mktemp_output[0];
	$fp = fopen($tmp, "w");
	fwrite(
		$fp,
		$v_web_tmp .
			"\n" .
			$v_dns_tmp .
			"\n" .
			$v_mail_tmp .
			"\n" .
			$v_db_tmp .
			"\n" .
			$v_userdir_tmp .
			"\n",
	);
	fclose($fp);
	unset($mktemp_output);

	// Save changes
	exec(
		HESTIA_CMD . "v-update-user-backup-exclusions " . $user . " " . $tmp,
		$output,
		$return_var,
	);
	check_return_code($return_var, $output);
	unset($output);

	// Set success message
	if (empty($_SESSION["error_msg"])) {
		$_SESSION["ok_msg"] = _("Changes have been saved.");
	}
}

// Render page
render_page($user, $TAB, "edit_backup_exclusions");

// Flush session messages
unset($_SESSION["error_msg"]);
unset($_SESSION["ok_msg"]);
