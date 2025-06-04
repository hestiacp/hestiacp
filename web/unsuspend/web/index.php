<?php
use function DevITcp\quoteshellarg\quoteshellarg;

// Init
ob_start();
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check token
verify_csrf($_GET);

if (!empty($_GET["domain"])) {
	$v_domain = quoteshellarg($_GET["domain"]);
	exec(DevIT_CMD . "v-unsuspend-domain " . $user . " " . $v_domain, $output, $return_var);
	check_return_code($return_var, $output);
	unset($output);
}

$back = getenv("HTTP_REFERER");
if (!empty($back)) {
	header("Location: " . $back);
	exit();
}

header("Location: /list/web/");
exit();
