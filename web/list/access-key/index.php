<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

$TAB = "Access Key";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

if ($_SESSION["userContext"] === "admin" && !empty($_GET["user"])) {
	$user = quoteshellarg($_GET["user"]);
	$user_plain = $_GET["user"];
}

// Checks if API access is enabled
$api_status =
	!empty($_SESSION["API_SYSTEM"]) && is_numeric($_SESSION["API_SYSTEM"])
		? $_SESSION["API_SYSTEM"]
		: 0;
if ($api_status < 1 || ($user_plain != $_SESSION["ROOT_USER"] && $api_status < 2)) {
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

	// APIs
	exec(HESTIA_CMD . "v-list-apis json", $output, $return_var);
	$apis = json_decode(implode("", $output), true);
	$apis = array_filter($apis, function ($api) use ($user_plain) {
		return $user_plain == "admin" || $api["ROLE"] == "user";
	});
	ksort($apis);
	unset($output);

	render_page($user, $TAB, "list_access_key");
} else {
	exec(HESTIA_CMD . "v-list-access-keys $user json", $output, $return_var);
	$data = json_decode(implode("", $output), true);

	uasort($data, function ($a, $b) {
		return $a["DATE"] <=> $b["DATE"] ?: $a["TIME"] <=> $b["TIME"];
	});
	unset($output);

	// Render page
	render_page($user, $TAB, "list_access_keys");
}

// Back uri
$_SESSION["back"] = $_SERVER["REQUEST_URI"];
