<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

define("NO_AUTH_REQUIRED", true);
$TAB = "RESET PASSWORD";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

if (isset($_SESSION["user"])) {
	header("Location: /list/user");
}

if ($_SESSION["POLICY_SYSTEM_PASSWORD_RESET"] == "no") {
	header("Location: /login/");
	exit();
}

if (!empty($_POST["user"]) && empty($_POST["code"])) {
	// Check token
	verify_csrf($_POST);
	$v_user = quoteshellarg($_POST["user"]);
	$user = $_POST["user"];
	$email = $_POST["email"];
	$cmd = "/usr/bin/sudo /usr/local/hestia/bin/v-list-user";
	exec($cmd . " " . $v_user . " json", $output, $return_var);
	if ($return_var == 0) {
		$data = json_decode(implode("", $output), true);
		unset($output);
		exec(HESTIA_CMD . "v-get-user-value " . $v_user . " RKEYEXP", $output, $return_var);
		$rkeyexp = json_decode(implode("", $output), true);
		if ($rkeyexp === null || $rkeyexp < time() - 1) {
			if ($email == $data[$user]["CONTACT"]) {
				$rkey = substr(password_hash("", PASSWORD_DEFAULT), 8, 12);
				$hash = password_hash($rkey, PASSWORD_DEFAULT);
				$v_rkey = tempnam("/tmp", "vst");
				$fp = fopen($v_rkey, "w");
				fwrite($fp, $hash . "\n");
				fclose($fp);
				exec(
					HESTIA_CMD . "v-change-user-rkey " . $v_user . " " . $v_rkey . "",
					$output,
					$return_var,
				);
				unset($output);
				unlink($v_rkey);
				$template = get_email_template("reset_password", $data[$user]["LANGUAGE"]);
				if (!empty($template)) {
					preg_match("/<subject>(.*?)<\/subject>/si", $template, $matches);
					$subject = $matches[1];
					$subject = str_replace(
						["{{date}}", "{{hostname}}", "{{appname}}", "{{user}}"],
						[date("Y-m-d H:i:s"), get_hostname(), $_SESSION["APP_NAME"], $user],
						$subject,
					);
					$template = str_replace($matches[0], "", $template);
				} else {
					putenv("LANGUAGE=" . $data[$user]["LANGUAGE"]);
					$template = _(
						"Hello {{name}},\n" .
							"\n" .
							"To reset your {{appname}} password, please follow this link:\n" .
							"https://{{hostname}}/reset/?action=confirm&user={{user}}&code={{resetcode}}\n" .
							"\n" .
							"Alternatively, you may go to https://{{hostname}}/reset/?action=code&user={{user}} and enter the following reset code:\n" .
							"{{resetcode}}\n" .
							"\n" .
							"If you did not request password reset, please ignore this message and accept our apologies.\n" .
							"\n" .
							"Best regards,\n" .
							"\n" .
							"--\n" .
							"{{appname}}",
					);
					putenv("LANGUAGE=" . detect_user_language());
				}
				$name = $data[$user]["NAME"];
				$contact = $data[$user]["CONTACT"];
				$to = $data[$user]["CONTACT"];
				if (empty($subject)) {
					$subject = str_replace(
						["{{subject}}", "{{hostname}}", "{{appname}}"],
						[
							sprintf(_("Password Reset at %s"), date("Y-m-d H:i:s")),
							get_hostname(),
							$_SESSION["APP_NAME"],
						],
						$_SESSION["SUBJECT_EMAIL"],
					);
				}
				$hostname = get_hostname();
				if ($hostname) {
					$host = preg_replace(
						"/(\[?[^]]*\]?):([0-9]{1,5})$/",
						"$1",
						$_SERVER["HTTP_HOST"],
					);
					if ($host == $hostname) {
						$port_is_defined = preg_match(
							"/\[?[^]]*\]?:[0-9]{1,5}$/",
							$_SERVER["HTTP_HOST"],
						);
						if ($port_is_defined) {
							$port =
								":" .
								preg_replace(
									"/(\[?[^]]*\]?):([0-9]{1,5})$/",
									"$2",
									$_SERVER["HTTP_HOST"],
								);
						} else {
							$port = "";
						}
					} else {
						$port = ":" . $_SERVER["SERVER_PORT"];
					}
					$from = !empty($_SESSION["FROM_EMAIL"])
						? $_SESSION["FROM_EMAIL"]
						: "noreply@" . $hostname;
					$from_name = !empty($_SESSION["FROM_NAME"])
						? $_SESSION["FROM_NAME"]
						: $_SESSION["APP_NAME"];

					putenv("LANGUAGE=" . $data[$user]["LANGUAGE"]);
					$name = empty($data[$user]["NAME"]) ? $user : $data[$user]["NAME"];
					$mailtext = translate_email($template, [
						"name" => htmlentities($name),
						"hostname" => htmlentities($hostname . $port),
						"user" => htmlentities($user),
						"resetcode" => htmlentities($rkey),
						"appname" => $_SESSION["APP_NAME"],
					]);

					send_email($to, $subject, $mailtext, $from, $from_name, $data[$user]["NAME"]);
					putenv("LANGUAGE=" . detect_user_language());
					$error = _(
						"Password reset instructions have been sent to the email address associated with this account.",
					);
				}
				$error = _(
					"Password reset instructions have been sent to the email address associated with this account.",
				);
			} else {
				# Prevent user enumeration and let hackers guess username and working email
				$error = _(
					"Password reset instructions have been sent to the email address associated with this account.",
				);
			}
		} else {
			$error = _("Please wait 15 minutes before sending a new request.");
		}
	} else {
		# Prevent user enumeration and let hackers guess username and working email
		$error = _(
			"Password reset instructions have been sent to the email address associated with this account.",
		);
	}
	unset($output);
}

if (!empty($_POST["user"]) && !empty($_POST["code"]) && !empty($_POST["password"])) {
	// Check token
	verify_csrf($_POST);
	if ($_POST["password"] == $_POST["password_confirm"]) {
		$v_user = quoteshellarg($_POST["user"]);
		$user = $_POST["user"];
		exec(HESTIA_CMD . "v-list-user " . $v_user . " json", $output, $return_var);
		if ($return_var == 0) {
			$data = json_decode(implode("", $output), true);
			$rkey = $data[$user]["RKEY"];
			if (password_verify($_POST["code"], $rkey)) {
				unset($output);
				exec(HESTIA_CMD . "v-get-user-value " . $v_user . " RKEYEXP", $output, $return_var);
				if ($output[0] > time() - 900) {
					$v_password = tempnam("/tmp", "vst");
					$fp = fopen($v_password, "w");
					fwrite($fp, $_POST["password"] . "\n");
					fclose($fp);
					exec(
						HESTIA_CMD . "v-change-user-password " . $v_user . " " . $v_password,
						$output,
						$return_var,
					);
					unlink($v_password);
					if ($return_var > 0) {
						sleep(5);
						$error = _("An internal error occurred");
					} else {
						$_SESSION["user"] = $_POST["user"];
						header("Location: /");
						exit();
					}
				} else {
					sleep(5);
					$error = _("Code has been expired");
					exec(
						HESTIA_CMD .
							"v-log-user-login " .
							$v_user .
							" " .
							$v_ip .
							" failed " .
							$v_session_id .
							" " .
							$v_user_agent .
							' yes "Reset code has been expired"',
						$output,
						$return_var,
					);
				}
			} else {
				sleep(5);
				$error = _("Invalid username or code");
				exec(
					HESTIA_CMD .
						"v-log-user-login " .
						$v_user .
						" " .
						$v_ip .
						" failed " .
						$v_session_id .
						" " .
						$v_user_agent .
						' yes "Invalid Username or Code"',
					$output,
					$return_var,
				);
			}
		} else {
			sleep(5);
			$error = _("Invalid username or code");
		}
	} else {
		$error = _("Passwords do not match");
	}
}

if (empty($_GET["action"])) {
	require_once "../templates/header.php";
	require_once "../templates/pages/login/reset_1.php";
} else {
	require_once "../templates/header.php";
	if ($_GET["action"] == "code") {
		require_once "../templates/pages/login/reset_2.php";
	}
	if ($_GET["action"] == "confirm" && !empty($_GET["code"])) {
		require_once "../templates/pages/login/reset_3.php";
	}
}
