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

// Check ip argument
if (empty($_GET["rule"])) {
	header("Location: /list/firewall/");
	exit();
}

// List rule
$v_rule = quoteshellarg($_GET["rule"]);
exec(HESTIA_CMD . "v-list-firewall-rule " . $v_rule . " 'json'", $output, $return_var);
check_return_code_redirect($return_var, $output, "/list/firewall");
$data = json_decode(implode("", $output), true);
unset($output);

// Parse rule
$v_rule = $_GET["rule"];
$v_action = $data[$v_rule]["ACTION"];
$v_protocol = $data[$v_rule]["PROTOCOL"];
$v_port = $data[$v_rule]["PORT"];
$v_ip = $data[$v_rule]["IP"];
$v_comment = $data[$v_rule]["COMMENT"];
$v_date = $data[$v_rule]["DATE"];
$v_time = $data[$v_rule]["TIME"];
$v_suspended = $data[$v_rule]["SUSPENDED"];
if ($v_suspended == "yes") {
	$v_status = "suspended";
} else {
	$v_status = "active";
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
if (!empty($_POST["save"])) {
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
	if (empty($_SESSION["error_msg"])) {
		$v_rule = quoteshellarg($_GET["rule"]);
		$v_action = quoteshellarg($_POST["v_action"]);
		$v_protocol = quoteshellarg($_POST["v_protocol"]);
		$v_port = str_replace(" ", ",", $_POST["v_port"]);
		$v_port = preg_replace("/\,+/", ",", $v_port);
		$v_port = trim($v_port, ",");
		$v_port = quoteshellarg($v_port);
		$v_ip = quoteshellarg($_POST["v_ip"]);
		$v_comment = quoteshellarg($_POST["v_comment"]);

		// Change Status
		exec(
			HESTIA_CMD .
				"v-change-firewall-rule " .
				$v_rule .
				" " .
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

		$v_rule = $_GET["v_rule"];
		$v_action = $_POST["v_action"];
		$v_protocol = $_POST["v_protocol"];
		$v_port = str_replace(" ", ",", $_POST["v_port"]);
		$v_port = preg_replace("/\,+/", ",", $v_port);
		$v_port = trim($v_port, ",");
		$v_ip = $_POST["v_ip"];
		$v_comment = $_POST["v_comment"];

		// Set success message
		if (empty($_SESSION["error_msg"])) {
			$_SESSION["ok_msg"] = _("Changes have been saved.");
		}
	} else {
		$v_rule = $_GET["v_rule"];
		$v_action = $_POST["v_action"];
		$v_protocol = $_POST["v_protocol"];
		$v_port = str_replace(" ", ",", $_POST["v_port"]);
		$v_port = preg_replace("/\,+/", ",", $v_port);
		$v_port = trim($v_port, ",");
		$v_ip = $_POST["v_ip"];
		$v_comment = $_POST["v_comment"];
	}
}

// Render page
render_page($user, $TAB, "edit_firewall");

// Flush session messages
unset($_SESSION["error_msg"]);
unset($_SESSION["ok_msg"]);
