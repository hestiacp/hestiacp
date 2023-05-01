<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
$TAB = "FIREWALL";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check user
if ($_SESSION["userContext"] != "admin") {
	header("Location: /list/user");
	exit();
}

// Check POST request
if (!empty($_POST["ok"])) {
	// Check token
	verify_csrf($_POST);

	// Check empty fields
	if (empty($_POST["v_chain"])) {
		$errors[] = _("Banlist");
	}
	if (empty($_POST["v_ip"])) {
		$errors[] = _("IP Address");
	}
	if (!empty($errors[0])) {
		foreach ($errors as $i => $error) {
			if ($i == 0) {
				$error_msg = $error;
			} else {
				$error_msg = $error_msg . ", " . $error;
			}
		}
		$_SESSION["error_msg"] = sprintf(_('Field "%s" can not be blank.'), $error_msg);
	}

	// Protect input
	$v_chain = quoteshellarg($_POST["v_chain"]);
	$v_ip = quoteshellarg($_POST["v_ip"]);

	// Add firewall rule
	if (empty($_SESSION["error_msg"])) {
		exec(HESTIA_CMD . "v-add-firewall-ban " . $v_ip . " " . $v_chain, $output, $return_var);
		check_return_code($return_var, $output);
		unset($output);
	}

	// Flush field values on success
	if (empty($_SESSION["error_msg"])) {
		$_SESSION["ok_msg"] = _("IP address has been banned successfully.");
		unset($v_chain);
		unset($v_ip);
	}
}

if (empty($v_ip)) {
	$v_ip = "";
}
if (empty($v_chain)) {
	$v_chain = "";
}
// Render
render_page($user, $TAB, "add_firewall_banlist");

// Flush session messages
unset($_SESSION["error_msg"]);
unset($_SESSION["ok_msg"]);
