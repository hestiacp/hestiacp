<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

verify_csrf($_POST);

if ($_SESSION["userContext"] != "admin") {
    header("Location: /list/user");
    exit();
}

if (empty($_POST["rule"])) {
    header("Location: /list/firewall/ipv6/");
    exit();
}
if (empty($_POST["action"])) {
    header("Location: /list/firewall/ipv6/");
    exit();
}

$rule   = $_POST["rule"];
$action = $_POST["action"];

switch ($action) {
    case "delete":    $cmd = "v-delete-firewall-rule-ipv6";    break;
    case "suspend":   $cmd = "v-suspend-firewall-rule-ipv6";   break;
    case "unsuspend": $cmd = "v-unsuspend-firewall-rule-ipv6"; break;
    default:
        header("Location: /list/firewall/ipv6/");
        exit();
}

foreach ($rule as $value) {
    $value = quoteshellarg($value);
    exec(HESTIA_CMD . $cmd . " " . $value, $output, $return_var);
}

header("Location: /list/firewall/ipv6/");
exit();
