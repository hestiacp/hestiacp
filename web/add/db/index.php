<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
$TAB = "DB";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check POST request
if (!empty($_POST["ok"])) {
	// Check token
	verify_csrf($_POST);

	// Check empty fields
	if (empty($_POST["v_database"])) {
		$errors[] = _("Database");
	}
	if (empty($_POST["v_dbuser"])) {
		$errors[] = _("Username");
	}
	if (empty($_POST["v_password"])) {
		$errors[] = _("Password");
	}
	if (empty($_POST["v_type"])) {
		$errors[] = _("Type");
	}
	if (empty($_POST["v_host"])) {
		$errors[] = _("Host");
	}
	if (empty($_POST["v_charset"])) {
		$errors[] = _("Charset");
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
	if (!empty($_POST["v_db_email"]) && empty($_SESSION["error_msg"])) {
		if (!filter_var($_POST["v_db_email"], FILTER_VALIDATE_EMAIL)) {
			$_SESSION["error_msg"] = _("Please enter a valid email address.");
		}
	}

	// Check password length
	if (empty($_SESSION["error_msg"])) {
		if (!validate_password($_POST["v_password"])) {
			$_SESSION["error_msg"] = _("Password does not match the minimum requirements.");
		}
	}

	// Protect input
	$v_database = quoteshellarg($_POST["v_database"]);
	$v_dbuser = quoteshellarg($_POST["v_dbuser"]);
	$v_type = $_POST["v_type"];
	$v_charset = $_POST["v_charset"];
	$v_host = $_POST["v_host"];
	$v_db_email = $_POST["v_db_email"];

	// Add database
	if (empty($_SESSION["error_msg"])) {
		$v_type = quoteshellarg($_POST["v_type"]);
		$v_charset = quoteshellarg($_POST["v_charset"]);
		$v_host = quoteshellarg($_POST["v_host"]);
		$v_password = tempnam("/tmp", "vst");
		$fp = fopen($v_password, "w");
		fwrite($fp, $_POST["v_password"] . "\n");
		fclose($fp);
		exec(
			HESTIA_CMD .
				"v-add-database " .
				$user .
				" " .
				$v_database .
				" " .
				$v_dbuser .
				" " .
				$v_password .
				" " .
				$v_type .
				" " .
				$v_host .
				" " .
				$v_charset,
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
		unlink($v_password);
		$v_password = quoteshellarg($_POST["v_password"]);
		$v_type = $_POST["v_type"];
		$v_host = $_POST["v_host"];
		$v_charset = $_POST["v_charset"];
	}

	// Get database manager url
	if (empty($_SESSION["error_msg"])) {
		[$http_host, $port] = explode(":", $_SERVER["HTTP_HOST"] . ":");
		if ($_POST["v_host"] != "localhost") {
			$http_host = $_POST["v_host"];
		}
		if ($_POST["v_type"] == "mysql") {
			$db_admin = "phpMyAdmin";
		}
		if ($_POST["v_type"] == "mysql") {
			$db_admin_link = "https://" . $http_host . "/phpmyadmin/";
		}
		if ($_POST["v_type"] == "mysql" && !empty($_SESSION["DB_PMA_ALIAS"])) {
			$db_admin_link = "https://" . $http_host . "/" . $_SESSION["DB_PMA_ALIAS"];
		}
		if ($_POST["v_type"] == "pgsql") {
			$db_admin = "phpPgAdmin";
		}
		if ($_POST["v_type"] == "pgsql") {
			$db_admin_link = "https://" . $http_host . "/phppgadmin/";
		}
		if ($_POST["v_type"] == "pgsql" && !empty($_SESSION["DB_PGA_ALIAS"])) {
			$db_admin_link = "https://" . $http_host . "/" . $_SESSION["DB_PGA_ALIAS"];
		}
	}

	// Email login credentials
	if (!empty($v_db_email) && empty($_SESSION["error_msg"])) {
		$to = $v_db_email;
		$template = get_email_template("database_credentials", $_SESSION["language"]);
		if (!empty($template)) {
			preg_match("/<subject>(.*?)<\/subject>/si", $template, $matches);
			$subject = $matches[1];
			$subject = str_replace(
				["{{hostname}}", "{{appname}}", "{{dabase}}", "{{dbuser}}"],
				[
					get_hostname(),
					$_SESSION["APP_NAME"],
					$user_plain . "_" . $_POST["v_database"],
					$user_plain . "_" . $_POST["v_dbuser"],
				],
				$subject,
			);
			$template = str_replace($matches[0], "", $template);
		} else {
			$template = _(
				"Database has been created.\n" .
					"\n" .
					"Database: {{database}}\n" .
					"Username: {{username}}\n" .
					"Password: {{password}}\n" .
					"SQL Manager: {{dbadmin}}\n" .
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
						_("Database Credentials: %s"),
						$user_plain . "_" . $_POST["v_database"],
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
			"database" => htmlentities($user_plain . "_" . $_POST["v_database"]),
			"username" => htmlentities($user_plain . "_" . $_POST["v_dbuser"]),
			"password" => htmlentities($_POST["v_password"]),
			"dbadmin" => $db_admin_link,
			"appname" => $_SESSION["APP_NAME"],
		]);

		send_email($to, $subject, $mailtext, $from, $from_name);
	}

	// Flush field values on success
	if (empty($_SESSION["error_msg"])) {
		$_SESSION["ok_msg"] = htmlify_trans(
			sprintf(
				_("Database {%s} has been created successfully. / {Open %s}"),
				htmlentities($user_plain) . "_" . htmlentities($_POST["v_database"]),
				htmlentities($user_plain) . "_" . htmlentities($_POST["v_database"]),
			),
			"</a>",
			'<a href="/edit/db/?database=' .
				htmlentities($user_plain) .
				"_" .
				htmlentities($_POST["v_database"]) .
				'">',
			'<a href="' . $db_admin_link . '" target="_blank">',
		);
		unset($v_database);
		unset($v_dbuser);
		unset($v_password);
		unset($v_type);
		unset($v_charset);
	}
}

// Get user email
$v_db_email = "";
if (empty($v_database)) {
	$v_database = "";
}
if (empty($v_dbuser)) {
	$v_dbuser = "";
}

// List avaiable database types
$db_types = explode(",", $_SESSION["DB_SYSTEM"]);

// List available database servers
exec(HESTIA_CMD . "v-list-database-hosts json", $output, $return_var);
$db_hosts_tmp1 = json_decode(implode("", $output), true);
$db_hosts_tmp2 = array_map(function ($host) {
	return $host["HOST"];
}, $db_hosts_tmp1);
$db_hosts = array_values(array_unique($db_hosts_tmp2));
unset($output);
unset($db_hosts_tmp1);
unset($db_hosts_tmp2);

$accept = $_GET["accept"] ?? "";

render_page($user, $TAB, "add_db");

// Flush session messages
unset($_SESSION["error_msg"]);
unset($_SESSION["ok_msg"]);
