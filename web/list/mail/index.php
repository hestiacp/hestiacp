<?php
use function Hestiacp\quoteshellarg\quoteshellarg;
$TAB = "MAIL";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Data & Render page
if (empty($_GET["domain"])) {
	exec(HESTIA_CMD . "v-list-mail-domains $user json", $output, $return_var);
	$data = json_decode(implode("", $output), true);
	if ($_SESSION["userSortOrder"] == "name") {
		ksort($data);
	} else {
		$data = array_reverse($data, true);
	}
	unset($output);

	render_page($user, $TAB, "list_mail");
} elseif (!empty($_GET["dns"])) {
	exec(
		HESTIA_CMD . "v-list-mail-domain " . $user . " " . quoteshellarg($_GET["domain"]) . " json",
		$output,
		$return_var,
	);
	$data = json_decode(implode("", $output), true);
	$data = array_reverse($data, true);
	unset($output);
	exec(HESTIA_CMD . "v-list-user-ips " . $user . " json", $output, $return_var);
	$ips = json_decode(implode("", $output), true);
	$ips = array_reverse($ips, true);
	unset($output);
	exec(
		HESTIA_CMD .
			"v-list-mail-domain-dkim-dns " .
			$user .
			" " .
			quoteshellarg($_GET["domain"]) .
			" json",
		$output,
		$return_var,
	);
	$dkim = json_decode(implode("", $output), true);
	$dkim = array_reverse($dkim, true);
	unset($output);

	render_page($user, $TAB, "list_mail_dns");
} else {
	exec(
		HESTIA_CMD .
			"v-list-mail-accounts " .
			$user .
			" " .
			quoteshellarg($_GET["domain"]) .
			" json",
		$output,
		$return_var,
	);
	$data = json_decode(implode("", $output), true);
	if ($_SESSION["userSortOrder"] == "name") {
		ksort($data);
	} else {
		$data = array_reverse($data, true);
	}
	unset($output);

	render_page($user, $TAB, "list_mail_acc");
}

// Back uri
$_SESSION["back"] = $_SERVER["REQUEST_URI"];
