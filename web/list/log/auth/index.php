<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
$TAB = "LOG";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Edit as someone else?
if ($_SESSION["userContext"] === "admin" && $_SESSION["look"] != "") {
	$user = quoteshellarg($_SESSION["look"]);
} elseif ($_SESSION["userContext"] === "admin" && !empty($_GET["user"])) {
	$user = quoteshellarg($_GET["user"]);
}

exec(HESTIA_CMD . "v-list-user-auth-log " . $user . " json", $output, $return_var);
check_return_code_redirect($return_var, $output, "/");

$data = json_decode(implode("", $output), true);
$data = array_reverse($data);
unset($output);

// Render page
render_page($user, $TAB, "list_log_auth");

// Flush session messages
unset($_SESSION["error_msg"]);
unset($_SESSION["ok_msg"]);
