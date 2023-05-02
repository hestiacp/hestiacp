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

// Get ipset lists
exec(HESTIA_CMD . "v-list-firewall-ipset 'json'", $output, $return_var);
check_return_code($return_var, $output);
$data = json_decode(implode("", $output), true);
unset($output);

$ipset_lists = [];
foreach ($data as $key => $value) {
	if (isset($value["SUSPENDED"]) && $value["SUSPENDED"] === "yes") {
		continue;
	}
	if (isset($value["IP_VERSION"]) && $value["IP_VERSION"] !== "v4") {
		continue;
	}
	array_push($ipset_lists, ["name" => $key]);
}
$ipset_lists_json = json_encode($ipset_lists);

// Check POST request
if (!empty($_POST["ok"])) {
	// Check token
	// Check token
	verify_csrf($_POST);

	// Check empty fields
	if (empty($_POST["v_action"])) {
		$errors[] = _("Action");
	}
	if (empty($_POST["v_protocol"])) {
		$errors[] = _("Protocol");
	}
	if (empty($_POST["v_port"]) && strlen($_POST["v_port"]) == 0) {
		$errors[] = _("Port");
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
	$v_action = quoteshellarg($_POST["v_action"]);
	$v_protocol = quoteshellarg($_POST["v_protocol"]);
	$v_port = str_replace(" ", ",", $_POST["v_port"]);
	$v_port = preg_replace("/\,+/", ",", $v_port);
	$v_port = trim($v_port, ",");
	$v_port = quoteshellarg($v_port);
	$v_ip = quoteshellarg($_POST["v_ip"]);
	$v_comment = quoteshellarg($_POST["v_comment"]);

	// Add firewall rule
	if (empty($_SESSION["error_msg"])) {
		exec(
			HESTIA_CMD .
				"v-add-firewall-rule " .
				$v_action .
				" " .
				$v_ip .
				" " .
				$v_port .
				" " .
				$v_protocol .
				" " .
				$v_comment,
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
	}

	// Flush field values on success
	if (empty($_SESSION["error_msg"])) {
		$_SESSION["ok_msg"] = _("Rule has been created successfully.");
		unset($v_port);
		unset($v_ip);
		unset($v_comment);
	}
}

if (empty($v_action)) {
	$v_action = "";
}
if (empty($v_protocol)) {
	$v_protocol = "";
}
if (empty($v_port)) {
	$v_port = "";
}
if (empty($v_ip)) {
	$v_ip = "";
}
if (empty($v_comment)) {
	$v_comment = "";
}

// Render
render_page($user, $TAB, "add_firewall");

// Flush session messages
unset($_SESSION["error_msg"]);
unset($_SESSION["ok_msg"]);
