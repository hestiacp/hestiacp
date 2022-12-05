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
				$name = $data[$user]["NAME"];
				$contact = $data[$user]["CONTACT"];
				$to = $data[$user]["CONTACT"];
				$subject = sprintf(_("MAIL_RESET_SUBJECT"), date("Y-m-d H:i:s"));
				$hostname = get_hostname();
				if ($hostname . ":" . $_SERVER["SERVER_PORT"] == $_SERVER["HTTP_HOST"]) {
					$check = true;
					$hostname_email = $hostname;
				} elseif ($hostname_full . ":" . $_SERVER["SERVER_PORT"] == $_SERVER["HTTP_HOST"]) {
					$check = true;
					$hostname_email = $hostname_full;
				} else {
					$check = false;
					$ERROR = "<p class=\"error\">" . _("Invalid host domain") . "</p>";
				}
				if ($check == true) {
					$from = "noreply@" . $hostname;
					$from_name = _("Hestia Control Panel");
					if (!empty($name)) {
						$mailtext = sprintf(_("GREETINGS_GORDON"), $name);
					} else {
						$mailtext = _("GREETINGS");
					}
					$mailtext .= sprintf(
						_("PASSWORD_RESET_REQUEST"),
						$_SERVER["HTTP_HOST"],
						$user,
						$rkey,
						$_SERVER["HTTP_HOST"],
						$user,
						$rkey,
					);
					if (!empty($rkey)) {
						send_email(
							$to,
							$subject,
							$mailtext,
							$from,
							$from_name,
							$data[$user]["NAME"],
						);
					}
					$ERROR =
						"<p class=\"error\">" .
						_(
							"A email has been send to the known email adress with the password reset instructions",
						) .
						"</p>";
				}
			} else {
				# Prevent user enumeration and let hackers guess username and working email
				$ERROR =
					"<p class=\"error\">" .
					_(
						"A email has been send to the known email adress with the password reset instructions",
					) .
					"</p>";
			}
		} else {
			$ERROR =
				"<p class=\"error\">" .
				_("Please wait 15 minutes before sending a new request") .
				"</p>";
		}
	} else {
		# Prevent user enumeration and let hackers guess username and working email
		$ERROR =
			"<p class=\"error\">" .
			_(
				"A email has been send to the known email adress with the password reset instructions",
			) .
			"</p>";
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
						$ERROR = "<p class=\"error\">" . _("An internal error occurred") . "</p>";
					} else {
						$_SESSION["user"] = $_POST["user"];
						header("Location: /");
						exit();
					}
				} else {
					sleep(5);
					$ERROR = "<p class=\"error\">" . _("Code has been expired") . "</p>";
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
				$ERROR = "<p class=\"error\">" . _("Invalid username or code") . "</p>";
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
			$ERROR = "<p class=\"error\">" . _("Invalid username or code") . "</p>";
		}
	} else {
		$ERROR = "<p class=\"error\">" . _("Passwords not match") . "</p>";
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
