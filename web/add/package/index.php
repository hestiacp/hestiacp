<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
$TAB = "PACKAGE";

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
	if (empty($_POST["v_package"])) {
		$errors[] = _("package");
	}
	if (empty($_POST["v_web_template"])) {
		$errors[] = _("web template");
	}
	if (!empty($_SESSION["WEB_BACKEND"])) {
		if (empty($_POST["v_backend_template"])) {
			$errors[] = _("backend template");
		}
	} else {
		# When modphp is enabled
		$_POST["v_backend_template"] = "";
	}
	if (!empty($_SESSION["PROXY_SYSTEM"])) {
		if (empty($_POST["v_proxy_template"])) {
			$errors[] = _("proxy template");
		}
	} else {
		# when nginx only is enabled
		$_POST["v_proxy_template"] = "default";
	}
	if (empty($_POST["v_dns_template"])) {
		$errors[] = _("dns template");
	}
	if (empty($_POST["v_shell"])) {
		$errrors[] = _("shell");
	}
	if (!isset($_POST["v_web_domains"])) {
		$errors[] = _("web domains");
	}
	if (!isset($_POST["v_web_aliases"])) {
		$errors[] = _("web aliases");
	}
	if (!isset($_POST["v_dns_domains"])) {
		$errors[] = _("dns domains");
	}
	if (!isset($_POST["v_dns_records"])) {
		$errors[] = _("dns records");
	}
	if (!isset($_POST["v_mail_domains"])) {
		$errors[] = _("mail domains");
	}
	if (!isset($_POST["v_mail_accounts"])) {
		$errors[] = _("mail accounts");
	}
	if (!isset($_POST["v_databases"])) {
		$errors[] = _("databases");
	}
	if (!isset($_POST["v_cron_jobs"])) {
		$errors[] = _("cron jobs");
	}
	if (!isset($_POST["v_backups"])) {
		$errors[] = _("backups");
	}
	if (!isset($_POST["v_disk_quota"])) {
		$errors[] = _("quota");
	}
	if (!isset($_POST["v_bandwidth"])) {
		$errors[] = _("bandwidth");
	}
	if (!isset($_POST["v_ratelimit"])) {
		$errors[] = _("rate limit");
	}
	// Check if name server entries are blank if DNS server is installed
	if (isset($_SESSION["DNS_SYSTEM"]) && !empty($_SESSION["DNS_SYSTEM"])) {
		if (empty($_POST["v_ns1"])) {
			$errors[] = _("ns1");
		}
		if (empty($_POST["v_ns2"])) {
			$errors[] = _("ns2");
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

	// Protect input
	$v_package = quoteshellarg($_POST["v_package"]);
	$v_web_template = quoteshellarg($_POST["v_web_template"]);
	$v_backend_template = quoteshellarg($_POST["v_backend_template"]);
	$v_proxy_template = quoteshellarg($_POST["v_proxy_template"]);
	$v_dns_template = quoteshellarg($_POST["v_dns_template"]);
	$v_shell = quoteshellarg($_POST["v_shell"]);
	$v_web_domains = quoteshellarg($_POST["v_web_domains"]);
	$v_web_aliases = quoteshellarg($_POST["v_web_aliases"]);
	$v_dns_domains = quoteshellarg($_POST["v_dns_domains"]);
	$v_dns_records = quoteshellarg($_POST["v_dns_records"]);
	$v_mail_domains = quoteshellarg($_POST["v_mail_domains"]);
	$v_mail_accounts = quoteshellarg($_POST["v_mail_accounts"]);
	$v_databases = quoteshellarg($_POST["v_databases"]);
	$v_cron_jobs = quoteshellarg($_POST["v_cron_jobs"]);
	$v_backups = quoteshellarg($_POST["v_backups"]);
	$v_disk_quota = quoteshellarg($_POST["v_disk_quota"]);
	$v_bandwidth = quoteshellarg($_POST["v_bandwidth"]);
	$v_ratelimit = quoteshellarg($_POST["v_ratelimit"]);
	$v_ns1 = trim($_POST["v_ns1"], ".");
	$v_ns2 = trim($_POST["v_ns2"], ".");
	$v_ns3 = trim($_POST["v_ns3"], ".");
	$v_ns4 = trim($_POST["v_ns4"], ".");
	$v_ns5 = trim($_POST["v_ns5"], ".");
	$v_ns6 = trim($_POST["v_ns6"], ".");
	$v_ns7 = trim($_POST["v_ns7"], ".");
	$v_ns8 = trim($_POST["v_ns8"], ".");
	$v_ns = $v_ns1 . "," . $v_ns2;
	if (!empty($v_ns3)) {
		$v_ns .= "," . $v_ns3;
	}
	if (!empty($v_ns4)) {
		$v_ns .= "," . $v_ns4;
	}
	if (!empty($v_ns5)) {
		$v_ns .= "," . $v_ns5;
	}
	if (!empty($v_ns6)) {
		$v_ns .= "," . $v_ns6;
	}
	if (!empty($v_ns7)) {
		$v_ns .= "," . $v_ns7;
	}
	if (!empty($v_ns8)) {
		$v_ns .= "," . $v_ns8;
	}
	$v_ns = quoteshellarg($v_ns);
	$v_time = quoteshellarg(date("H:i:s"));
	$v_date = quoteshellarg(date("Y-m-d"));

	// Create package file
	if (empty($_SESSION["error_msg"])) {
		$pkg = "WEB_TEMPLATE=" . $v_web_template . "\n";
		if (!empty($_SESSION["WEB_BACKEND"])) {
			$pkg .= "BACKEND_TEMPLATE=" . $v_backend_template . "\n";
		}
		if (!empty($_SESSION["PROXY_SYSTEM"])) {
			$pkg .= "PROXY_TEMPLATE=" . $v_proxy_template . "\n";
		}
		$pkg .= "DNS_TEMPLATE=" . $v_dns_template . "\n";
		$pkg .= "WEB_DOMAINS=" . $v_web_domains . "\n";
		$pkg .= "WEB_ALIASES=" . $v_web_aliases . "\n";
		$pkg .= "DNS_DOMAINS=" . $v_dns_domains . "\n";
		$pkg .= "DNS_RECORDS=" . $v_dns_records . "\n";
		$pkg .= "MAIL_DOMAINS=" . $v_mail_domains . "\n";
		$pkg .= "MAIL_ACCOUNTS=" . $v_mail_accounts . "\n";
		$pkg .= "DATABASES=" . $v_databases . "\n";
		$pkg .= "CRON_JOBS=" . $v_cron_jobs . "\n";
		$pkg .= "DISK_QUOTA=" . $v_disk_quota . "\n";
		$pkg .= "BANDWIDTH=" . $v_bandwidth . "\n";
		$pkg .= "RATE_LIMIT=" . $v_ratelimit . "\n";
		$pkg .= "NS=" . $v_ns . "\n";
		$pkg .= "SHELL=" . $v_shell . "\n";
		$pkg .= "BACKUPS=" . $v_backups . "\n";
		$pkg .= "TIME=" . $v_time . "\n";
		$pkg .= "DATE=" . $v_date . "\n";

		$tmpfile = tempnam("/tmp/", "hst_");
		$fp = fopen($tmpfile, "w");
		fwrite($fp, $pkg);
		exec(
			HESTIA_CMD . "v-add-user-package " . $tmpfile . " " . $v_package,
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);

		fclose($fp);
		unlink($tmpfile);
	}

	// Flush field values on success
	if (empty($_SESSION["error_msg"])) {
		$_SESSION["ok_msg"] = htmlify_trans(
			sprintf(
				_("Package {%s} has been created successfully."),
				htmlentities($_POST["v_package"]),
			),
			"</b></a>",
			'<a href="/edit/package/?package=' . htmlentities($_POST["v_package"]) . '"><b>',
		);
		unset($v_package);
	}
}

// List web temmplates
exec(HESTIA_CMD . "v-list-web-templates json", $output, $return_var);
$web_templates = json_decode(implode("", $output), true);
unset($output);

// List web templates for backend
if (!empty($_SESSION["WEB_BACKEND"])) {
	exec(HESTIA_CMD . "v-list-web-templates-backend json", $output, $return_var);
	$backend_templates = json_decode(implode("", $output), true);
	unset($output);
}

// List web templates for proxy
if (!empty($_SESSION["PROXY_SYSTEM"])) {
	exec(HESTIA_CMD . "v-list-web-templates-proxy json", $output, $return_var);
	$proxy_templates = json_decode(implode("", $output), true);
	unset($output);
}

// List DNS templates
exec(HESTIA_CMD . "v-list-dns-templates json", $output, $return_var);
$dns_templates = json_decode(implode("", $output), true);
unset($output);

// List system shells
exec(HESTIA_CMD . "v-list-sys-shells json", $output, $return_var);
$shells = json_decode(implode("", $output), true);
unset($output);

// Set default values
if (empty($v_package)) {
	$v_package = "";
}
if (empty($v_web_template)) {
	$v_web_template = "default";
}
if (empty($v_backend_template)) {
	$v_backend_template = "default";
}
if (empty($v_proxy_template)) {
	$v_proxy_template = "default";
}
if (empty($v_dns_template)) {
	$v_dns_template = "default";
}
if (empty($v_shell)) {
	$v_shell = "nologin";
}
if (empty($v_web_domains)) {
	$v_web_domains = "'1'";
}
if (empty($v_web_aliases)) {
	$v_web_aliases = "'5'";
}
if (empty($v_dns_domains)) {
	$v_dns_domains = "'1'";
}
if (empty($v_dns_records)) {
	$v_dns_records = "'unlimited'";
}
if (empty($v_mail_domains)) {
	$v_mail_domains = "'1'";
}
if (empty($v_mail_accounts)) {
	$v_mail_accounts = "'5'";
}
if (empty($v_databases)) {
	$v_databases = "'1'";
}
if (empty($v_cron_jobs)) {
	$v_cron_jobs = "'1'";
}
if (empty($v_backups)) {
	$v_backups = "'1'";
}
if (empty($v_disk_quota)) {
	$v_disk_quota = "'1000'";
}
if (empty($v_bandwidth)) {
	$v_bandwidth = "'1000'";
}
if (empty($v_ratelimit)) {
	$v_ratelimit = "'200'";
}
if (empty($v_ns1)) {
	$v_ns1 = "ns1.example.ltd";
}
if (empty($v_ns2)) {
	$v_ns2 = "ns2.example.ltd";
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
// Render page
render_page($user, $TAB, "add_package");

// Flush session messages
unset($_SESSION["error_msg"]);
unset($_SESSION["ok_msg"]);
