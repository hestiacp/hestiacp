<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check token
verify_csrf($_GET);

$type = "";
if ($_GET["type"] == "access") {
	$type = "access";
}
if ($_GET["type"] == "error") {
	$type = "error";
}

if (empty($type)) {
	http_response_code(400);
	echo "Error: Invalid log type";
	exit();
}

$v_domain = $_GET["domain"];
$safe_filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $v_domain);

header("Cache-Control: public");
header("Content-Description: File Transfer");
header("Content-Disposition: attachment; filename=" . $safe_filename . "." . $type . "-log.txt");
header("Content-Type: application/octet-stream; ");
header("Content-Transfer-Encoding: binary");

$cmd = implode(" ", [
	"/usr/bin/sudo " . quoteshellarg(HESTIA_DIR_BIN . "v-list-web-domain-" . $type . "log"),
	// $user is already shell-escaped
	$user,
	quoteshellarg($v_domain),
	"5000",
]);

passthru($cmd, $return_var);
if ($return_var != 0) {
	$errstr = "Internal server error: command returned non-zero: {$return_var}: {$cmd}";
	echo $errstr;
	throw new Exception($errstr); // make sure it ends up in an errorlog somewhere
}
