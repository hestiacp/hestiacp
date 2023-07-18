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

// Check package argument
if (empty($_GET["package"])) {
	header("Location: /list/package/");
	exit();
}

// Prevent editing of system package
if ($_GET["package"] === "system") {
	header("Location: /list/package/");
	exit();
}

// List package
$v_package = quoteshellarg($_GET["package"]);
exec(HESTIA_CMD . "v-list-user-package " . $v_package . " 'json'", $output, $return_var);
check_return_code_redirect($return_var, $output, "/list/package/");
$data = json_decode(implode("", $output), true);
unset($output);

// Parse package
$v_package = $_GET["package"];
$v_package_new = $_GET["package"];
$v_web_template = $data[$v_package]["WEB_TEMPLATE"];
$v_backend_template = $data[$v_package]["BACKEND_TEMPLATE"];
$v_proxy_template = $data[$v_package]["PROXY_TEMPLATE"];
$v_dns_template = $data[$v_package]["DNS_TEMPLATE"];
$v_web_domains = $data[$v_package]["WEB_DOMAINS"];
$v_web_aliases = $data[$v_package]["WEB_ALIASES"];
$v_dns_domains = $data[$v_package]["DNS_DOMAINS"];
$v_dns_records = $data[$v_package]["DNS_RECORDS"];
$v_mail_domains = $data[$v_package]["MAIL_DOMAINS"];
$v_mail_accounts = $data[$v_package]["MAIL_ACCOUNTS"];
$v_ratelimit = $data[$v_package]["RATE_LIMIT"];
$v_databases = $data[$v_package]["DATABASES"];
$v_cron_jobs = $data[$v_package]["CRON_JOBS"];
$v_disk_quota = $data[$v_package]["DISK_QUOTA"];
$v_bandwidth = $data[$v_package]["BANDWIDTH"];
$v_shell = $data[$v_package]["SHELL"];
$v_ns = $data[$v_package]["NS"];
$nameservers = explode(",", $v_ns);
if (empty($nameservers[0])) {
	$v_ns1 = "";
} else {
	$v_ns1 = $nameservers[0];
}
if (empty($nameservers[1])) {
	$v_ns2 = "";
} else {
	$v_ns2 = $nameservers[1];
}
if (empty($nameservers[2])) {
	$v_ns3 = "";
} else {
	$v_ns3 = $nameservers[2];
}
if (empty($nameservers[3])) {
	$v_ns4 = "";
} else {
	$v_ns4 = $nameservers[3];
}
if (empty($nameservers[4])) {
	$v_ns5 = "";
} else {
	$v_ns5 = $nameservers[4];
}
if (empty($nameservers[5])) {
	$v_ns6 = "";
} else {
	$v_ns6 = $nameservers[5];
}
if (empty($nameservers[6])) {
	$v_ns7 = "";
} else {
	$v_ns7 = $nameservers[6];
}
if (empty($nameservers[7])) {
	$v_ns8 = "";
} else {
	$v_ns8 = $nameservers[7];
}
$v_backups = $data[$v_package]["BACKUPS"];
$v_date = $data[$v_package]["DATE"];
$v_time = $data[$v_package]["TIME"];
$v_status = "active";

// List web templates
exec(HESTIA_CMD . "v-list-web-templates json", $output, $return_var);
$web_templates = json_decode(implode("", $output), true);
unset($output);

// List backend templates
if (!empty($_SESSION["WEB_BACKEND"])) {
	exec(HESTIA_CMD . "v-list-web-templates-backend json", $output, $return_var);
	$backend_templates = json_decode(implode("", $output), true);
	unset($output);
}

// List proxy templates
if (!empty($_SESSION["PROXY_SYSTEM"])) {
	exec(HESTIA_CMD . "v-list-web-templates-proxy json", $output, $return_var);
	$proxy_templates = json_decode(implode("", $output), true);
	unset($output);
}

// List dns templates
exec(HESTIA_CMD . "v-list-dns-templates json", $output, $return_var);
$dns_templates = json_decode(implode("", $output), true);
unset($output);

// List shels
exec(HESTIA_CMD . "v-list-sys-shells json", $output, $return_var);
$shells = json_decode(implode("", $output), true);
unset($output);

// Check POST request
if (!empty($_POST["save"])) {
	// Check token
	verify_csrf($_POST);

	// Check empty fields
	if (empty($_POST["v_package"])) {
		$errors[] = _("Package");
	}
	if (empty($_POST["v_web_template"])) {
		$errors[] = _("Web Template");
	}
	if (!empty($_SESSION["WEB_BACKEND"])) {
		if (empty($_POST["v_backend_template"])) {
			$errors[] = _("Backend Template");
		}
	}
	if (!empty($_SESSION["PROXY_SYSTEM"])) {
		if (empty($_POST["v_proxy_template"])) {
			$errors[] = _("Proxy Template");
		}
	}
	if (empty($_POST["v_dns_template"])) {
		$errors[] = _("DNS Template");
	}
	if (empty($_POST["v_shell"])) {
		$errrors[] = _("Shell");
	}
	if (!isset($_POST["v_web_domains"])) {
		$errors[] = _("Web Domains");
	}
	if (!isset($_POST["v_web_aliases"])) {
		$errors[] = _("Web Aliases");
	}
	if (!isset($_POST["v_dns_domains"])) {
		$errors[] = _("DNS Zones");
	}
	if (!isset($_POST["v_dns_records"])) {
		$errors[] = _("DNS Records");
	}
	if (!isset($_POST["v_mail_domains"])) {
		$errors[] = _("Mail Domains");
	}
	if (!isset($_POST["v_mail_accounts"])) {
		$errors[] = _("Mail Accounts");
	}
	if (!isset($_POST["v_ratelimit"])) {
		$errors[] = _("Rate Limit");
	}
	if (!isset($_POST["v_databases"])) {
		$errors[] = _("Databases");
	}
	if (!isset($_POST["v_cron_jobs"])) {
		$errors[] = _("Cron Jobs");
	}
	if (!isset($_POST["v_backups"])) {
		$errors[] = _("Backups");
	}
	if (!isset($_POST["v_disk_quota"])) {
		$errors[] = _("Quota");
	}
	if (!isset($_POST["v_bandwidth"])) {
		$errors[] = _("Bandwidth");
	}

	// Check if name server entries are blank if DNS server is installed
	if (isset($_SESSION["DNS_SYSTEM"]) && !empty($_SESSION["DNS_SYSTEM"])) {
		if (empty($_POST["v_ns1"])) {
			$errors[] = _("Nameserver 1");
		}
		if (empty($_POST["v_ns2"])) {
			$errors[] = _("Nameserver 2");
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
	$v_package_new = quoteshellarg($_POST["v_package_new"]);
	$v_web_template = quoteshellarg($_POST["v_web_template"]);
	if (!empty($_SESSION["WEB_BACKEND"])) {
		$v_backend_template = quoteshellarg($_POST["v_backend_template"]);
	}
	if (!empty($_SESSION["PROXY_SYSTEM"])) {
		$v_proxy_template = quoteshellarg($_POST["v_proxy_template"]);
	}
	$v_dns_template = quoteshellarg($_POST["v_dns_template"]);
	if (!empty($_POST["v_shell"])) {
		$v_shell = quoteshellarg($_POST["v_shell"]);
	} else {
		$v_shell = "nologin";
	}
	$v_web_domains = quoteshellarg($_POST["v_web_domains"]);
	$v_web_aliases = quoteshellarg($_POST["v_web_aliases"]);
	$v_dns_domains = quoteshellarg($_POST["v_dns_domains"]);
	$v_dns_records = quoteshellarg($_POST["v_dns_records"]);
	$v_mail_domains = quoteshellarg($_POST["v_mail_domains"]);
	$v_mail_accounts = quoteshellarg($_POST["v_mail_accounts"]);
	$v_ratelimit = quoteshellarg($_POST["v_ratelimit"]);
	$v_databases = quoteshellarg($_POST["v_databases"]);
	$v_cron_jobs = quoteshellarg($_POST["v_cron_jobs"]);
	$v_backups = quoteshellarg($_POST["v_backups"]);
	$v_disk_quota = quoteshellarg($_POST["v_disk_quota"]);
	$v_bandwidth = quoteshellarg($_POST["v_bandwidth"]);
	$v_ns1 = !empty($_POST["v_ns1"]) ? trim($_POST["v_ns1"], ".") : "";
	$v_ns2 = !empty($_POST["v_ns2"]) ? trim($_POST["v_ns2"], ".") : "";
	$v_ns3 = !empty($_POST["v_ns3"]) ? trim($_POST["v_ns3"], ".") : "";
	$v_ns4 = !empty($_POST["v_ns4"]) ? trim($_POST["v_ns4"], ".") : "";
	$v_ns5 = !empty($_POST["v_ns5"]) ? trim($_POST["v_ns5"], ".") : "";
	$v_ns6 = !empty($_POST["v_ns6"]) ? trim($_POST["v_ns6"], ".") : "";
	$v_ns7 = !empty($_POST["v_ns7"]) ? trim($_POST["v_ns7"], ".") : "";
	$v_ns8 = !empty($_POST["v_ns8"]) ? trim($_POST["v_ns8"], ".") : "";
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

	// Save package file on a fs
	$pkg = "WEB_TEMPLATE=" . $v_web_template . "\n";
	$pkg .= "BACKEND_TEMPLATE=" . $v_backend_template . "\n";
	$pkg .= "PROXY_TEMPLATE=" . $v_proxy_template . "\n";
	$pkg .= "DNS_TEMPLATE=" . $v_dns_template . "\n";
	$pkg .= "WEB_DOMAINS=" . $v_web_domains . "\n";
	$pkg .= "WEB_ALIASES=" . $v_web_aliases . "\n";
	$pkg .= "DNS_DOMAINS=" . $v_dns_domains . "\n";
	$pkg .= "DNS_RECORDS=" . $v_dns_records . "\n";
	$pkg .= "MAIL_DOMAINS=" . $v_mail_domains . "\n";
	$pkg .= "MAIL_ACCOUNTS=" . $v_mail_accounts . "\n";
	$pkg .= "RATE_LIMIT=" . $v_ratelimit . "\n";
	$pkg .= "DATABASES=" . $v_databases . "\n";
	$pkg .= "CRON_JOBS=" . $v_cron_jobs . "\n";
	$pkg .= "DISK_QUOTA=" . $v_disk_quota . "\n";
	$pkg .= "BANDWIDTH=" . $v_bandwidth . "\n";
	$pkg .= "NS=" . $v_ns . "\n";
	$pkg .= "SHELL=" . $v_shell . "\n";
	$pkg .= "BACKUPS=" . $v_backups . "\n";
	$pkg .= "TIME=" . $v_time . "\n";
	$pkg .= "DATE=" . $v_date . "\n";

	$tmpfile = tempnam("/tmp/", "hst_");
	$fp = fopen($tmpfile, "w");
	fwrite($fp, $pkg);
	exec(
		HESTIA_CMD . "v-add-user-package " . $tmpfile . " " . $v_package . " yes",
		$output,
		$return_var,
	);
	check_return_code($return_var, $output);
	unset($output);

	fclose($fp);
	unlink($tmpfile);

	// Propagate new package
	exec(HESTIA_CMD . "v-update-user-package " . $v_package . " 'json'", $output, $return_var);
	check_return_code($return_var, $output);
	unset($output);

	if ($v_package_new != $v_package) {
		exec(
			HESTIA_CMD . "v-rename-user-package " . $v_package . " " . $v_package_new,
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
	}
	// Set success message
	if (empty($_SESSION["error_msg"])) {
		$_SESSION["ok_msg"] = _("Changes have been saved.");
	}
}

// Render page
render_page($user, $TAB, "edit_package");

// Flush session messages
unset($_SESSION["error_msg"]);
unset($_SESSION["ok_msg"]);
