<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check token
verify_csrf($_GET);

if ($_SESSION["userContext"] === "admin" && !empty($_GET["user"])) {
	$user = quoteshellarg($_GET["user"]);
	$user_plain = $_GET["user"];
}

// Checks if API access is enabled
$api_status =
	!empty($_SESSION["API_SYSTEM"]) && is_numeric($_SESSION["API_SYSTEM"])
		? $_SESSION["API_SYSTEM"]
		: 0;
if (
	($user_plain == $_SESSION["ROOT_USER"] && $api_status < 1) ||
	($user_plain != $_SESSION["ROOT_USER"] && $api_status < 2)
) {
	header("Location: /edit/user/");
	exit();
}

if (!empty($_GET["key"])) {
	$v_key = quoteshellarg(trim($_GET["key"]));

	// Key data
	exec(HESTIA_CMD . "v-list-access-key " . $v_key . " json", $output, $return_var);
	$key_data = json_decode(implode("", $output), true);
	unset($output);

	if (empty($key_data) || $key_data["USER"] != $user_plain) {
		header("Location: /list/access-key/");
		exit();
	}

	exec(HESTIA_CMD . "v-delete-access-key " . $v_key, $output, $return_var);
	check_return_code($return_var, $output);
	unset($output);
}

$back = $_SESSION["back"];
if (!empty($back)) {
	header("Location: " . $back);
	exit();
}
header("Location: /list/key/");
exit();
