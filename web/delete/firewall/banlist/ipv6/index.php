<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

if ($_SESSION["userContext"] != "admin") {
	header("Location: /list/user");
	exit();
}

verify_csrf($_GET);

if (!empty($_GET["ip"]) && !empty($_GET["chain"])) {
	$v_ip = quoteshellarg($_GET["ip"]);
	$v_chain = quoteshellarg($_GET["chain"]);
	exec(HESTIA_CMD . "v-delete-firewall-ban-ipv6 " . $v_ip . " " . $v_chain, $output, $return_var);
}
check_return_code($return_var, $output);
unset($output);

header("Location: /list/firewall/banlist/ipv6/");
exit();
