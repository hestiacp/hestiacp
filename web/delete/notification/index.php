<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check token
verify_csrf($_GET);

if ($_GET["delete"] == 1) {
	if (empty($_GET["notification_id"])) {
		exec(HESTIA_CMD . "v-delete-user-notification " . $user . " all", $output, $return_var);
	} else {
		$v_id = quoteshellarg((int) $_GET["notification_id"]);
		exec(
			HESTIA_CMD . "v-delete-user-notification " . $user . " " . $v_id,
			$output,
			$return_var,
		);
	}
	check_return_code($return_var, $output);
	unset($output);
} else {
	$v_id = quoteshellarg((int) $_GET["notification_id"]);
	exec(
		HESTIA_CMD . "v-acknowledge-user-notification " . $user . " " . $v_id,
		$output,
		$return_var,
	);
	check_return_code($return_var, $output);
	unset($output);
}

exit();
