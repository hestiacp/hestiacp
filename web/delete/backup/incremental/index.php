<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

if ($_SESSION["userContext"] === "admin" && !empty($_GET["user"])) {
	$user = quoteshellarg($_GET["user"]);
}

// Check token
verify_csrf($_GET);

if ($read_only === true) {
	header("Location: /list/backup/incremental/");
	exit();
}

if (!empty($_GET["snapshot"])) {
	$v_snapshot = quoteshellarg($_GET["snapshot"]);
	exec(
		HESTIA_CMD . "v-delete-user-backup-restic " . $user . " " . $v_snapshot,
		$output,
		$return_var,
	);
	check_return_code($return_var, $output);
	unset($output);
}

header("Location: /list/backup/incremental/");
exit();
