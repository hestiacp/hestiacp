<?php
$TAB = "SERVER";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check user
if ($_SESSION["userContext"] !== "admin") {
	header("Location: /list/user");
	exit();
}

exec(HESTIA_CMD . "v-list-database-hosts json", $output, $return_var);
check_return_code($return_var, $output);
$data = json_decode(implode("", $output), true);
$data = is_array($data) ? $data : [];
unset($output);

render_page($user, $TAB, "list_db_host");

$_SESSION["back"] = $_SERVER["REQUEST_URI"];

unset($_SESSION["error_msg"]);
unset($_SESSION["ok_msg"]);
