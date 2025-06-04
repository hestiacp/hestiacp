<?php
$TAB = "FIREWALL";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check user
if ($_SESSION["userContext"] != "admin") {
	header("Location: /list/user");
	exit();
}

// Data
exec(DevIT_CMD . "v-list-firewall-ipset json", $output, $return_var);
$data = json_decode(implode("", $output), true);
ksort($data);

// Render page
render_page($user, $TAB, "list_firewall_ipset");

// Back uri
$_SESSION["back"] = $_SERVER["REQUEST_URI"];
