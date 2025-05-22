<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

verify_csrf($_POST);

if ($_SESSION["userContext"] != "admin") {
	header("Location: /list/user");
	exit();
}

if (empty($_POST["ipchain"])) {
	header("Location: /list/firewall/banlist/");
	exit();
}
if (empty($_POST["action"])) {
	header("Location: /list/firewall/banlist/");
	exit();
}

$ipchain = $_POST["ipchain"];
$action = $_POST["action"];

// Detect if we are on the IPv6 tab
$type = (isset($_POST['ipver']) && $_POST['ipver'] == 'ipv6') ? 'ipv6' : 'ipv4';

switch ($action) {
	case "delete":
		$cmd = ($type === 'ipv6') ? "v-delete-firewall-ban-ipv6" : "v-delete-firewall-ban";
		break;
	default:
		header("Location: /list/firewall/banlist/");
		exit();
}

foreach ($ipchain as $value) {
    $last_colon = strrpos($value, ':');
    $ip = substr($value, 0, $last_colon);
    $chain = substr($value, $last_colon + 1);
    $v_ip = quoteshellarg($ip);
    $v_chain = quoteshellarg($chain);
    exec(HESTIA_CMD . $cmd . " " . $v_ip . " " . $v_chain, $output, $return_var);
}

header("Location: /list/firewall/banlist/?ipver=" . $type);
exit();
