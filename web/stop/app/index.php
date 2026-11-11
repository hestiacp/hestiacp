<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check token
verify_csrf($_GET);

// Stop as someone else?
$user_query = [];
if ($_SESSION["userContext"] === "admin" && !empty($_GET["user"])) {
	$user = quoteshellarg($_GET["user"]);
	$user_query = ["user" => $_GET["user"]];
}

if ($read_only === true) {
	header("Location: /list/app/?" . http_build_query($user_query));
	exit();
}

if (!empty($_GET["domain"])) {
	$v_domain = quoteshellarg($_GET["domain"]);
	exec(HESTIA_CMD . "v-stop-web-domain-app " . $user . " " . $v_domain, $output, $return_var);
	check_return_code($return_var, $output);
	if ($return_var == 0) {
		$_SESSION["ok_msg"] = sprintf(
			_("App for %s has been stopped."),
			htmlentities($_GET["domain"]),
		);
	}
	unset($output);
}

$back = $_SESSION["back"] ?? "";
if (!empty($back)) {
	header("Location: " . $back);
	exit();
}

header("Location: /list/app/?" . http_build_query($user_query));
exit();
