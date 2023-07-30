<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
$TAB = "MAIL";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

exec(HESTIA_CMD . "v-list-sys-webmail json", $output, $return_var);
$webmail_clients = json_decode(implode("", $output), true);
unset($output);

if (!empty($_GET["domain"])) {
	$v_domain = $_GET["domain"];
}
if (!empty($v_domain)) {
	// Set webmail alias
	exec(
		HESTIA_CMD . "v-list-mail-domain " . $user . " " . quoteshellarg($v_domain) . " json",
		$output,
		$return_var,
	);
	if ($return_var > 0) {
		check_return_code_redirect($return_var, $output, "/list/mail/");
	}
	$data = json_decode(implode("", $output), true);
	unset($output);
	$v_webmail_alias = $data[$v_domain]["WEBMAIL_ALIAS"];
}

// Check POST request for mail domain
if (!empty($_POST["ok"])) {
	// Check token
	verify_csrf($_POST);

	// Check empty fields
	if (empty($_POST["v_domain"])) {
		$errors[] = _("Domain");
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

	// Check antispam option
	if (!empty($_POST["v_antispam"])) {
		$v_antispam = "yes";
	} else {
		$v_antispam = "no";
	}

	// Check antivirus option
	if (!empty($_POST["v_antivirus"])) {
		$v_antivirus = "yes";
	} else {
		$v_antivirus = "no";
	}

	// Check dkim option
	if (!empty($_POST["v_dkim"])) {
		$v_dkim = "yes";
	} else {
		$v_dkim = "no";
	}

	// Set domain name to lowercase and remove www prefix
	$v_domain = preg_replace("/^www./i", "", $_POST["v_domain"]);
	$v_domain = quoteshellarg($v_domain);
	$v_domain = strtolower($v_domain);

	// Add mail domain
	if (empty($_SESSION["error_msg"])) {
		exec(
			HESTIA_CMD .
				"v-add-mail-domain " .
				$user .
				" " .
				$v_domain .
				" " .
				$v_antispam .
				" " .
				$v_antivirus .
				" " .
				$v_dkim,
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
	}

	if (!empty($_POST["v_reject"]) && $v_antispam == "yes") {
		exec(
			HESTIA_CMD . "v-add-mail-domain-reject " . $user . " " . $v_domain . " yes",
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
				unset($output);
			}
		}
	}

	// Add SMTP Relay Support
	if (empty($_SESSION["error_msg"])) {
		if (isset($_POST["v_smtp_relay"]) && !empty($_POST["v_smtp_relay_host"])) {
			if (
				$_POST["v_smtp_relay_host"] != $v_smtp_relay_host ||
				$_POST["v_smtp_relay_user"] != $v_smtp_relay_user ||
				$_POST["v_smtp_relay_port"] != $v_smtp_relay_port
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
						$user .
						" " .
						$v_domain .
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
	}

	// Flush field values on success
	if (empty($_SESSION["error_msg"])) {
		$_SESSION["ok_msg"] = htmlify_trans(
			sprintf(
				_("Mail domain {%s} has been created successfully."),
				htmlentities($_POST["v_domain"]),
			),
			"</a>",
			'<a href="/list/mail/?domain=' . htmlentities($_POST["v_domain"]) . '">',
		);
		unset($v_domain, $v_webmail);
	}
}

// Check POST request for mail account
if (!empty($_POST["ok_acc"])) {
	// Check token
	if (!isset($_POST["token"]) || $_SESSION["token"] != $_POST["token"]) {
		header("location: /login/");
		exit();
	}

	// Check antispam option
	if (!empty($_POST["v_blackhole"])) {
		$v_blackhole = "yes";
	} else {
		$v_blackhole = "no";
	}
	// Check empty fields
	if (empty($_POST["v_domain"])) {
		$errors[] = _("Domain");
	}
	if (empty($_POST["v_account"])) {
		$errors[] = _("Account");
	}
	if (empty($_POST["v_fwd_only"]) && empty($_POST["v_password"])) {
		if (empty($_POST["v_password"])) {
			$errors[] = _("Password");
		}
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

	// Validate email
	if (!empty($_POST["v_send_email"]) && empty($_SESSION["error_msg"])) {
		if (!filter_var($_POST["v_send_email"], FILTER_VALIDATE_EMAIL)) {
			$_SESSION["error_msg"] = _("Please enter a valid email address.");
		}
	}

	// Check password length
	if (empty($_SESSION["error_msg"]) && empty($_POST["v_fwd_only"])) {
		if (!validate_password($_POST["v_password"])) {
			$_SESSION["error_msg"] = _("Password does not match the minimum requirements.");
		}
	}

	// Protect input
	$v_domain = quoteshellarg($_POST["v_domain"]);
	$v_domain = strtolower($v_domain);
	$v_account = quoteshellarg($_POST["v_account"]);
	$v_quota = quoteshellarg($_POST["v_quota"]);
	$v_send_email = $_POST["v_send_email"];
	$v_aliases = $_POST["v_aliases"];
	$v_fwd = $_POST["v_fwd"];
	if (empty($_POST["v_quota"])) {
		$v_quota = 0;
	}
	if (!empty($_POST["v_quota"]) || !empty($_POST["v_aliases"]) || !empty($_POST["v_fwd"])) {
		$v_adv = "yes";
	}

	// Add Mail Account
	if (empty($_SESSION["error_msg"])) {
		$v_password = tempnam("/tmp", "vst");
		$fp = fopen($v_password, "w");
		fwrite($fp, $_POST["v_password"] . "\n");
		fclose($fp);
		exec(
			HESTIA_CMD .
				"v-add-mail-account " .
				$user .
				" " .
				$v_domain .
				" " .
				$v_account .
				" " .
				$v_password .
				" " .
				$v_quota,
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
		unlink($v_password);
		$v_password = quoteshellarg($_POST["v_password"]);
	}

	// Add Aliases
	if (!empty($_POST["v_aliases"]) && empty($_SESSION["error_msg"])) {
		$valiases = preg_replace("/\n/", " ", $_POST["v_aliases"]);
		$valiases = preg_replace("/,/", " ", $valiases);
		$valiases = preg_replace("/\s+/", " ", $valiases);
		$valiases = trim($valiases);
		$aliases = explode(" ", $valiases);
		foreach ($aliases as $alias) {
			$alias = quoteshellarg($alias);
			if (empty($_SESSION["error_msg"])) {
				exec(
					HESTIA_CMD .
						"v-add-mail-account-alias " .
						$user .
						" " .
						$v_domain .
						" " .
						$v_account .
						" " .
						$alias,
					$output,
					$return_var,
				);
				check_return_code($return_var, $output);
				unset($output);
			}
		}
	}

	if (!empty($_POST["v_blackhole"]) && empty($_SESSION["error_msg"])) {
		exec(
			HESTIA_CMD .
				"v-add-mail-account-forward " .
				$user .
				" " .
				$v_domain .
				" " .
				$v_account .
				" :blackhole:",
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
		//disable any input in v_fwd
		$_POST["v_fwd"] = "";
	}
	// Add Forwarders
	if (!empty($_POST["v_fwd"]) && empty($_SESSION["error_msg"])) {
		$vfwd = preg_replace("/\n/", " ", $_POST["v_fwd"]);
		$vfwd = preg_replace("/,/", " ", $vfwd);
		$vfwd = preg_replace("/\s+/", " ", $vfwd);
		$vfwd = trim($vfwd);
		$fwd = explode(" ", $vfwd);
		foreach ($fwd as $forward) {
			$forward = quoteshellarg($forward);
			if (empty($_SESSION["error_msg"])) {
				exec(
					HESTIA_CMD .
						"v-add-mail-account-forward " .
						$user .
						" " .
						$v_domain .
						" " .
						$v_account .
						" " .
						$forward,
					$output,
					$return_var,
				);
				check_return_code($return_var, $output);
				unset($output);
			}
		}
	}

	// Add fwd_only flag
	if (!empty($_POST["v_fwd_only"]) && empty($_SESSION["error_msg"])) {
		exec(
			HESTIA_CMD .
				"v-add-mail-account-fwd-only " .
				$user .
				" " .
				$v_domain .
				" " .
				$v_account,
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
	}

	// Add fwd_only flag
	if (
		!empty($_POST["v_rate"]) &&
		empty($_SESSION["error_msg"]) &&
		$_SESSION["userContext"] == "admin"
	) {
		$v_rate = quoteshellarg($_POST["v_rate"]);
		exec(
			HESTIA_CMD .
				"v-change-mail-account-rate-limit " .
				$user .
				" " .
				$v_domain .
				" " .
				$v_account .
				" " .
				$v_rate,
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
	}

	// Get webmail url
	if (empty($_SESSION["error_msg"])) {
		[$hostname, $port] = explode(":", $_SERVER["HTTP_HOST"] . ":");
		$webmail = "http://" . $hostname . "/" . $v_webmail_alias . "/";
		if (!empty($_SESSION["WEBMAIL_ALIAS"])) {
			$webmail = $_SESSION["WEBMAIL_ALIAS"];
		}
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

	// Flush field values on success
	if (empty($_SESSION["error_msg"])) {
		$_SESSION["ok_msg"] = htmlify_trans(
			sprintf(
				_("Mail account {%s@%s} has been created successfully."),
				htmlentities(strtolower($_POST["v_account"])),
				htmlentities($_POST["v_domain"]),
			),
			"</a>",
			'<a href="/edit/mail/?account=' .
				htmlentities(strtolower($_POST["v_account"])) .
				"&domain=" .
				htmlentities($_POST["v_domain"]) .
				'">',
		);
		unset($v_account);
		unset($v_password);
		unset($v_aliases);
		unset($v_fwd);
		unset($v_quota);
	}
}

// Render page
if (empty($_GET["domain"])) {
	// Display body for mail domain
	if (!empty($_POST["v_webmail"])) {
		$v_webmail = $_POST["v_webmail"];
	} else {
		//default is always roundcube unless it hasn't been installed. Then picks the first one in order
		$v_webmail = "roundcube";
	}

	if (empty($_GET["accept"])) {
		$_GET["accept"] = false;
	}
	if (empty($v_domain)) {
		$v_domain = "";
	}
	if (empty($v_smtp_relay)) {
		$v_smtp_relay = "";
	}
	if (empty($v_smtp_relay_user)) {
		$v_smtp_relay_user = "";
	}
	if (empty($v_smtp_relay_password)) {
		$v_smtp_relay_password = "";
	}
	if (empty($v_smtp_relay_host)) {
		$v_smtp_relay_host = "";
	}
	if (empty($v_smtp_relay_port)) {
		$v_smtp_relay_port = "";
	}

	$accept = $_GET["accept"] ?? "";
	render_page($user, $TAB, "add_mail");
} else {
	// Display body for mail account
	if (empty($v_account)) {
		$v_account = "";
	}
	if (empty($v_quota)) {
		$v_quota = "";
	}
	if (empty($v_rate)) {
		$v_rate = "";
	}
	if (empty($v_blackhole)) {
		$v_blackhole = "";
	}
	if (empty($v_fwd_only)) {
		$v_fwd_only = "";
	}
	if (empty($v_aliases)) {
		$v_aliases = "";
	}
	if (empty($v_send_email)) {
		$v_send_email = "";
	}
	if (empty($v_fwd)) {
		$v_fwd = "";
	}
	$v_domain = $_GET["domain"];
	render_page($user, $TAB, "add_mail_acc");
}

// Flush session messages
unset($_SESSION["error_msg"]);
unset($_SESSION["ok_msg"]);
