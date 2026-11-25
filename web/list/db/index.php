<?php
$TAB = "DB";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

$prepare_sso = $_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["pma_sso"]);
if ($prepare_sso) {
	header("Content-Type: application/json");
	header("Cache-Control: no-store");
	if (
		!isset($_POST["token"]) ||
		!verify_csrf($_POST, true) ||
		empty($_SESSION["PHPMYADMIN_KEY"]) ||
		ipUsed()
	) {
		echo json_encode(["error" => "invalid"]);
		exit;
	}
}

// Data
exec(HESTIA_CMD . "v-list-databases $user json", $output, $return_var);
$data = json_decode(implode("", $output), true);
if ($prepare_sso) {
	$database = $_POST["database"] ?? null;
	if (
		!is_string($database) ||
		($data[$database]["TYPE"] ?? null) !== "mysql" ||
		($data[$database]["SUSPENDED"] ?? null) !== "no" ||
		!is_string($data[$database]["HOST"] ?? null)
	) {
		echo json_encode(["error" => "invalid"]);
		exit;
	}
	require $_SERVER["DOCUMENT_ROOT"] . "/inc/phpmyadmin-sso.php";
	echo json_encode(phpmyadmin_sso_launch($database, $user_plain, $data[$database]["HOST"]));
	exit;
}
if ($_SESSION["userSortOrder"] == "name") {
	ksort($data);
} else {
	$data = array_reverse($data, true);
}
unset($output);

// Render page
render_page($user, $TAB, "list_db");

// Back uri
$_SESSION["back"] = $_SERVER["REQUEST_URI"];
