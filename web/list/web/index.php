<?php
$TAB = "WEB";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/inc/domain_sorting.php";

// Data
exec(HESTIA_CMD . "v-list-web-domains " . $user . " 'json'", $output, $return_var);
$data = json_decode(implode("", $output), true);
$domain_sort_keys = get_domain_sort_keys(array_keys($data));
if ($_SESSION["userSortOrder"] == "name") {
	$data = sort_domains_by_name($data, $domain_sort_keys);
} else {
	$data = array_reverse($data, true);
}
$ips = json_decode(shell_exec(HESTIA_CMD . "v-list-sys-ips json"), true);

// Render page
render_page($user, $TAB, "list_web");

// Back uri
$_SESSION["back"] = $_SERVER["REQUEST_URI"];
