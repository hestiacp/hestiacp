<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check user
if ($_SESSION["userContext"] != "admin") {
	header("Location: /list/user");
	exit();
}

// Check token
verify_csrf($_GET);

if (!empty($_GET["rule"])) {
	$v_rule = quoteshellarg($_GET["rule"]);
	exec(HESTIA_CMD . "v-delete-firewall-rule " . $v_rule, $output, $return_var);
}
check_return_code($return_var, $output);
unset($output);

$back = $_SESSION["back"];
if (!empty($back)) {
	header("Location: " . $back);
	exit();
}

header("Location: /list/firewall/");
exit();
