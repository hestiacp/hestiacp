<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

$TAB = "SEARCH";

$_SESSION["back"] = $_SERVER["REQUEST_URI"];

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check token
verify_csrf($_GET);

if (empty($_GET["u"])) {
	$_GET["u"] = "";
}
if (empty($_GET["q"])) {
	$_GET["q"] = "";
}
// Data
$q = quoteshellarg($_GET["q"]);
$u = quoteshellarg($_GET["u"]);

if ($_SESSION["userContext"] === "admin" && $_SESSION["look"] == "") {
	if (!empty($_GET["u"])) {
		$user = $u;
		exec(
			HESTIA_CMD . "v-search-user-object " . $user . " " . $q . " json",
			$output,
			$return_var,
		);
	} else {
		exec(HESTIA_CMD . "v-search-object " . $q . " json", $output, $return_var);
	}
} else {
	exec(HESTIA_CMD . "v-search-user-object " . $user . " " . $q . " json", $output, $return_var);
}

$data = json_decode(implode("", $output), true);

// Render page
render_page($user, $TAB, "list_search");
