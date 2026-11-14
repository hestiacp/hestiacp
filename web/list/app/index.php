<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

$TAB = "WEB";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// List as someone else?
$user_query = [];
if ($_SESSION["userContext"] === "admin" && !empty($_GET["user"])) {
	$user = quoteshellarg($_GET["user"]);
	$user_plain = htmlentities($_GET["user"]);
	$user_query = ["user" => $_GET["user"]];
}

// Data
exec(HESTIA_CMD . "v-list-web-domain-apps " . $user . " json", $output, $return_var);
check_return_code($return_var, $output);
$data = json_decode(implode("", $output), true);
if (!is_array($data)) {
	$data = [];
}
if ($_SESSION["userSortOrder"] == "name") {
	ksort($data);
} else {
	$data = array_reverse($data, true);
}
unset($output);

// Render page
render_page($user, $TAB, "list_apps");

// Back uri
$_SESSION["back"] = $_SERVER["REQUEST_URI"];
