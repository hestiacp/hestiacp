<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check token
verify_csrf($_GET);

$database = quoteshellarg($_GET["database"]);

exec(
	HESTIA_CMD . "v-dump-database " . $user . " " . $database . " file gzip",
	$output,
	$return_var,
);

if ($return_var == 0) {
	header("Content-type: application/sql");
	header("Content-Disposition: attachment; filename=" . $output[0]);
	header("X-Accel-Redirect: " . $output[0]);
}
