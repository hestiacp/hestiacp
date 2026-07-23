<?php
$TAB = "FIREWALL";

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

if ($_SESSION["userContext"] != "admin") {
	header("Location: /list/user");
	exit();
}

exec(HESTIA_CMD . "v-list-firewall-ipv6 json", $output, $return_var);
$data = json_decode(implode("", $output), true);
if ($_SESSION["userSortOrder"] == "name") {
	ksort($data);
} else {
	$data = array_reverse($data, true);
}
unset($output);

render_page($user, $TAB, "list_firewall_ipv6");

$_SESSION["back"] = $_SERVER["REQUEST_URI"];
