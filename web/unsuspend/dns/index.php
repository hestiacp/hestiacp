<?php
use function DevITcp\quoteshellarg\quoteshellarg;

// Init
ob_start();
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check token
verify_csrf($_GET);

// DNS domain
if (!empty($_GET["domain"]) && empty($_GET["record_id"])) {
	$v_domain = quoteshellarg($_GET["domain"]);
	exec(DevIT_CMD . "v-unsuspend-dns-domain " . $user . " " . $v_domain, $output, $return_var);
	if ($return_var != 0) {
		$error = implode("<br>", $output);
		if (empty($error)) {
			$error = _("Error: DevIT did not return any output.");
		}
		$_SESSION["error_msg"] = $error;
	}
	unset($output);
	$back = getenv("HTTP_REFERER");
	if (!empty($back)) {
		header("Location: " . $back);
		exit();
	}
	header("Location: /list/dns/");
	exit();
}

// DNS record
if (!empty($_GET["domain"]) && !empty($_GET["record_id"])) {
	$v_domain = quoteshellarg($_GET["domain"]);
	$v_record_id = quoteshellarg($_GET["record_id"]);
	exec(
		DevIT_CMD . "v-unsuspend-dns-record " . $user . " " . $v_domain . " " . $v_record_id,
		$output,
		$return_var,
	);
	if ($return_var != 0) {
		$error = implode("<br>", $output);
		if (empty($error)) {
			$error = _("Error: DevIT did not return any output.");
		}
		$_SESSION["error_msg"] = $error;
	}
	unset($output);
	$back = getenv("HTTP_REFERER");
	if (!empty($back)) {
		header("Location: " . $back);
		exit();
	}
	header("Location: /list/dns/?domain=" . $_GET["domain"]);
	exit();
}

$back = getenv("HTTP_REFERER");
if (!empty($back)) {
	header("Location: " . $back);
	exit();
}

header("Location: /list/dns/");
exit();
