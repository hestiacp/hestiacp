<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
$TAB = "USER";

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
	if (empty($_POST["v_username"])) {
		$errors[] = _("Username");
	}
	if (empty($_POST["v_password"])) {
		$errors[] = _("Password");
	}
	if (empty($_POST["v_package"])) {
		$errrors[] = _("Package");
	}
	if (empty($_POST["v_email"])) {
		$errors[] = _("Email");
	}
	if (empty($_POST["v_name"])) {
		$errors[] = _("Contact Name");
	}
	if (!empty($errors)) {
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
	if (empty($_SESSION["error_msg"]) && !filter_var($_POST["v_email"], FILTER_VALIDATE_EMAIL)) {
		$_SESSION["error_msg"] = _("Please enter a valid email address.");
	}

	// Check password length
	if (empty($_SESSION["error_msg"])) {
		if (!validate_password($_POST["v_password"])) {
			$_SESSION["error_msg"] = _("Password does not match the minimum requirements.");
		}
	}

	// Protect input
	$v_username = quoteshellarg($_POST["v_username"]);
	$v_email = quoteshellarg($_POST["v_email"]);
	$v_package = quoteshellarg($_POST["v_package"]);
	$v_language = quoteshellarg($_POST["v_language"]);
	$v_name = quoteshellarg($_POST["v_name"]);
	$v_notify = $_POST["v_notify"];

	// Add user
	if (empty($_SESSION["error_msg"])) {
		$v_password = tempnam("/tmp", "vst");
		$fp = fopen($v_password, "w");
		fwrite($fp, $_POST["v_password"] . "\n");
		fclose($fp);
		exec(
			HESTIA_CMD .
				"v-add-user " .
				$v_username .
				" " .
				$v_password .
				" " .
				$v_email .
				" " .
				$v_package .
				" " .
				$v_name,
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
		unlink($v_password);
		$v_password = quoteshellarg($_POST["v_password"]);
	}

	// Set language
	if (empty($_SESSION["error_msg"])) {
		exec(
			HESTIA_CMD . "v-change-user-language " . $v_username . " " . $v_language,
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
	}

	// Set Role
	if (empty($_SESSION["error_msg"])) {
		$v_role = quoteshellarg($_POST["v_role"]);
		exec(
			HESTIA_CMD . "v-change-user-role " . $v_username . " " . $v_role,
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
	}

	// Set login restriction
	if (empty($_SESSION["error_msg"])) {
		if (!empty($_POST["v_login_disabled"])) {
			$_POST["v_login_disabled"] = "yes";
			exec(
				HESTIA_CMD .
					"v-change-user-config-value " .
					$v_username .
					" LOGIN_DISABLED " .
					quoteshellarg($_POST["v_login_disabled"]),
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
		}
	}

	// Send email to the new user
	if (empty($_SESSION["error_msg"]) && !empty($v_notify)) {
		$to = $_POST["v_notify"];
		// send email in "users" language
		putenv("LANGUAGE=" . $_POST["v_language"]);

		$name = empty($_POST["v_name"]) ? $_POST["v_username"] : $_POST["v_name"];

		$template = get_email_template("account_ready", $v_language);
		if (!empty($template)) {
			preg_match("/<subject>(.*?)<\/subject>/si", $template, $matches);
			$subject = $matches[1];
			$subject = str_replace(
				["{{hostname}}", "{{appname}}", "{{user}}", "{{name}}"],
				[get_hostname(), $_SESSION["APP_NAME"], $_POST["v_username"], $name],
				$subject,
			);
			$template = str_replace($matches[0], "", $template);
		} else {
			$template = _(
				"Hello {{name}},\n" .
					"\n" .
					"Your account has been created and ready to use.\n" .
					"\n" .
					"https://{{hostname}}/login/\n" .
					"Username: {{user}}\n" .
					"Password: {{password}}\n" .
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
					sprintf(_("Welcome to %s"), $_SESSION["APP_NAME"]),
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

		if ($hostname) {
			$host = preg_replace("/(\[?[^]]*\]?):([0-9]{1,5})$/", "$1", $_SERVER["HTTP_HOST"]);
			if ($host == $hostname) {
				$port_is_defined = preg_match("/\[?[^]]*\]?:[0-9]{1,5}$/", $_SERVER["HTTP_HOST"]);
				if ($port_is_defined) {
					$port =
						":" .
						preg_replace("/(\[?[^]]*\]?):([0-9]{1,5})$/", "$2", $_SERVER["HTTP_HOST"]);
				} else {
					$port = "";
				}
			} else {
				$port = ":" . $_SERVER["SERVER_PORT"];
			}
			$hostname = $hostname . $port;
		} else {
			$hostname = $_SERVER["HTTP_HOST"];
		}

		$mailtext = translate_email($template, [
			"name" => htmlentities($name),
			"user" => htmlentities($_POST["v_username"]),
			"password" => htmlentities($_POST["v_password"]),
			"hostname" => htmlentities($hostname),
			"appname" => $_SESSION["APP_NAME"],
		]);

		send_email($to, $subject, $mailtext, $from, $from_name, $name);
		putenv("LANGUAGE=" . detect_user_language());
	}

	// Flush field values on success
	if (empty($_SESSION["error_msg"])) {
		$_SESSION["ok_msg"] = htmlify_trans(
			sprintf(
				_("User {%s} has been created successfully. / {Log in as %s}"),
				htmlentities($_POST["v_username"]),
				htmlentities($_POST["v_username"]),
			),
			"</a>",
			'<a href="/edit/user/?user=' . htmlentities($_POST["v_username"]) . '">',
			'<a href="/login/?loginas=' .
				htmlentities($_POST["v_username"]) .
				"&token=" .
				htmlentities($_SESSION["token"]) .
				'">',
		);
		unset($v_username);
		unset($v_password);
		unset($v_email);
		unset($v_name);
		unset($v_notify);
	}
}

// List hosting packages
exec(HESTIA_CMD . "v-list-user-packages json", $output, $return_var);
check_error($return_var);
$data = json_decode(implode("", $output), true);
unset($output);

// List languages
exec(HESTIA_CMD . "v-list-sys-languages json", $output, $return_var);
$language = json_decode(implode("", $output), true);
foreach ($language as $lang) {
	$languages[$lang] = translate_json($lang);
}
asort($languages);

if (empty($v_username)) {
	$v_username = "";
}
if (empty($v_name)) {
	$v_name = "";
}
if (empty($v_email)) {
	$v_email = "";
}
if (empty($v_password)) {
	$v_password = "";
}
if (empty($v_login_disabled)) {
	$v_login_disabled = "";
}
if (empty($v_role)) {
	$v_role = "";
}
if (empty($v_notify)) {
	$v_notify = "";
}
// Render page
render_page($user, $TAB, "add_user");

// Flush session messages
unset($_SESSION["error_msg"]);
unset($_SESSION["ok_msg"]);
