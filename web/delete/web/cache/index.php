<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check token
verify_csrf($_GET);

// Delete as someone else?
if ($_SESSION["userContext"] === "admin" && !empty($_GET["user"])) {
	$user = quoteshellarg($_GET["user"]);
}

if (!empty($_GET["domain"])) {
	$v_domain = quoteshellarg($_GET["domain"]);
	exec(HESTIA_CMD . "v-purge-nginx-cache " . $user . " " . $v_domain, $output, $return_var);
	check_return_code($return_var, $output);
}
$_SESSION["ok_msg"] = _("NGINX cache has been purged successfully.");
header("Location: /edit/web/?domain=" . $_GET["domain"]);
exit();
