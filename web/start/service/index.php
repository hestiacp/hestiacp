<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

// Init
ob_start();
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check token
verify_csrf($_GET);

if ($_SESSION["userContext"] === "admin") {
	if (!empty($_GET["srv"])) {
		if ($_GET["srv"] == "iptables") {
			exec(HESTIA_CMD . "v-update-firewall", $output, $return_var);
		} else {
			$v_service = quoteshellarg($_GET["srv"]);
			exec(HESTIA_CMD . "v-start-service " . $v_service, $output, $return_var);
		}
	}
	if ($return_var != 0) {
		$error = implode("<br>", $output);
		if (empty($error)) {
			$error = _('Start "%s" failed', $v_service);
		}
		$_SESSION["error_srv"] = $error;
	}
	unset($output);
}

header("Location: /list/server/");
exit();
