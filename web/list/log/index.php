<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

if (empty($_GET["user"])) {
	$_GET["user"] = "";
}
if ($_GET["user"] === "system") {
	$TAB = "SERVER";
} else {
	$TAB = "LOG";
}

// Redirect non-administrators if they request another user's log
if ($_SESSION["userContext"] !== "admin" && !empty($_GET["user"])) {
	header("location: /login/");
	exit();
}

// Data
if ($_SESSION["userContext"] === "admin" && !empty($_GET["user"])) {
	// Check token
	verify_csrf($_GET);
	$user = quoteshellarg($_GET["user"]);
}

exec(HESTIA_CMD . "v-list-user-log $user json", $output, $return_var);
check_error($return_var);
$data = json_decode(implode("", $output), true);
if (is_array($data)) {
	$data = array_reverse($data);
	unset($output);

	// Render page
	if ($user === "system") {
		$user = "'" . $_SESSION["user"] . "'";
	}
} else {
	$data = [];
	$data[] = [
		"LEVEL" => "error",
		"DATE" => date("Y-m-d"),
		"TIME" => date("H:i:s"),
		"MESSAGE" => "Unable to load logs",
		"CATEGORY" => "system",
	];
}
render_page($user, $TAB, "list_log");
