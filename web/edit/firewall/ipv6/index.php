<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
$TAB = "FIREWALL";

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

if ($_SESSION["userContext"] != "admin") {
	header("Location: /list/user");
	exit();
}

if (empty($_GET["rule"])) {
	header("Location: /list/firewall/ipv6/");
	exit();
}

// Get rule data
exec(HESTIA_CMD . "v-list-firewall-ipv6 json", $output, $return_var);
check_return_code_redirect($return_var, $output, "/list/firewall/ipv6");
$all_data = json_decode(implode("", $output), true);
unset($output);

$rule_id = $_GET["rule"];
$rule_data = $all_data[$rule_id] ?? null;

if (!$rule_data) {
	header("Location: /list/firewall/ipv6/");
	exit();
}

$v_rule = $rule_id;
$v_action = $rule_data["ACTION"];
$v_protocol = $rule_data["PROTOCOL"];
$v_port = $rule_data["PORT"];
$v_ip = $rule_data["IP6"];
$v_comment = $rule_data["COMMENT"];
$v_suspended = $rule_data["SUSPENDED"];
$v_status = $v_suspended == "yes" ? "suspended" : "active";

// Get IPv6 ipset lists
exec(HESTIA_CMD . "v-list-firewall-ipset-ipv6 'json'", $output, $return_var);
$ipset_data = json_decode(implode("", $output), true) ?? [];
unset($output);

$ipset_lists = [];
foreach ($ipset_data as $key => $value) {
	if (isset($value["SUSPENDED"]) && $value["SUSPENDED"] === "yes") {
		continue;
	}
	$ipset_lists[] = ["name" => $key];
}
$ipset_lists_json = json_encode($ipset_lists);

if (!empty($_POST["save"])) {
	verify_csrf($_POST);

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
		$errors[] = _("IPv6 Address");
	}

	if (!empty($errors[0])) {
		foreach ($errors as $i => $error) {
			$error_msg = $i == 0 ? $error : $error_msg . ", " . $error;
		}
		$_SESSION["error_msg"] = sprintf(_('Field "%s" can not be blank.'), $error_msg);
	}

	if (empty($_SESSION["error_msg"])) {
		$v_rule_q = quoteshellarg($_GET["rule"]);
		$v_action_q = quoteshellarg($_POST["v_action"]);
		$v_proto_q = quoteshellarg($_POST["v_protocol"]);
		$v_port_q = quoteshellarg(
			trim(preg_replace("/\,+/", ",", str_replace(" ", ",", $_POST["v_port"])), ","),
		);
		$v_ip_q = quoteshellarg($_POST["v_ip"]);
		$v_comment_q = quoteshellarg($_POST["v_comment"]);

		exec(
			HESTIA_CMD .
				"v-change-firewall-ipv6-rule $v_rule_q $v_action_q $v_ip_q $v_port_q $v_proto_q $v_comment_q",
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);

		$v_action = $_POST["v_action"];
		$v_protocol = $_POST["v_protocol"];
		$v_port = trim(preg_replace("/\,+/", ",", str_replace(" ", ",", $_POST["v_port"])), ",");
		$v_ip = $_POST["v_ip"];
		$v_comment = $_POST["v_comment"];

		if (empty($_SESSION["error_msg"])) {
			$_SESSION["ok_msg"] = _("Changes have been saved.");
		}
	}
}

render_page($user, $TAB, "edit_firewall_ipv6");

unset($_SESSION["error_msg"]);
unset($_SESSION["ok_msg"]);
