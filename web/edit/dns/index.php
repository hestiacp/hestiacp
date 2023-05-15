<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
$TAB = "DNS";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check domain name
if (empty($_GET["domain"])) {
	header("Location: /list/dns/");
	exit();
}

// Edit as someone else?
if ($_SESSION["userContext"] === "admin" && !empty($_GET["user"])) {
	$user = quoteshellarg($_GET["user"]);
	$user_plain = htmlentities($_GET["user"]);
}

// List ip addresses
exec(HESTIA_CMD . "v-list-user-ips " . $user . " json", $output, $return_var);
$v_ips = json_decode(implode("", $output), true);
unset($output);

// List dns domain
if (!empty($_GET["domain"]) && empty($_GET["record_id"])) {
	$v_domain = quoteshellarg($_GET["domain"]);
	exec(
		HESTIA_CMD . "v-list-dns-domain " . $user . " " . $v_domain . " json",
		$output,
		$return_var,
	);
	check_return_code_redirect($return_var, $output, "/list/dns/");
	$data = json_decode(implode("", $output), true);
	unset($output);

	// Parse dns domain
	$v_username = $user;
	$v_domain = $_GET["domain"];
	$v_ip = $data[$v_domain]["IP"];
	$v_template = $data[$v_domain]["TPL"];
	$v_ttl = $data[$v_domain]["TTL"];
	$v_dnssec = $data[$v_domain]["DNSSEC"];
	$v_exp = $data[$v_domain]["EXP"];
	$v_soa = $data[$v_domain]["SOA"];
	$v_date = $data[$v_domain]["DATE"];
	$v_time = $data[$v_domain]["TIME"];
	$v_suspended = $data[$v_domain]["SUSPENDED"];
	if ($v_suspended == "yes") {
		$v_status = "suspended";
	} else {
		$v_status = "active";
	}

	// List dns templates
	exec(HESTIA_CMD . "v-list-dns-templates json", $output, $return_var);
	$templates = json_decode(implode("", $output), true);
	unset($output);
}

// List dns record
if (!empty($_GET["domain"]) && !empty($_GET["record_id"])) {
	$v_domain = quoteshellarg($_GET["domain"]);
	$v_record_id = quoteshellarg($_GET["record_id"]);
	exec(
		HESTIA_CMD . "v-list-dns-records " . $user . " " . $v_domain . " 'json'",
		$output,
		$return_var,
	);
	check_return_code_redirect($return_var, $output, "/list/dns/");
	$data = json_decode(implode("", $output), true);
	unset($output);
	// Parse dns record
	$v_username = $user;
	$v_domain = $_GET["domain"];
	$v_record_id = $_GET["record_id"];
	$v_rec = $data[$v_record_id]["RECORD"];
	$v_type = $data[$v_record_id]["TYPE"];
	$v_val = $data[$v_record_id]["VALUE"];
	$v_priority = $data[$v_record_id]["PRIORITY"];
	$v_suspended = $data[$v_record_id]["SUSPENDED"];
	if ($v_suspended == "yes") {
		$v_status = "suspended";
	} else {
		$v_status = "active";
	}
	$v_date = $data[$v_record_id]["DATE"];
	$v_time = $data[$v_record_id]["TIME"];
	$v_ttl = $data[$v_record_id]["TTL"];
}

// Check POST request for dns domain
if (!empty($_POST["save"]) && !empty($_GET["domain"]) && empty($_GET["record_id"])) {
	$v_domain = quoteshellarg($_POST["v_domain"]);

	// Check token
	verify_csrf($_POST);

	// Change domain IP
	if ($v_ip != $_POST["v_ip"] && empty($_SESSION["error_msg"])) {
		$v_ip = quoteshellarg($_POST["v_ip"]);
		exec(
			HESTIA_CMD .
				"v-change-dns-domain-ip " .
				$user .
				" " .
				$v_domain .
				" " .
				$v_ip .
				" 'no'",
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		$restart_dns = "yes";
		unset($output);
	}

	// Change domain template
	if ($v_template != $_POST["v_template"] && empty($_SESSION["error_msg"])) {
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
		$restart_dns = "yes";
	}

	// Change SOA record
	if ($v_soa != $_POST["v_soa"] && empty($_SESSION["error_msg"])) {
		$v_soa = quoteshellarg($_POST["v_soa"]);
		exec(
			HESTIA_CMD .
				"v-change-dns-domain-soa " .
				$user .
				" " .
				$v_domain .
				" " .
				$v_soa .
				" 'no'",
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
		$restart_dns = "yes";
	}

	// Change expiration date
	if ($v_exp != $_POST["v_exp"] && empty($_SESSION["error_msg"])) {
		$v_exp = quoteshellarg($_POST["v_exp"]);
		exec(
			HESTIA_CMD .
				"v-change-dns-domain-exp " .
				$user .
				" " .
				$v_domain .
				" " .
				$v_exp .
				" 'no'",
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
	}

	// Change domain ttl
	if ($v_ttl != $_POST["v_ttl"] && empty($_SESSION["error_msg"])) {
		$v_ttl = quoteshellarg($_POST["v_ttl"]);
		exec(
			HESTIA_CMD .
				"v-change-dns-domain-ttl " .
				$user .
				" " .
				$v_domain .
				" " .
				$v_ttl .
				" 'no'",
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
		$restart_dns = "yes";
	}
	// Change domain dnssec
	if ($_POST["v_dnssec"] == "" && $v_dnssec == "yes" && empty($_SESSION["error_msg"])) {
		exec(
			HESTIA_CMD . "v-change-dns-domain-dnssec " . $user . " " . $v_domain . " 'no'",
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
		$v_dnssec = "no";
		$restart_dns = "yes";
	}

	// Change domain dnssec
	if ($_POST["v_dnssec"] == "yes" && $v_dnssec !== "yes" && empty($_SESSION["error_msg"])) {
		exec(
			HESTIA_CMD . "v-change-dns-domain-dnssec " . $user . " " . $v_domain . " 'yes'",
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
		$v_dnssec = "yes";
		$restart_dns = "yes";
	}

	// Restart dns server
	if (!empty($restart_dns) && empty($_SESSION["error_msg"])) {
		exec(HESTIA_CMD . "v-restart-dns", $output, $return_var);
		check_return_code($return_var, $output);
		unset($output);
	}

	// Set success message
	if (empty($_SESSION["error_msg"])) {
		$_SESSION["ok_msg"] = _("Changes have been saved.");
	}
	// Restart dns server
	if (empty($_SESSION["error_msg"])) {
		exec(HESTIA_CMD . "v-restart-dns", $output, $return_var);
		check_return_code($return_var, $output);
		unset($output);
	}
}

// Check POST request for dns record
if (!empty($_POST["save"]) && !empty($_GET["domain"]) && !empty($_GET["record_id"])) {
	// Check token
	verify_csrf($_POST);

	// Protect input
	$v_domain = quoteshellarg($_POST["v_domain"]);
	$v_record_id = quoteshellarg($_POST["v_record_id"]);

	// Change dns record
	if (
		$v_rec != $_POST["v_rec"] ||
		$v_type != $_POST["v_type"] ||
		$v_val != $_POST["v_val"] ||
		$v_priority != $_POST["v_priority"] ||
		($v_ttl != $_POST["v_ttl"] && empty($_SESSION["error_msg"]))
	) {
		$v_rec = quoteshellarg($_POST["v_rec"]);
		$v_type = quoteshellarg($_POST["v_type"]);
		$v_val = quoteshellarg($_POST["v_val"]);
		$v_priority = quoteshellarg($_POST["v_priority"]);
		$v_ttl = quoteshellarg($_POST["v_ttl"]);
		exec(
			HESTIA_CMD .
				"v-change-dns-record " .
				$user .
				" " .
				$v_domain .
				" " .
				$v_record_id .
				" " .
				$v_rec .
				" " .
				$v_type .
				" " .
				$v_val .
				" " .
				$v_priority .
				" yes " .
				$v_ttl,
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		$v_rec = $_POST["v_rec"];
		$v_type = $_POST["v_type"];
		$v_val = $_POST["v_val"];
		unset($output);
		$restart_dns = "yes";
	}

	// Change dns record id
	if ($_GET["record_id"] != $_POST["v_record_id"] && empty($_SESSION["error_msg"])) {
		$v_old_record_id = quoteshellarg($_GET["record_id"]);
		exec(
			HESTIA_CMD .
				"v-change-dns-record-id " .
				$user .
				" " .
				$v_domain .
				" " .
				$v_old_record_id .
				" " .
				$v_record_id,
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
		$restart_dns = "yes";
	}

	// Restart dns server
	if (!empty($restart_dns) && empty($_SESSION["error_msg"])) {
		exec(HESTIA_CMD . "v-restart-dns", $output, $return_var);
		check_return_code($return_var, $output);
		unset($output);
	}

	// Set success message
	if (empty($_SESSION["error_msg"])) {
		$_SESSION["ok_msg"] = _("Changes have been saved.");
	}

	// Change url if record id was changed
	if (empty($_SESSION["error_msg"]) && $_GET["record_id"] != $_POST["v_record_id"]) {
		header(
			"Location: /edit/dns/?domain=" .
				$_GET["domain"] .
				"&record_id=" .
				$_POST["v_record_id"],
		);
		exit();
	}
}

// Render page
if (empty($_GET["record_id"])) {
	// Display body for dns domain
	render_page($user, $TAB, "edit_dns");
} else {
	if (empty($data[$_GET["record_id"]])) {
		header("Location: /list/dns/");
		$_SESSION["error_msg"] = _("Error: unknown record ID.");
	}
	// Display body for dns record
	render_page($user, $TAB, "edit_dns_rec");
}

// Flush session messages
unset($_SESSION["error_msg"]);
unset($_SESSION["ok_msg"]);
