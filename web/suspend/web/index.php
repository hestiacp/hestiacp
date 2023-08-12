<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check token
verify_csrf($_GET);

if (!empty($_GET["domain"])) {
	$v_username = quoteshellarg($user);
	$v_domain = quoteshellarg($_GET["domain"]);
	exec(HESTIA_CMD . "v-suspend-web-domain " . $user . " " . $v_domain, $output, $return_var);
}
check_return_code($return_var, $output);
unset($output);

$back = $_SESSION["back"];
if (!empty($back)) {
	header("Location: " . $back);
	exit();
}

header("Location: /list/web/");
exit();
