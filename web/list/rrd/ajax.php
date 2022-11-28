<?php
use function Hestiacp\quoteshellarg\quoteshellarg;
// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check user
if ($_SESSION["userContext"] != "admin") {
	header("Location: /list/user");
	exit();
}

if (empty($_POST["period"])) {
	$period = "daily";
} else {
	if (in_array($_POST["period"], ["day", "week", "month", "year"])) {
		$period = $_POST["period"];
	} else {
		$period = "daily";
	}
}

if (empty($_POST["service"])) {
	$service = "la";
} else {
	$service = $_POST["service"];
}

// Data
exec(
	HESTIA_CMD . "v-export-rrd " . quoteshellarg($service) . " " . quoteshellarg($period),
	$output,
	$return_var,
);
$data = json_decode(implode("", $output), true);
$data["service"] = $service;
echo json_encode($data);
