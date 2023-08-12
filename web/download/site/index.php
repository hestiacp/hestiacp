<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check token
verify_csrf($_GET);

$site = quoteshellarg($_GET["site"]);

exec(HESTIA_CMD . "v-dump-site " . $user . " " . $site . " full", $output, $return_var);

if ($return_var == 0) {
	header("Content-type: application/zip");
	header("Content-Disposition: attachment; filename=" . $output[0]);
	header("X-Accel-Redirect: " . $output[1]);
}
