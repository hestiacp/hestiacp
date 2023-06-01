<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check user
if ($_SESSION["userContext"] != "admin") {
	header("Location: /list/user/");
	exit();
}

// Check token
verify_csrf($_GET);

if (!empty($_GET["plugin"])) {
	$v_plugin = quoteshellarg($_GET["plugin"]);
	exec(HESTIA_CMD . "v-disable-plugin " . $v_plugin . " 'json'", $output, $return_var);
	check_return_code($return_var, $output);
	unset($output);
}

header("Location: /list/plugin/");
exit();
