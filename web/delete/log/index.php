<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check token
verify_csrf($_GET);

// Check if administrator is viewing system log (currently 'admin' user)
if ($_SESSION["userContext"] === "admin" && !empty($_GET["user"])) {
	$user = quoteshellarg($_GET["user"]);
	$token = $_SESSION["token"];
}

// Clear log
exec(HESTIA_CMD . "v-delete-user-log " . $user, $output, $return_var);
check_return_code($return_var, $output);
unset($output);

if ($return_var > 0) {
	header("Location: /list/log/");
} else {
	// Set correct page reload target
	if ($_SESSION["userContext"] === "admin" && !empty($_GET["user"])) {
		if ($_GET["user"] != "system") {
			header("Location: /list/log/?user=" . $_GET["user"] . "&token=$token");
		} else {
			header("Location: /list/log/?user=system&token=$token");
		}
	} else {
		header("Location: /list/log/");
	}
}

// Flush session messages
unset($_SESSION["error_msg"]);
unset($_SESSION["ok_msg"]);

exit();
