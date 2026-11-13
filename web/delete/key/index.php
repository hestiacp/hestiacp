<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check token
verify_csrf($_GET);

if ($read_only === true) {
	header("Location: /list/key/");
	exit();
}

// Prevent other users with admin privileges from managing SSH keys of the ROOT_USER account.
// An SSH key for ROOT_USER is equivalent to taking over the account.
$target_user = $user_plain;
if ($_SESSION["userContext"] === "admin" && !empty($_GET["user"])) {
	$user = quoteshellarg($_GET["user"]);
	$target_user = htmlentities($_GET["user"]);
}
if (is_protected_root_target($target_user)) {
	header("Location: /list/user/");
	exit();
}

if (!empty($_GET["key"])) {
	$v_key = quoteshellarg(trim($_GET["key"]));
	exec(HESTIA_CMD . "v-delete-user-ssh-key " . $user . " " . $v_key, $output, $return_var);
	check_return_code($return_var, $output);
}

unset($output);

//die();
$back = $_SESSION["back"];
if (!empty($back)) {
	header("Location: " . $back);
	exit();
}
header("Location: /list/key/");
exit();
