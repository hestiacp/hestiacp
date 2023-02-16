<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check token
verify_csrf($_GET);

if ($_SESSION["userContext"] === "admin") {
	if (!empty($_GET["ip"])) {
		$v_ip = quoteshellarg($_GET["ip"]);
		if (filter_var($_GET["ip"], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
			$v_ipv6_suffix='v6';
		} else {
			$v_ipv6_suffix='';
		}
		exec(HESTIA_CMD . "v-delete-sys-ip".$v_ipv6_suffix." " . $v_ip, $output, $return_var);
	}
	check_return_code($return_var, $output);
	unset($output);
}

$back = $_SESSION["back"];
if (!empty($back)) {
	header("Location: " . $back);
	exit();
}

header("Location: /list/ip/");
exit();
