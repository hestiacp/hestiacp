<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
$TAB = "WEB";

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

if ($_SESSION["userContext"] === "admin" && !empty($_GET["user"])) {
	$user = quoteshellarg($_GET["user"]);
	$user_plain = htmlentities($_GET["user"]);
}

$v_laravel_user_query = [];
if ($_SESSION["userContext"] === "admin" && !empty($_GET["user"])) {
	$v_laravel_user_query["user"] = $_GET["user"];
}

exec(HESTIA_CMD . "v-list-laravel-apps " . $user . " json", $output, $return_var);
check_return_code($return_var, $output);
$v_laravel_apps = json_decode(implode("", $output), true);
unset($output);

render_page($user, $TAB, "list_laravel");
