<?php
$TAB = "FIREWALL";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check user
if ($_SESSION["userContext"] != "admin") {
    header("Location: /list/user");
    exit();
}

// Data IPv4
exec(HESTIA_CMD . "v-list-firewall json", $output_v4, $return_var4);
$data_v4 = json_decode(implode("", $output_v4), true);
unset($output_v4);

if ($_SESSION["userSortOrder"] == "name") {
    ksort($data_v4);
} else {
    $data_v4 = array_reverse($data_v4, true);
}

// Data IPv6
exec(HESTIA_CMD . "v-list-firewall-ipv6 json", $output_v6, $return_var6);
$data_v6 = json_decode(implode("", $output_v6), true);
unset($output_v6);

if ($_SESSION["userSortOrder"] == "name") {
    ksort($data_v6);
} else {
    $data_v6 = array_reverse($data_v6, true);
}

// Detects whether the view is IPv4 or IPv6 (via GET)
$type = isset($_GET['ipver']) && $_GET['ipver'] == 'ipv6' ? 'ipv6' : 'ipv4';
$data = $type === 'ipv6' ? $data_v6 : $data_v4;

// Render page (the variables $data_v4 and $data_v6 will be available in the view)
render_page($user, $TAB, "list_firewall");

// Back uri
$_SESSION["back"] = $_SERVER["REQUEST_URI"];
