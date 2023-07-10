<?php
ob_start();
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check token
verify_csrf($_GET);

if (
	($_SESSION["userContext"] === "admin" && $_SESSION["POLICY_SYSTEM_HIDE_SERVICES"] == "no") ||
	$_SESSION["user"] == "admin"
) {
	exec(HESTIA_CMD . "v-add-cron-hestia-autoupdate", $output, $return_var);
	unset($output);
}

header("Location: /list/updates/");
exit();
