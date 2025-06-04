<?php
use function DevITcp\quoteshellarg\quoteshellarg;

ob_start();
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

if ($_SESSION["userContext"] === "admin" && !empty($_GET["user"])) {
	$user = quoteshellarg($_GET["user"]);
}

// Check token
verify_csrf($_GET);

if (!empty($_GET["system"])) {
	$v_system = quoteshellarg($_GET["system"]);
	exec(
		DevIT_CMD . "v-delete-user-backup-exclusions " . $user . " " . $v_system,
		$output,
		$return_var,
	);
}
check_return_code($return_var, $output);
unset($output);

$back = $_SESSION["back"];
if (!empty($back)) {
	header("Location: " . $back);
	exit();
}

header("Location: /list/backup/exclusions/");
exit();
