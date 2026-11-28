<?php
use function Hestiacp\quoteshellarg\quoteshellarg;
$TAB = "BACKUP";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

foreach (["backup", "q"] as $param) {
	if (isset($_GET[$param]) && !is_string($_GET[$param])) {
		$_GET[$param] = "";
	}
}
if (isset($_POST["q"]) && !is_string($_POST["q"])) {
	$_POST["q"] = "";
}

// Data & Render page
if (empty($_GET["backup"])) {
	exec(HESTIA_CMD . "v-list-user-backups $user json", $output, $return_var);
	$data = json_decode(implode("", $output), true);
	if (!is_array($data)) {
		$data = [];
	}
	if ($_SESSION["userSortOrder"] == "name") {
		ksort($data);
	} else {
		$data = array_reverse($data, true);
	}
	if (!empty($_GET["q"])) {
		$q = $_GET["q"];
		$data = array_filter(
			$data,
			function ($value, $key) use ($q) {
				$haystack = implode(" ", [
					$key,
					$value["DATE"] ?? "",
					$value["TIME"] ?? "",
					$value["TYPE"] ?? "",
					$value["WEB"] ?? "",
					$value["DNS"] ?? "",
					$value["MAIL"] ?? "",
					$value["DB"] ?? "",
				]);
				return stripos($haystack, $q) !== false;
			},
			ARRAY_FILTER_USE_BOTH,
		);
	}
	unset($output);
	render_page($user, $TAB, "list_backup");
} else {
	exec(
		HESTIA_CMD . "v-list-user-backup $user " . quoteshellarg($_GET["backup"]) . " json",
		$output,
		$return_var,
	);
	$data = json_decode(implode("", $output), true);
	if (is_array($data)) {
		$data = array_reverse($data, true);
	} else {
		$data = [];
	}
	unset($output);

	render_page($user, $TAB, "list_backup_detail");
}

// Back uri
$_SESSION["back"] = $_SERVER["REQUEST_URI"];
