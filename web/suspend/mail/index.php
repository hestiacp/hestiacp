<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check token
verify_csrf($_GET);

// Mail domain
if (!empty($_GET["domain"]) && empty($_GET["account"])) {
	$v_domain = quoteshellarg($_GET["domain"]);
	exec(HESTIA_CMD . "v-suspend-mail-domain " . $user . " " . $v_domain, $output, $return_var);
	check_return_code($return_var, $output);
	unset($output);
	$back = getenv("HTTP_REFERER");
	if (!empty($back)) {
		header("Location: " . $back);
		exit();
	}
	header("Location: /list/mail/");
	exit();
}

// Mail account
if (!empty($_GET["domain"]) && !empty($_GET["account"])) {
	$v_username = quoteshellarg($user);
	$v_domain = quoteshellarg($_GET["domain"]);
	$v_account = quoteshellarg($_GET["account"]);
	exec(
		HESTIA_CMD . "v-suspend-mail-account " . $user . " " . $v_domain . " " . $v_account,
		$output,
		$return_var,
	);
	check_return_code($return_var, $output);
	unset($output);
	$back = $_SESSION["back"];
	if (!empty($back)) {
		header("Location: " . $back);
		exit();
	}
	header("Location: /list/mail/?domain=" . $_GET["domain"]);
	exit();
}

$back = $_SESSION["back"];
if (!empty($back)) {
	header("Location: " . $back);
	exit();
}

header("Location: /list/mail/");
exit();
