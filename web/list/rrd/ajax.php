<?php
use function Hestiacp\quoteshellarg\quoteshellarg;
// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check user
if ($_SESSION["userContext"] != "admin") {
	header("Location: /list/user");
	exit();
}

$requestPayload = json_decode(file_get_contents("php://input"), true);

$allowedPeriods = ["daily", "weekly", "monthly", "yearly"];

$period =
	!empty($requestPayload["period"]) && in_array($requestPayload["period"], $allowedPeriods)
		? $requestPayload["period"]
		: "daily";

$service = !empty($requestPayload["service"]) ? $requestPayload["service"] : "la";

// Data
exec(
	HESTIA_CMD . "v-export-rrd " . quoteshellarg($service) . " " . quoteshellarg($period),
	$output,
	$return_var,
);

if ($return_var != 0) {
	http_response_code(500);
	exit("Error fetching RRD data");
}

$data = json_decode(implode("", $output), true);
$data["service"] = $service;
echo json_encode($data);
