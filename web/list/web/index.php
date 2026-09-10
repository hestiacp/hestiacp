<?php
$TAB = "WEB";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Data
exec(HESTIA_CMD . "v-list-web-domains " . $user . " 'json'", $output, $return_var);
$data = json_decode(implode("", $output), true);
if ($_SESSION["userSortOrder"] == "name") {
	ksort($data);
} else {
	$data = array_reverse($data, true);
}
if (is_subdomain_grouping_enabled()) {
	$data = group_domains_by_parent($data);
}
$ips = json_decode(shell_exec(HESTIA_CMD . "v-list-sys-ips json"), true);

// Render page
render_page($user, $TAB, "list_web");

// Back uri
$_SESSION["back"] = $_SERVER["REQUEST_URI"];
