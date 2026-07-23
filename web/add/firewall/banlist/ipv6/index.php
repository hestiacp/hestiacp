<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
$TAB = "FIREWALL";

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

if ($_SESSION["userContext"] != "admin") {
	header("Location: /list/user");
	exit();
}

if (!empty($_POST["ok"])) {
	verify_csrf($_POST);

	if (empty($_POST["v_chain"])) {
		$errors[] = _("Banlist");
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

	$v_chain = quoteshellarg($_POST["v_chain"]);
	$v_ip = quoteshellarg($_POST["v_ip"]);

	if (empty($_SESSION["error_msg"])) {
		exec(
			HESTIA_CMD . "v-add-firewall-ban-ipv6 " . $v_ip . " " . $v_chain,
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
	}

	if (empty($_SESSION["error_msg"])) {
		$_SESSION["ok_msg"] = _("IPv6 address has been banned successfully.");
		unset($v_chain, $v_ip);
	}
}

$v_ip = $v_ip ?? "";
$v_chain = $v_chain ?? "";

render_page($user, $TAB, "add_firewall_banlist_ipv6");

unset($_SESSION["error_msg"]);
unset($_SESSION["ok_msg"]);
