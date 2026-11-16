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
$ips = json_decode(shell_exec(HESTIA_CMD . "v-list-sys-ips json"), true);

// Web domain apps (hide the app actions if the feature is not available)
unset($output);
exec(HESTIA_CMD . "v-list-web-domain-apps " . $user . " json", $output, $return_var);
$apps_enabled = $return_var == 0;
$apps = $apps_enabled ? json_decode(implode("", $output), true) : [];
if (!is_array($apps)) {
	$apps = [];
}
unset($output);

// Render page
render_page($user, $TAB, "list_web");

// Back uri
$_SESSION["back"] = $_SERVER["REQUEST_URI"];
