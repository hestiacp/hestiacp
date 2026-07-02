<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

if ($_SESSION["userContext"] != "admin") {
    header("Location: /list/user");
    exit();
}

verify_csrf($_GET);

if (!empty($_GET["rule"])) {
    $v_rule = quoteshellarg($_GET["rule"]);
    exec(HESTIA_CMD . "v-delete-firewall-rule-ipv6 " . $v_rule, $output, $return_var);
}
check_return_code($return_var, $output);
unset($output);

header("Location: /list/firewall/ipv6/");
exit();
