<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
$TAB = "DNS";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// List ip addresses
exec(HESTIA_CMD . "v-list-user-ips " . $user . " json", $output, $return_var);
$v_ips = json_decode(implode("", $output), true);
unset($output);

// Check POST request for dns domain
if (!empty($_POST["ok"])) {
	// Check token
	verify_csrf($_POST);

	// Check empty fields
	if (empty($_POST["v_domain"])) {
		$errors[] = _("Domain");
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
	$v_domain = preg_replace("/^www./i", "", $_POST["v_domain"]);
	$v_domain = quoteshellarg($v_domain);
	$v_domain = strtolower($v_domain);
	$v_ip = $_POST["v_ip"];
	// Change NameServers
	if (empty($_POST["v_ns1"])) {
		$_POST["v_ns1"] = "";
	}
	if (empty($_POST["v_ns2"])) {
		$_POST["v_ns2"] = "";
	}
	if (empty($_POST["v_ns3"])) {
		$_POST["v_ns3"] = "";
	}
	if (empty($_POST["v_ns4"])) {
		$_POST["v_ns4"] = "";
	}
	if (empty($_POST["v_ns5"])) {
		$_POST["v_ns5"] = "";
	}
	if (empty($_POST["v_ns6"])) {
		$_POST["v_ns6"] = "";
	}
	if (empty($_POST["v_ns7"])) {
		$_POST["v_ns7"] = "";
	}
	if (empty($_POST["v_ns8"])) {
		$_POST["v_ns8"] = "";
	}
	if (empty($_POST["v_dnssec"])) {
		$_POST["v_dnssec"] = "no";
	}
	$v_ns1 = quoteshellarg($_POST["v_ns1"]);
	$v_ns2 = quoteshellarg($_POST["v_ns2"]);
	$v_ns3 = quoteshellarg($_POST["v_ns3"]);
	$v_ns4 = quoteshellarg($_POST["v_ns4"]);
	$v_ns5 = quoteshellarg($_POST["v_ns5"]);
	$v_ns6 = quoteshellarg($_POST["v_ns6"]);
	$v_ns7 = quoteshellarg($_POST["v_ns7"]);
	$v_ns8 = quoteshellarg($_POST["v_ns8"]);
	$v_dnssec = quoteshellarg($_POST["v_dnssec"]);

	// Add dns domain
	if (empty($_SESSION["error_msg"])) {
		exec(
			HESTIA_CMD .
				"v-add-dns-domain " .
				$user .
				" " .
				$v_domain .
				" " .
				quoteshellarg($v_ip) .
				" " .
				$v_ns1 .
				" " .
				$v_ns2 .
				" " .
				$v_ns3 .
				" " .
				$v_ns4 .
				" " .
				$v_ns5 .
				" " .
				$v_ns6 .
				" " .
				$v_ns7 .
				" " .
				$v_ns8 .
				" no " .
				$v_dnssec,
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
	}
	exec(HESTIA_CMD . "v-list-user " . $user . " json", $output, $return_var);
	$user_config = json_decode(implode("", $output), true);
	unset($output);
	$v_template = $user_config[$user_plain]["DNS_TEMPLATE"];

	if (
		$v_template != $_POST["v_template"] &&
		!empty($_POST["v_template"]) &&
		empty($_SESSION["error_msg"])
	) {
		$v_template = quoteshellarg($_POST["v_template"]);
		exec(
			HESTIA_CMD .
				"v-change-dns-domain-tpl " .
				$user .
				" " .
				$v_domain .
				" " .
				$v_template .
				" 'no'",
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
	}

	// Set expiration date
	if (empty($_SESSION["error_msg"])) {
		if (!empty($_POST["v_exp"]) && $_POST["v_exp"] != date("Y-m-d", strtotime("+1 year"))) {
			$v_exp = quoteshellarg($_POST["v_exp"]);
			exec(
				HESTIA_CMD .
					"v-change-dns-domain-exp " .
					$user .
					" " .
					$v_domain .
					" " .
					$v_exp .
					" no",
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
		}
	}

	// Set ttl
	if (empty($_SESSION["error_msg"])) {
		if (
			!empty($_POST["v_ttl"]) &&
			$_POST["v_ttl"] != "14400" &&
			empty($_SESSION["error_msg"])
		) {
			$v_ttl = quoteshellarg($_POST["v_ttl"]);
			exec(
				HESTIA_CMD .
					"v-change-dns-domain-ttl " .
					$user .
					" " .
					$v_domain .
					" " .
					$v_ttl .
					" no",
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
		}
	}

	// Restart dns server
	if (empty($_SESSION["error_msg"])) {
		exec(HESTIA_CMD . "v-restart-dns", $output, $return_var);
		check_return_code($return_var, $output);
		unset($output);
	}

	// Flush field values on success
	if (empty($_SESSION["error_msg"])) {
		$_SESSION["ok_msg"] = htmlify_trans(
			sprintf(
				_("DNS zone {%s} has been created successfully."),
				htmlentities($_POST["v_domain"]),
			),
			"</a>",
			'<a href="/edit/dns/?domain=' . htmlentities($_POST["v_domain"]) . '">',
		);

		unset($v_domain);
	}
}

// Check POST request for dns record
if (!empty($_POST["ok_rec"])) {
	// Check token
	if (!isset($_POST["token"]) || $_SESSION["token"] != $_POST["token"]) {
		header("location: /login/");
		exit();
	}

	// Check empty fields
	if (empty($_POST["v_domain"])) {
		$errors[] = _("Domain");
	}
	if (empty($_POST["v_rec"])) {
		$errors[] = _("Record");
	}
	if (empty($_POST["v_type"])) {
		$errors[] = _("Type");
	}
	if (empty($_POST["v_val"])) {
		$errors[] = _("IP or Value");
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
	$v_domain = quoteshellarg($_POST["v_domain"]);
	$v_rec = quoteshellarg($_POST["v_rec"]);
	$v_type = quoteshellarg($_POST["v_type"]);
	$v_val = quoteshellarg($_POST["v_val"]);
	$v_priority = quoteshellarg($_POST["v_priority"]);
	$v_ttl = quoteshellarg($_POST["v_ttl"]);

	// Add dns record
	if (empty($_SESSION["error_msg"])) {
		exec(
			HESTIA_CMD .
				"v-add-dns-record " .
				$user .
				" " .
				$v_domain .
				" " .
				$v_rec .
				" " .
				$v_type .
				" " .
				$v_val .
				" " .
				$v_priority .
				" '' yes " .
				$v_ttl,
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
	}
	$v_type = $_POST["v_type"];

	// Flush field values on success
	if (empty($_SESSION["error_msg"])) {
		$_SESSION["ok_msg"] = htmlify_trans(
			sprintf(
				_("Record {%s.%s} has been created successfully."),
				htmlentities($_POST["v_rec"]),
				htmlentities($_POST["v_domain"]),
			),
			"</code>",
			"<code>",
		);
		unset($v_domain);
		unset($v_rec);
		unset($v_val);
		unset($v_priority);
		unset($v_dnssec);
	}
}

if (empty($v_ns1)) {
	$v_ns1 = "";
}
if (empty($v_ns2)) {
	$v_ns2 = "";
}
if (empty($v_ns3)) {
	$v_ns3 = "";
}
if (empty($v_ns4)) {
	$v_ns4 = "";
}
if (empty($v_ns5)) {
	$v_ns5 = "";
}
if (empty($v_ns6)) {
	$v_ns6 = "";
}
if (empty($v_ns7)) {
	$v_ns7 = "";
}
if (empty($v_ns8)) {
	$v_ns8 = "";
}

$v_ns1 = str_replace("'", "", $v_ns1);
$v_ns2 = str_replace("'", "", $v_ns2);
$v_ns3 = str_replace("'", "", $v_ns3);
$v_ns4 = str_replace("'", "", $v_ns4);
$v_ns5 = str_replace("'", "", $v_ns5);
$v_ns6 = str_replace("'", "", $v_ns6);
$v_ns7 = str_replace("'", "", $v_ns7);
$v_ns8 = str_replace("'", "", $v_ns8);

if (empty($v_ip) && count($v_ips) > 0) {
	$ip = array_key_first($v_ips);
	$v_ip = empty($v_ips[$ip]["NAT"]) ? $ip : $v_ips[$ip]["NAT"];
}

// List dns templates
exec(HESTIA_CMD . "v-list-dns-templates json", $output, $return_var);
$templates = json_decode(implode("", $output), true);
unset($output);

exec(HESTIA_CMD . "v-list-user " . $user . " json", $output, $return_var);
$user_config = json_decode(implode("", $output), true);
unset($output);
$v_template = $user_config[$user_plain]["DNS_TEMPLATE"];

if (empty($_GET["domain"])) {
	// Display body for dns domain
	if (empty($v_domain)) {
		$v_domain = "";
	}
	if (empty($v_ttl)) {
		$v_ttl = 14400;
	}
	if (empty($v_exp)) {
		$v_exp = date("Y-m-d", strtotime("+1 year"));
	}
	if (empty($v_dnssec)) {
		$v_dnssec = "";
	}
	if (empty($v_ns1)) {
		exec(HESTIA_CMD . "v-list-user-ns " . $user . " json", $output, $return_var);
		$nameservers = json_decode(implode("", $output), true);
		for ($i = 0; $i < 8; $i++) {
			if (empty($nameservers[$i])) {
				$nameservers[$i] = "";
			}
		}
		$v_ns1 = str_replace("'", "", $nameservers[0]);
		$v_ns2 = str_replace("'", "", $nameservers[1]);
		$v_ns3 = str_replace("'", "", $nameservers[2]);
		$v_ns4 = str_replace("'", "", $nameservers[3]);
		$v_ns5 = str_replace("'", "", $nameservers[4]);
		$v_ns6 = str_replace("'", "", $nameservers[5]);
		$v_ns7 = str_replace("'", "", $nameservers[6]);
		$v_ns8 = str_replace("'", "", $nameservers[7]);
		unset($output);
	}
	$accept = $_GET["accept"] ?? "";

	render_page($user, $TAB, "add_dns");
} else {
	// Display body for dns record
	$v_domain = $_GET["domain"];
	if (empty($v_rec)) {
		$v_rec = "@";
	}
	if (empty($v_type)) {
		$v_type = "";
	}
	if (empty($v_val)) {
		$v_val = "";
	}
	if (empty($v_priority)) {
		$v_priority = "";
	}
	if (empty($v_ttl)) {
		$v_ttl = "";
	}
	if (empty($v_dnssec)) {
		$v_dnssec = "";
	}
	$accept = $_GET["accept"] ?? "";
	render_page($user, $TAB, "add_dns_rec");
}

// Flush session messages
unset($_SESSION["error_msg"]);
unset($_SESSION["ok_msg"]);
