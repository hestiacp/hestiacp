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
    header("Location: /list/firewall/banlist/ipv6/");
    exit();
}

$ipchain = $_POST["ipchain"];
$action  = $_POST["action"] ?? "";

switch ($action) {
    case "delete": $cmd = "v-delete-firewall-ban-ipv6"; break;
    default:
        header("Location: /list/firewall/banlist/ipv6/");
        exit();
}

foreach ($ipchain as $value) {
    [$ip, $chain] = explode(":", $value);
    $v_ip    = quoteshellarg($ip);
    $v_chain = quoteshellarg($chain);
    exec(HESTIA_CMD . $cmd . " " . $v_ip . " " . $v_chain, $output, $return_var);
}

header("Location: /list/firewall/banlist/ipv6/");
exit();
