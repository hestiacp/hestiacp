<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check token
verify_csrf($_GET);

// Update as someone else?
$user_query = [];
if ($_SESSION["userContext"] === "admin" && !empty($_GET["user"])) {
	$user = quoteshellarg($_GET["user"]);
	$user_query = ["user" => $_GET["user"]];
}

if ($read_only === true || empty($_GET["domain"])) {
	header("Location: /list/app/?" . http_build_query($user_query));
	exit();
}

$domain = $_GET["domain"];

// Installing dependencies / building can take a few minutes:
// do not time out and do not keep the session locked meanwhile
set_time_limit(0);
session_write_close();

exec(
	HESTIA_CMD . "v-update-web-domain-app-deps " . $user . " " . quoteshellarg($domain) . " 2>&1",
	$output,
	$return_var,
);

session_start();
if (!isset($_SESSION["app_deps_output"]) || !is_array($_SESSION["app_deps_output"])) {
	$_SESSION["app_deps_output"] = [];
}
$_SESSION["app_deps_output"][$domain] = implode("\n", $output);
if ($return_var != 0) {
	$_SESSION["error_msg"] = sprintf(
		_("The install / build command failed (error code %s). See the output below."),
		$return_var,
	);
} else {
	$_SESSION["ok_msg"] = _("Dependencies have been installed.");
}
unset($output);

header("Location: /edit/app/?" . http_build_query(array_merge(["domain" => $domain], $user_query)));
exit();
