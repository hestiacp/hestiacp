<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
$TAB = "MAIL";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check domain argument
if (empty($_GET["domain"])) {
	header("Location: /list/mail/");
	exit();
}

// Edit as someone else?
if ($_SESSION["userContext"] === "admin" && !empty($_GET["user"])) {
	$user = quoteshellarg($_GET["user"]);
	$user_plain = htmlentities($_GET["user"]);
}

$v_username = $user;

// List mail domain
if (!empty($_GET["domain"]) && empty($_GET["account"])) {
	$v_domain = $_GET["domain"];

	exec(HESTIA_CMD . "v-list-sys-webmail json", $output, $return_var);
	$webmail_clients = json_decode(implode("", $output), true);
	unset($output);

	exec(
		HESTIA_CMD . "v-list-mail-domain " . $user . " " . quoteshellarg($v_domain) . " json",
		$output,
		$return_var,
	);
	$data = json_decode(implode("", $output), true);
	check_return_code_redirect($return_var, $output, "/list/mail/");
	unset($output);

	// Parse domain
	$v_antispam = $data[$v_domain]["ANTISPAM"];
	$v_reject = $data[$v_domain]["REJECT"];
	$v_antivirus = $data[$v_domain]["ANTIVIRUS"];
	$v_dkim = $data[$v_domain]["DKIM"];
	$v_catchall = $data[$v_domain]["CATCHALL"];
	$v_rate = $data[$v_domain]["RATE_LIMIT"];
	$v_date = $data[$v_domain]["DATE"];
	$v_time = $data[$v_domain]["TIME"];
	$v_suspended = $data[$v_domain]["SUSPENDED"];
	$v_webmail_alias = $data[$v_domain]["WEBMAIL_ALIAS"];
	$v_webmail = $data[$v_domain]["WEBMAIL"];
	$v_smtp_relay = $data[$v_domain]["U_SMTP_RELAY"];
	$v_smtp_relay_host = $data[$v_domain]["U_SMTP_RELAY_HOST"];
	$v_smtp_relay_port = $data[$v_domain]["U_SMTP_RELAY_PORT"];
	$v_smtp_relay_user = $data[$v_domain]["U_SMTP_RELAY_USERNAME"];

	if ($v_suspended == "yes") {
		$v_status = "suspended";
	} else {
		$v_status = "active";
	}

	$v_ssl = $data[$v_domain]["SSL"];
	if (!empty($v_ssl)) {
		exec(
			HESTIA_CMD .
				"v-list-mail-domain-ssl " .
				$user .
				" " .
				quoteshellarg($v_domain) .
				" json",
			$output,
			$return_var,
		);
		$ssl_str = json_decode(implode("", $output), true);
		unset($output);
		$v_ssl_crt = $ssl_str[$v_domain]["CRT"];
		$v_ssl_key = $ssl_str[$v_domain]["KEY"];
		$v_ssl_ca = $ssl_str[$v_domain]["CA"];
		$v_ssl_subject = $ssl_str[$v_domain]["SUBJECT"];
		$v_ssl_aliases = $ssl_str[$v_domain]["ALIASES"];
		$v_ssl_not_before = $ssl_str[$v_domain]["NOT_BEFORE"];
		$v_ssl_not_after = $ssl_str[$v_domain]["NOT_AFTER"];
		$v_ssl_signature = $ssl_str[$v_domain]["SIGNATURE"];
		$v_ssl_pub_key = $ssl_str[$v_domain]["PUB_KEY"];
		$v_ssl_issuer = $ssl_str[$v_domain]["ISSUER"];
	}
	$v_letsencrypt = $data[$v_domain]["LETSENCRYPT"];
	if (empty($v_letsencrypt)) {
		$v_letsencrypt = "no";
	}
}

// List mail account
if (!empty($_GET["domain"]) && !empty($_GET["account"])) {
	$v_domain = $_GET["domain"];

	$v_account = $_GET["account"];
	exec(
		HESTIA_CMD .
			"v-list-mail-account " .
			$user .
			" " .
			quoteshellarg($v_domain) .
			" " .
			quoteshellarg($v_account) .
			" 'json'",
		$output,
		$return_var,
	);
	$data = json_decode(implode("", $output), true);
	check_return_code_redirect($return_var, $output, "/list/mail/");
	unset($output);

	// Parse mail account
	$v_username = $user;
	$v_password = "";
	$v_aliases = str_replace(",", "\n", $data[$v_account]["ALIAS"]);
	$valiases = explode(",", $data[$v_account]["ALIAS"]);
	$v_fwd = str_replace(",", "\n", $data[$v_account]["FWD"]);
	if ($v_fwd == ":blackhole:") {
		$v_fwd = "";
		$v_blackhole = "yes";
	} else {
		$v_blackhole = "no";
	}
	$vfwd = explode(",", $data[$v_account]["FWD"]);
	$v_fwd_only = $data[$v_account]["FWD_ONLY"];
	$v_rate = $data[$v_account]["RATE_LIMIT"];
	$v_quota = $data[$v_account]["QUOTA"];
	$v_autoreply = $data[$v_account]["AUTOREPLY"];
	$v_suspended = $data[$v_account]["SUSPENDED"];
	$v_webmail_alias = $data[$v_account]["WEBMAIL_ALIAS"];
	if (empty($v_send_email)) {
		$v_send_email = "";
	}
	if ($v_suspended == "yes") {
		$v_status = "suspended";
	} else {
		$v_status = "active";
	}
	$v_date = $data[$v_account]["DATE"];
	$v_time = $data[$v_account]["TIME"];

	// Parse autoreply
	if ($v_autoreply == "yes") {
		exec(
			HESTIA_CMD .
				"v-list-mail-account-autoreply " .
				$user .
				" " .
				quoteshellarg($v_domain) .
				" " .
				quoteshellarg($v_account) .
				" json",
			$output,
			$return_var,
		);
		$autoreply_str = json_decode(implode("", $output), true);
		unset($output);
		$v_autoreply_message = $autoreply_str[$v_account]["MSG"];
		$v_autoreply_message = str_replace("\\n", "\n", $v_autoreply_message);
	} else {
		$v_autoreply_message = "";
	}
}

// Check POST request for mail domain
if (!empty($_POST["save"]) && !empty($_GET["domain"]) && empty($_GET["account"])) {
	// Check token
	verify_csrf($_POST);

	exec(
		HESTIA_CMD . "v-list-mail-domain " . $user . " " . quoteshellarg($v_domain) . " json",
		$output,
		$return_var,
	);
	$data = json_decode(implode("", $output), true);
	check_return_code_redirect($return_var, $output, "/list/mail/");
	unset($output);

	// Delete antispam
	if ($v_antispam == "yes" && empty($_POST["v_antispam"]) && empty($_SESSION["error_msg"])) {
		exec(
			HESTIA_CMD .
				"v-delete-mail-domain-antispam " .
				$v_username .
				" " .
				quoteshellarg($v_domain),
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		$v_antispam = "no";
		unset($output);
	}

	// Add antispam
	if ($v_antispam == "no" && !empty($_POST["v_antispam"]) && empty($_SESSION["error_msg"])) {
		exec(
			HESTIA_CMD .
				"v-add-mail-domain-antispam " .
				$v_username .
				" " .
				quoteshellarg($v_domain),
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		$v_antispam = "yes";
		unset($output);
	}

	// Delete antivirus
	if ($v_antivirus == "yes" && empty($_POST["v_antivirus"]) && empty($_SESSION["error_msg"])) {
		exec(
			HESTIA_CMD .
				"v-delete-mail-domain-antivirus " .
				$v_username .
				" " .
				quoteshellarg($v_domain),
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		$v_antivirus = "no";
		unset($output);
	}

	// Add antivirus
	if ($v_antivirus == "no" && !empty($_POST["v_antivirus"]) && empty($_SESSION["error_msg"])) {
		exec(
			HESTIA_CMD .
				"v-add-mail-domain-antivirus " .
				$v_username .
				" " .
				quoteshellarg($v_domain),
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		$v_antivirus = "yes";
		unset($output);
	}

	// Delete DKIM
	if ($v_dkim == "yes" && empty($_POST["v_dkim"]) && empty($_SESSION["error_msg"])) {
		exec(
			HESTIA_CMD .
				"v-delete-mail-domain-dkim " .
				$v_username .
				" " .
				quoteshellarg($v_domain),
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		$v_dkim = "no";
		unset($output);
	}

	// Add DKIM
	if ($v_dkim == "no" && !empty($_POST["v_dkim"]) && empty($_SESSION["error_msg"])) {
		exec(
			HESTIA_CMD . "v-add-mail-domain-dkim " . $v_username . " " . quoteshellarg($v_domain),
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		$v_dkim = "yes";
		unset($output);
	}

	// Delete catchall
	if (!empty($v_catchall) && empty($_POST["v_catchall"]) && empty($_SESSION["error_msg"])) {
		exec(
			HESTIA_CMD .
				"v-delete-mail-domain-catchall " .
				$v_username .
				" " .
				quoteshellarg($v_domain),
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		$v_catchall = "";
		unset($output);
	}

	// Change rate limit
	if (
		$v_rate != $_POST["v_rate"] &&
		empty($_SESSION["error_msg"]) &&
		$_SESSION["userContext"] == "admin"
	) {
		if (empty($_POST["v_rate"])) {
			$v_rate = "system";
		} else {
			$v_rate = quoteshellarg($_POST["v_rate"]);
		}
		exec(
			HESTIA_CMD .
				"v-change-mail-domain-rate-limit " .
				$v_username .
				" " .
				quoteshellarg($v_domain) .
				" " .
				$v_rate,
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		if ($v_rate == "system") {
			$v_rate = "";
		}
		unset($output);
	}

	if (!empty($_POST["v_reject"]) && $v_antispam == "yes" && $v_reject != "yes") {
		exec(
			HESTIA_CMD . "v-add-mail-domain-reject " . $user . " " . $v_domain . " yes",
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		$v_reject = "yes";
		unset($output);
	}
	if (empty($_POST["v_reject"]) && $v_reject == "yes") {
		exec(
			HESTIA_CMD . "v-delete-mail-domain-reject " . $user . " " . $v_domain . " yes",
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		$v_reject = "";
		unset($output);
	}

	// Change catchall address
	if (!empty($v_catchall) && !empty($_POST["v_catchall"]) && empty($_SESSION["error_msg"])) {
		if ($v_catchall != $_POST["v_catchall"]) {
			$v_catchall = quoteshellarg($_POST["v_catchall"]);
			exec(
				HESTIA_CMD .
					"v-change-mail-domain-catchall " .
					$v_username .
					" " .
					quoteshellarg($v_domain) .
					" " .
					$v_catchall,
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
		}
	}

	// Add catchall
	if (empty($v_catchall) && !empty($_POST["v_catchall"]) && empty($_SESSION["error_msg"])) {
		$v_catchall = quoteshellarg($_POST["v_catchall"]);
		exec(
			HESTIA_CMD .
				"v-add-mail-domain-catchall " .
				$v_username .
				" " .
				quoteshellarg($v_domain) .
				" " .
				$v_catchall,
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
	}

	if (!empty($_SESSION["IMAP_SYSTEM"]) && !empty($_SESSION["WEBMAIL_SYSTEM"])) {
		if (empty($_SESSION["error_msg"])) {
			if (!empty($_POST["v_webmail"])) {
				$v_webmail = quoteshellarg($_POST["v_webmail"]);
				exec(
					HESTIA_CMD .
						"v-add-mail-domain-webmail " .
						$user .
						" " .
						$v_domain .
						" " .
						$v_webmail .
						" yes",
					$output,
					$return_var,
				);
				check_return_code($return_var, $output);
				unset($output);
			}
		}
	}

	if (!empty($_SESSION["IMAP_SYSTEM"]) && !empty($_SESSION["WEBMAIL_SYSTEM"])) {
		if (empty($_POST["v_webmail"])) {
			if (empty($_SESSION["error_msg"])) {
				exec(
					HESTIA_CMD . "v-delete-mail-domain-webmail " . $user . " " . $v_domain . " yes",
					$output,
					$return_var,
				);
				check_return_code($return_var, $output);
				$v_webmail = "";
				unset($output);
			}
		}
	}

	// Change SSL certificate
	if (
		$v_letsencrypt == "no" &&
		empty($_POST["v_letsencrypt"]) &&
		$v_ssl == "yes" &&
		!empty($_POST["v_ssl"]) &&
		empty($_SESSION["error_msg"])
	) {
		if (
			$v_ssl_crt != str_replace("\r\n", "\n", $_POST["v_ssl_crt"]) ||
			$v_ssl_key != str_replace("\r\n", "\n", $_POST["v_ssl_key"]) ||
			$v_ssl_ca != str_replace("\r\n", "\n", $_POST["v_ssl_ca"])
		) {
			exec("mktemp -d", $mktemp_output, $return_var);
			$tmpdir = $mktemp_output[0];

			// Certificate
			if (!empty($_POST["v_ssl_crt"])) {
				$fp = fopen($tmpdir . "/" . $v_domain . ".crt", "w");
				fwrite($fp, str_replace("\r\n", "\n", $_POST["v_ssl_crt"]));
				fwrite($fp, "\n");
				fclose($fp);
			}

			// Key
			if (!empty($_POST["v_ssl_key"])) {
				$fp = fopen($tmpdir . "/" . $v_domain . ".key", "w");
				fwrite($fp, str_replace("\r\n", "\n", $_POST["v_ssl_key"]));
				fwrite($fp, "\n");
				fclose($fp);
			}

			// CA
			if (!empty($_POST["v_ssl_ca"])) {
				$fp = fopen($tmpdir . "/" . $v_domain . ".ca", "w");
				fwrite($fp, str_replace("\r\n", "\n", $_POST["v_ssl_ca"]));
				fwrite($fp, "\n");
				fclose($fp);
			}

			exec(
				HESTIA_CMD .
					"v-change-mail-domain-sslcert " .
					$user .
					" " .
					quoteshellarg($v_domain) .
					" " .
					$tmpdir .
					" 'no'",
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
			$restart_web = "yes";
			$restart_proxy = "yes";

			exec(
				HESTIA_CMD .
					"v-list-mail-domain-ssl " .
					$user .
					" " .
					quoteshellarg($v_domain) .
					" json",
				$output,
				$return_var,
			);
			$ssl_str = json_decode(implode("", $output), true);
			unset($output);
			$v_ssl_crt = $ssl_str[$v_domain]["CRT"];
			$v_ssl_key = $ssl_str[$v_domain]["KEY"];
			$v_ssl_ca = $ssl_str[$v_domain]["CA"];
			$v_ssl_subject = $ssl_str[$v_domain]["SUBJECT"];
			$v_ssl_aliases = $ssl_str[$v_domain]["ALIASES"];
			$v_ssl_not_before = $ssl_str[$v_domain]["NOT_BEFORE"];
			$v_ssl_not_after = $ssl_str[$v_domain]["NOT_AFTER"];
			$v_ssl_signature = $ssl_str[$v_domain]["SIGNATURE"];
			$v_ssl_pub_key = $ssl_str[$v_domain]["PUB_KEY"];
			$v_ssl_issuer = $ssl_str[$v_domain]["ISSUER"];

			// Cleanup certificate tempfiles
			if (!empty($_POST["v_ssl_crt"])) {
				unlink($tmpdir . "/" . $v_domain . ".crt");
			}
			if (!empty($_POST["v_ssl_key"])) {
				unlink($tmpdir . "/" . $v_domain . ".key");
			}
			if (!empty($_POST["v_ssl_ca"])) {
				unlink($tmpdir . "/" . $v_domain . ".ca");
			}
			rmdir($tmpdir);
		}
	}

	// Delete Lets Encrypt support
	if (
		$v_letsencrypt == "yes" &&
		(empty($_POST["v_letsencrypt"]) || empty($_POST["v_ssl"])) &&
		empty($_SESSION["error_msg"])
	) {
		exec(
			HESTIA_CMD .
				"v-delete-letsencrypt-domain " .
				$user .
				" " .
				quoteshellarg($v_domain) .
				" '' 'yes'",
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
		$v_ssl_crt = "";
		$v_ssl_key = "";
		$v_ssl_ca = "";
		$v_letsencrypt = "no";
		$v_letsencrypt_deleted = "yes";
		$v_ssl = "no";
		$restart_mail = "yes";
	}

	// Delete SSL certificate
	if ($v_ssl == "yes" && empty($_POST["v_ssl"]) && empty($_SESSION["error_msg"])) {
		exec(
			HESTIA_CMD . "v-delete-mail-domain-ssl " . $v_username . " " . quoteshellarg($v_domain),
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
		$v_ssl_crt = "";
		$v_ssl_key = "";
		$v_ssl_ca = "";
		$v_ssl = "no";
		$restart_mail = "yes";
	}

	// Add Lets Encrypt support
	if (
		!empty($_POST["v_ssl"]) &&
		$v_letsencrypt == "no" &&
		!empty($_POST["v_letsencrypt"]) &&
		empty($_SESSION["error_msg"])
	) {
		exec(
			HESTIA_CMD .
				"v-add-letsencrypt-domain " .
				$user .
				" " .
				quoteshellarg($v_domain) .
				" ' ' 'yes'",
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
		$v_letsencrypt = "yes";
		$v_ssl = "yes";
		$restart_mail = "yes";
	}

	// Add SSL certificate
	if (
		$v_ssl == "no" &&
		!empty($_POST["v_ssl"]) &&
		empty($v_letsencrypt_deleted) &&
		empty($_SESSION["error_msg"])
	) {
		if (empty($_POST["v_ssl_crt"])) {
			$errors[] = "ssl certificate";
		}
		if (empty($_POST["v_ssl_key"])) {
			$errors[] = "ssl key";
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
		} else {
			exec("mktemp -d", $mktemp_output, $return_var);
			$tmpdir = $mktemp_output[0];

			// Certificate
			if (!empty($_POST["v_ssl_crt"])) {
				$fp = fopen($tmpdir . "/" . $v_domain . ".crt", "w");
				fwrite($fp, str_replace("\r\n", "\n", $_POST["v_ssl_crt"]));
				fclose($fp);
			}

			// Key
			if (!empty($_POST["v_ssl_key"])) {
				$fp = fopen($tmpdir . "/" . $v_domain . ".key", "w");
				fwrite($fp, str_replace("\r\n", "\n", $_POST["v_ssl_key"]));
				fclose($fp);
			}

			// CA
			if (!empty($_POST["v_ssl_ca"])) {
				$fp = fopen($tmpdir . "/" . $v_domain . ".ca", "w");
				fwrite($fp, str_replace("\r\n", "\n", $_POST["v_ssl_ca"]));
				fclose($fp);
			}
			exec(
				HESTIA_CMD .
					"v-add-mail-domain-ssl " .
					$user .
					" " .
					quoteshellarg($v_domain) .
					" " .
					$tmpdir .
					" 'no'",
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
			$v_ssl = "yes";
			$restart_web = "yes";
			$restart_proxy = "yes";

			exec(
				HESTIA_CMD .
					"v-list-mail-domain-ssl " .
					$user .
					" " .
					quoteshellarg($v_domain) .
					" json",
				$output,
				$return_var,
			);
			$ssl_str = json_decode(implode("", $output), true);
			unset($output);
			$v_ssl_crt = $ssl_str[$v_domain]["CRT"];
			$v_ssl_key = $ssl_str[$v_domain]["KEY"];
			$v_ssl_ca = $ssl_str[$v_domain]["CA"];
			$v_ssl_subject = $ssl_str[$v_domain]["SUBJECT"];
			$v_ssl_aliases = $ssl_str[$v_domain]["ALIASES"];
			$v_ssl_not_before = $ssl_str[$v_domain]["NOT_BEFORE"];
			$v_ssl_not_after = $ssl_str[$v_domain]["NOT_AFTER"];
			$v_ssl_signature = $ssl_str[$v_domain]["SIGNATURE"];
			$v_ssl_pub_key = $ssl_str[$v_domain]["PUB_KEY"];
			$v_ssl_issuer = $ssl_str[$v_domain]["ISSUER"];

			// Cleanup certificate tempfiles
			if (!empty($_POST["v_ssl_crt"])) {
				unlink($tmpdir . "/" . $v_domain . ".crt");
			}
			if (!empty($_POST["v_ssl_key"])) {
				unlink($tmpdir . "/" . $v_domain . ".key");
			}
			if (!empty($_POST["v_ssl_ca"])) {
				unlink($tmpdir . "/" . $v_domain . ".ca");
			}
			rmdir($tmpdir);
		}
	}

	// Add SMTP Relay Support
	if (empty($_SESSION["error_msg"])) {
		if (isset($_POST["v_smtp_relay"]) && !empty($_POST["v_smtp_relay_host"])) {
			if (
				$_POST["v_smtp_relay_host"] != $v_smtp_relay_host ||
				$_POST["v_smtp_relay_user"] != $v_smtp_relay_user ||
				$_POST["v_smtp_relay_port"] != $v_smtp_relay_port ||
				$_POST["v_smtp_relay_pass"] != ""
			) {
				$v_smtp_relay = true;
				$v_smtp_relay_host = quoteshellarg($_POST["v_smtp_relay_host"]);
				$v_smtp_relay_user = quoteshellarg($_POST["v_smtp_relay_user"]);
				$v_smtp_relay_pass = quoteshellarg($_POST["v_smtp_relay_pass"]);
				if (!empty($_POST["v_smtp_relay_port"])) {
					$v_smtp_relay_port = quoteshellarg($_POST["v_smtp_relay_port"]);
				} else {
					$v_smtp_relay_port = "587";
				}
				exec(
					HESTIA_CMD .
						"v-add-mail-domain-smtp-relay " .
						$v_username .
						" " .
						quoteshellarg($v_domain) .
						" " .
						$v_smtp_relay_host .
						" " .
						$v_smtp_relay_user .
						" " .
						$v_smtp_relay_pass .
						" " .
						$v_smtp_relay_port,
					$output,
					$return_var,
				);
				check_return_code($return_var, $output);
				unset($output);
			}
		}
		if (!isset($_POST["v_smtp_relay"]) && $v_smtp_relay == true) {
			$v_smtp_relay = false;
			$v_smtp_relay_host = $v_smtp_relay_user = $v_smtp_relay_pass = $v_smtp_relay_port = "";
			exec(
				HESTIA_CMD .
					"v-delete-mail-domain-smtp-relay " .
					$v_username .
					" " .
					quoteshellarg($v_domain),
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
		}
	}

	// Set success message
	if (empty($_SESSION["error_msg"])) {
		$_SESSION["ok_msg"] = _("Changes have been saved.");
	}
}

// Check POST request for mail account
if (!empty($_POST["save"]) && !empty($_GET["domain"]) && !empty($_GET["account"])) {
	// Check token
	verify_csrf($_POST);

	// Validate email
	if (!empty($_POST["v_send_email"]) && empty($_SESSION["error_msg"])) {
		if (!filter_var($_POST["v_send_email"], FILTER_VALIDATE_EMAIL)) {
			$_SESSION["error_msg"] = _("Please enter a valid email address.");
		}
	}

	$v_account = $_POST["v_account"];
	$v_send_email = $_POST["v_send_email"];

	exec(
		HESTIA_CMD .
			"v-list-mail-account " .
			$user .
			" " .
			quoteshellarg($v_domain) .
			" " .
			quoteshellarg($v_account) .
			" json",
		$output,
		$return_var,
	);
	$data = json_decode(implode("", $output), true);
	check_return_code_redirect($return_var, $output, "/list/mail/");
	unset($output);

	// Change password
	if (!empty($_POST["v_password"]) && empty($_SESSION["error_msg"])) {
		if (!validate_password($_POST["v_password"])) {
			$_SESSION["error_msg"] = _("Password does not match the minimum requirements.");
		} else {
			$v_password = tempnam("/tmp", "vst");
			$fp = fopen($v_password, "w");
			fwrite($fp, $_POST["v_password"] . "\n");
			fclose($fp);
			exec(
				HESTIA_CMD .
					"v-change-mail-account-password " .
					$v_username .
					" " .
					quoteshellarg($v_domain) .
					" " .
					quoteshellarg($v_account) .
					" " .
					$v_password,
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
			unlink($v_password);
			$v_password = quoteshellarg($_POST["v_password"]);
		}
	}

	// Change quota
	if ($v_quota != $_POST["v_quota"] && empty($_SESSION["error_msg"])) {
		if (empty($_POST["v_quota"])) {
			$v_quota = 0;
		} else {
			$v_quota = quoteshellarg($_POST["v_quota"]);
		}
		exec(
			HESTIA_CMD .
				"v-change-mail-account-quota " .
				$v_username .
				" " .
				quoteshellarg($v_domain) .
				" " .
				quoteshellarg($v_account) .
				" " .
				$v_quota,
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
	}
	// Change rate limit
	if (
		$v_rate != $_POST["v_rate"] &&
		empty($_SESSION["error_msg"]) &&
		$_SESSION["userContext"] == "admin"
	) {
		if (empty($_POST["v_rate"])) {
			$v_rate = "system";
		} else {
			$v_rate = quoteshellarg($_POST["v_rate"]);
		}
		exec(
			HESTIA_CMD .
				"v-change-mail-account-rate-limit " .
				$v_username .
				" " .
				quoteshellarg($v_domain) .
				" " .
				quoteshellarg($v_account) .
				" " .
				$v_rate,
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		if ($v_rate == "system") {
			$v_rate = "";
		}
		unset($output);
	}

	// Change account aliases
	if (empty($_SESSION["error_msg"])) {
		$waliases = preg_replace("/\n/", " ", $_POST["v_aliases"]);
		$waliases = preg_replace("/,/", " ", $waliases);
		$waliases = preg_replace("/\s+/", " ", $waliases);
		$waliases = trim($waliases);
		$aliases = explode(" ", $waliases);
		$v_aliases = str_replace(" ", "\n", $waliases);
		$result = array_diff($valiases, $aliases);
		foreach ($result as $alias) {
			if (empty($_SESSION["error_msg"]) && !empty($alias)) {
				exec(
					HESTIA_CMD .
						"v-delete-mail-account-alias " .
						$v_username .
						" " .
						quoteshellarg($v_domain) .
						" " .
						quoteshellarg($v_account) .
						" " .
						quoteshellarg($alias),
					$output,
					$return_var,
				);
				check_return_code($return_var, $output);
				unset($output);
			}
		}
		$result = array_diff($aliases, $valiases);
		foreach ($result as $alias) {
			if (empty($_SESSION["error_msg"]) && !empty($alias)) {
				exec(
					HESTIA_CMD .
						"v-add-mail-account-alias " .
						$v_username .
						" " .
						quoteshellarg($v_domain) .
						" " .
						quoteshellarg($v_account) .
						" " .
						quoteshellarg($alias),
					$output,
					$return_var,
				);
				check_return_code($return_var, $output);
				unset($output);
			}
		}
	}
	// Change forwarders to :blackhole:
	if (empty($_SESSION["error_msg"]) && !empty($_POST["v_blackhole"])) {
		foreach ($vfwd as $forward) {
			if (empty($_SESSION["error_msg"]) && !empty($forward)) {
				exec(
					HESTIA_CMD .
						"v-delete-mail-account-forward " .
						$v_username .
						" " .
						quoteshellarg($v_domain) .
						" " .
						quoteshellarg($v_account) .
						" " .
						quoteshellarg($forward),
					$output,
					$return_var,
				);
				check_return_code($return_var, $output);
				unset($output);
			}
			exec(
				HESTIA_CMD .
					"v-add-mail-account-forward " .
					$v_username .
					" " .
					quoteshellarg($v_domain) .
					" " .
					quoteshellarg($v_account) .
					" :blackhole:",
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
			$v_fwd = "";
			$v_blackhole = "yes";
		}
	}
	// Change forwarders
	if (empty($_SESSION["error_msg"]) && empty($_POST["v_blackhole"])) {
		$wfwd = preg_replace("/\n/", " ", $_POST["v_fwd"]);
		$wfwd = preg_replace("/,/", " ", $wfwd);
		$wfwd = preg_replace("/\s+/", " ", $wfwd);
		$wfwd = trim($wfwd);
		$fwd = explode(" ", $wfwd);
		$v_fwd = str_replace(" ", "\n", $wfwd);
		$result = array_diff($vfwd, $fwd);
		foreach ($result as $forward) {
			if (empty($_SESSION["error_msg"]) && !empty($forward)) {
				exec(
					HESTIA_CMD .
						"v-delete-mail-account-forward " .
						$v_username .
						" " .
						quoteshellarg($v_domain) .
						" " .
						quoteshellarg($v_account) .
						" " .
						quoteshellarg($forward),
					$output,
					$return_var,
				);
				check_return_code($return_var, $output);
				unset($output);
			}
		}
		$result = array_diff($fwd, $vfwd);
		foreach ($result as $forward) {
			if (empty($_SESSION["error_msg"]) && !empty($forward)) {
				exec(
					HESTIA_CMD .
						"v-add-mail-account-forward " .
						$v_username .
						" " .
						quoteshellarg($v_domain) .
						" " .
						quoteshellarg($v_account) .
						" " .
						quoteshellarg($forward),
					$output,
					$return_var,
				);
				check_return_code($return_var, $output);
				unset($output);
			}
		}
		$v_blackhole = "no";
	}

	// Delete FWD_ONLY flag
	if ($v_fwd_only == "yes" && empty($_POST["v_fwd_only"]) && empty($_SESSION["error_msg"])) {
		exec(
			HESTIA_CMD .
				"v-delete-mail-account-fwd-only " .
				$v_username .
				" " .
				quoteshellarg($v_domain) .
				" " .
				quoteshellarg($v_account),
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
		$v_fwd_only = "";
	}

	// Add FWD_ONLY flag
	if ($v_fwd_only != "yes" && !empty($_POST["v_fwd_only"]) && empty($_SESSION["error_msg"])) {
		exec(
			HESTIA_CMD .
				"v-add-mail-account-fwd-only " .
				$v_username .
				" " .
				quoteshellarg($v_domain) .
				" " .
				quoteshellarg($v_account),
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
		$v_fwd_only = "yes";
	}

	// Delete autoreply
	if ($v_autoreply == "yes" && empty($_POST["v_autoreply"]) && empty($_SESSION["error_msg"])) {
		exec(
			HESTIA_CMD .
				"v-delete-mail-account-autoreply " .
				$v_username .
				" " .
				quoteshellarg($v_domain) .
				" " .
				quoteshellarg($v_account),
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
		$v_autoreply = "no";
		$v_autoreply_message = "";
	}

	// Add autoreply
	if (!empty($_POST["v_autoreply"]) && empty($_SESSION["error_msg"])) {
		if ($v_autoreply_message != str_replace("\r\n", "\n", $_POST["v_autoreply_message"])) {
			$v_autoreply_message = str_replace("\r\n", "\n", $_POST["v_autoreply_message"]);
			$v_autoreply_message = quoteshellarg($v_autoreply_message);
			exec(
				HESTIA_CMD .
					"v-add-mail-account-autoreply " .
					$v_username .
					" " .
					quoteshellarg($v_domain) .
					" " .
					quoteshellarg($v_account) .
					" " .
					$v_autoreply_message,
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
			$v_autoreply = "yes";
			$v_autoreply_message = $_POST["v_autoreply_message"];
		}
	}

	$hostname = get_hostname();
	$webmail = "http://" . $hostname . "/" . $v_webmail_alias . "/";
	if (!empty($_SESSION["WEBMAIL_ALIAS"])) {
		$webmail = $_SESSION["WEBMAIL_ALIAS"];
	}

	// Email login credentials
	if (!empty($_POST["v_send_email"]) && empty($_SESSION["error_msg"])) {
		$to = $_POST["v_send_email"];
		$template = get_email_template("email_credentials", $_SESSION["language"]);
		if (!empty($template)) {
			preg_match("/<subject>(.*?)<\/subject>/si", $template, $matches);
			$subject = $matches[1];
			$subject = str_replace(
				["{{hostname}}", "{{appname}}", "{{account}}", "{{domain}}"],
				[
					get_hostname(),
					$_SESSION["APP_NAME"],
					htmlentities(strtolower($_POST["v_account"])),
					htmlentities($_POST["v_domain"]),
				],
				$subject,
			);
			$template = str_replace($matches[0], "", $template);
		} else {
			$template = _(
				"Mail account has been created.\n" .
					"\n" .
					"Common Account Settings:\n" .
					"Username: {{account}}@{{domain}}\n" .
					"Password: {{password}}\n" .
					"Webmail: {{webmail}}\n" .
					"Hostname: {{hostname}}\n" .
					"\n" .
					"IMAP Settings\n" .
					"Authentication: Normal Password\n" .
					"SSL/TLS: Port 993\n" .
					"STARTTLS: Port 143\n" .
					"No encryption: Port 143\n" .
					"\n" .
					"POP3 Settings\n" .
					"Authentication: Normal Password\n" .
					"SSL/TLS: Port 995\n" .
					"STARTTLS: Port 110\n" .
					"No encryption: Port 110\n" .
					"\n" .
					"SMTP Settings\n" .
					"Authentication: Normal Password\n" .
					"SSL/TLS: Port 465\n" .
					"STARTTLS: Port 587\n" .
					"No encryption: Port 25\n" .
					"\n" .
					"Best regards,\n" .
					"\n" .
					"--\n" .
					"{{appname}}",
			);
		}
		if (empty($subject)) {
			$subject = str_replace(
				["{{subject}}", "{{hostname}}", "{{appname}}"],
				[
					sprintf(
						_("Email Credentials: %s@%s"),
						htmlentities(strtolower($_POST["v_account"])),
						htmlentities($_POST["v_domain"]),
					),
					get_hostname(),
					$_SESSION["APP_NAME"],
				],
				$_SESSION["SUBJECT_EMAIL"],
			);
		}

		$hostname = get_hostname();
		$from = !empty($_SESSION["FROM_EMAIL"]) ? $_SESSION["FROM_EMAIL"] : "noreply@" . $hostname;
		$from_name = !empty($_SESSION["FROM_NAME"])
			? $_SESSION["FROM_NAME"]
			: $_SESSION["APP_NAME"];

		$mailtext = translate_email($template, [
			"domain" => htmlentities($_POST["v_domain"]),
			"account" => htmlentities(strtolower($_POST["v_account"])),
			"password" => htmlentities($_POST["v_password"]),
			"webmail" => $webmail . "." . htmlentities($_POST["v_domain"]),
			"hostname" => "mail." . htmlentities($_POST["v_domain"]),
			"appname" => $_SESSION["APP_NAME"],
		]);

		send_email($to, $subject, $mailtext, $from, $from_name);
	}

	// Set success message
	if (empty($_SESSION["error_msg"])) {
		$_SESSION["ok_msg"] = _("Changes have been saved.");
	}
}

// Render page
if (empty($_GET["account"])) {
	// Display body for mail domain
	render_page($user, $TAB, "edit_mail");
} else {
	// Display body for mail account
	render_page($user, $TAB, "edit_mail_acc");
}

// Flush session messages
unset($_SESSION["error_msg"]);
unset($_SESSION["ok_msg"]);
