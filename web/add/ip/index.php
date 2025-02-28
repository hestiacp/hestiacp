<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
$TAB = "IP";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check user
if ($_SESSION["userContext"] != "admin") {
	header("Location: /list/user");
	exit();
}

// Check POST request
if (!empty($_POST["ok"])) {
	/// Check token
	verify_csrf($_POST);

	// Check empty fields
	if (empty($_POST["v_ip"])) {
		$errors[] = _("IP Address");
	}
	if (empty($_POST["v_netmask"])) {
		$errors[] = _("Netmask");
	}
	if (empty($_POST["v_interface"])) {
		$errors[] = _("Interface");
	}
	if (empty($_POST["v_owner"])) {
		$errors[] = _("Assigned User");
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
	$v_ip = quoteshellarg($_POST["v_ip"]);
	$v_netmask = quoteshellarg($_POST["v_netmask"]);
	$v_name = quoteshellarg($_POST["v_name"]);
	$v_nat = quoteshellarg($_POST["v_nat"]);
	$v_interface = quoteshellarg($_POST["v_interface"]);
	$v_owner = quoteshellarg($_POST["v_owner"]);
	$v_shared = $_POST["v_shared"];

	// Check shared checkmark
	if ($v_shared == "on") {
		$ip_status = "shared";
	} else {
		$ip_status = "dedicated";
		$v_dedicated = "yes";
	}

	// Add IP
	if (empty($_SESSION["error_msg"])) {
		exec(
			HESTIA_CMD .
				"v-add-sys-ip " .
				$v_ip .
				" " .
				$v_netmask .
				" " .
				$v_interface .
				" " .
				$v_owner .
				" " .
				quoteshellarg($ip_status) .
				" " .
				$v_name .
				" " .
				$v_nat,
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
		$v_owner = $_POST["v_owner"];
		$v_interface = $_POST["v_interface"];
	}

	// Flush field values on success
	if (empty($_SESSION["error_msg"])) {
		$_SESSION["ok_msg"] = htmlify_trans(
			sprintf(
				_("IP address {%s} has been created successfully."),
				htmlentities($_POST["v_ip"]),
			),
			"</a>",
			'<a href="/edit/ip/?ip=' . htmlentities($_POST["v_ip"]) . '">',
		);
		unset($v_ip);
		unset($v_netmask);
		unset($v_name);
		unset($v_nat);
	}
}

// List network interfaces
exec(HESTIA_CMD . "v-list-sys-interfaces 'json'", $output, $return_var);
$interfaces = json_decode(implode("", $output), true);
unset($output);

// List users
exec(HESTIA_CMD . "v-list-sys-users 'json'", $output, $return_var);
$users = json_decode(implode("", $output), true);
unset($output);

if (empty($v_ip)) {
	$v_ip = "";
}
if (empty($v_netmask)) {
	$v_netmask = "";
}
if (empty($v_name)) {
	$v_name = "";
}
if (empty($v_nat)) {
	$v_nat = "";
}
if (empty($v_interface)) {
	$v_interface = "";
}
if (empty($ip_status)) {
	$ip_status = "";
}
if (empty($v_owner)) {
	$v_owner = "";
}
// Render
render_page($user, $TAB, "add_ip");

// Flush session messages
unset($_SESSION["error_msg"]);
unset($_SESSION["ok_msg"]);
