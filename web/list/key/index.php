<?php
use function Hestiacp\quoteshellarg\quoteshellarg;
$TAB = "USER";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

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

exec(HESTIA_CMD . "v-list-user-ssh-key " . $user . " json", $output, $return_var);
if ($return_var > 0) {
	check_return_code_redirect($return_var, $output, "/");
}
$data = json_decode(implode("", $output), true);

// Render page\
render_page($user, $TAB, "list_key");

// Back uri
$_SESSION["back"] = $_SERVER["REQUEST_URI"];
?>
