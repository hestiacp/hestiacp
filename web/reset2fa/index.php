<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

define("NO_AUTH_REQUIRED", true);
$TAB = "RESET PASSWORD";

if (isset($_SESSION["user"])) {
	header("Location: /list/user");
}

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check values
if (!empty($_POST["user"]) && !empty($_POST["password"]) && !empty($_POST["twofa"])) {
	// Check token
	verify_csrf($_POST);
	$v_user = quoteshellarg($_POST["user"]);
	$user = $_POST["user"];
	$password = $_POST["password"];
	// Backup codes are often copied with spaces or dashes
	$backup_code = strtoupper(str_replace([" ", "-"], "", $_POST["twofa"]));

	$ip = get_real_user_ip();
	$v_ip = quoteshellarg($ip);
	$v_user_agent = quoteshellarg($_SERVER["HTTP_USER_AGENT"] ?? "");

	$password_ok = false;
	$backup_code_ok = false;

	exec(HESTIA_CMD . "v-list-user " . $v_user . " json", $output, $return_var);
	if ($return_var == 0) {
		$data = json_decode(implode("", $output), true);
		unset($output);

		if (!empty($data[$user]["TWOFA"])) {
			// Verify the account password using the same pipeline as the normal login form
			exec(
				HESTIA_CMD . "v-get-user-salt " . $v_user . " " . $v_ip . " json",
				$output,
				$return_var,
			);
			$pam = json_decode(implode("", $output), true);
			unset($output);

			if ($return_var == 0) {
				$salt = $pam[$user]["SALT"];
				$method = $pam[$user]["METHOD"];
				$hash = "";

				if ($method == "md5") {
					$hash = crypt($password, '$1$' . $salt . '$');
				}
				if ($method == "sha-512") {
					$hash = crypt($password, '$6$rounds=5000$' . $salt . '$');
					$hash = str_replace('$rounds=5000', "", $hash);
				}
				if ($method == "yescrypt") {
					$fp = tmpfile();
					$v_password = stream_get_meta_data($fp)["uri"];
					fwrite($fp, $password . "\n");
					exec(
						HESTIA_CMD .
							"v-check-user-password " .
							$v_user .
							" " .
							quoteshellarg($v_password) .
							" " .
							$v_ip .
							" yes",
						$output,
						$return_var,
					);
					$hash = $output[0] ?? "";
					fclose($fp);
					unset($output, $fp, $v_password);
				}
				if ($method == "des") {
					$hash = crypt($password, $salt);
				}

				if ($hash !== "") {
					$v_hash = exec("mktemp -p /tmp");
					$fp = fopen($v_hash, "w");
					fwrite($fp, $hash . "\n");
					fclose($fp);

					exec(
						HESTIA_CMD . "v-check-user-hash " . $v_user . " " . $v_hash . " " . $v_ip,
						$output,
						$return_var,
					);
					unset($output);
					unlink($v_hash);

					$password_ok = $return_var == 0;
				}
			}

			// Only spend a backup code once the password has already matched
			if ($password_ok) {
				exec(
					HESTIA_CMD .
						"v-check-user-2fa-backup-code " .
						$v_user .
						" " .
						quoteshellarg($backup_code),
					$output,
					$return_var,
				);
				unset($output);
				$backup_code_ok = $return_var == 0;
			}
		}
	}

	if ($password_ok && $backup_code_ok) {
		$success = true;
		exec(HESTIA_CMD . "v-delete-user-2fa " . $v_user, $output, $return_var);
		unset($output);
		// Disabling 2FA via account recovery is a security-relevant event -
		// leave a record the user (or an admin looking at their account)
		// will see even if they miss the confirmation page.
		exec(
			HESTIA_CMD .
				"v-add-user-notification " .
				$v_user .
				" " .
				quoteshellarg(_("Two-factor authentication disabled via account recovery")) .
				" " .
				quoteshellarg(
					_(
						"Two-factor authentication was disabled for your account using the account recovery form (password and backup code). If this wasn't you, secure your account and contact your administrator immediately.",
					),
				),
			$output,
			$return_var,
		);
		unset($output);
		session_destroy();
	} else {
		// Deliberately the same message and delay whether the account, the
		// password, or the backup code was wrong - this avoids telling an
		// attacker which one of the three failed.
		$error = _("Invalid username, password, or backup code.");
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
				' yes "Failed 2FA account recovery attempt"',
			$output,
			$return_var,
		);
		unset($output);
		sleep(5);
	}
}

require_once "../templates/header.php";
require_once "../templates/pages/login/reset2fa.php";
require_once "../templates/includes/login-footer.php";
