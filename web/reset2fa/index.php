<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

define("NO_AUTH_REQUIRED", true);
$TAB = "RESET PASSWORD";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

if (isset($_SESSION["user"])) {
	header("Location: /list/user/");
	exit();
}

// Check CSRF token. Don't use the default redirect to /login/: it hides the
// reason from the user (e.g. page opened in a new browser / expired session)
$csrf_valid = true;
if (!empty($_POST) && !verify_csrf($_POST, true)) {
	$csrf_valid = false;
	$error = _("Your session has expired. Please try again.");
}

// The CSRF token is normally only generated on /login/, so make sure the
// form on this page always has one
if (empty($_SESSION["token"])) {
	$_SESSION["token"] = bin2hex(random_bytes(16));
}

//Check values
if ($csrf_valid && !empty($_POST["user"]) && !empty($_POST["twofa"])) {
	$error = _("Invalid username or code");
	$v_user = quoteshellarg($_POST["user"]);
	$user = $_POST["user"];
	$twofa = $_POST["twofa"];
	// Needed for v-log-user-login on failed attempts
	$v_ip = quoteshellarg(get_real_user_ip());
	$v_session_id = quoteshellarg($_POST["token"]);
	$v_user_agent = quoteshellarg($_SERVER["HTTP_USER_AGENT"] ?? "");
	exec(HESTIA_CMD . "v-list-user " . $v_user . " json", $output, $return_var);
	if ($return_var == 0) {
		$data = json_decode(implode("", $output), true);
		if ($data[$user]["TWOFA"] == $twofa) {
			$success = true;
			exec(HESTIA_CMD . "v-delete-user-2fa " . $v_user, $output, $return_var);
			session_destroy();
		} else {
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
					' yes "Failed to enter correct 2FA reset key"',
				$output,
				$return_var,
			);
			sleep(5);
		}
	} else {
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
				' yes "Failed to enter correct 2FA reset key"',
			$output,
			$return_var,
		);
		sleep(5);
	}
}

require_once "../templates/header.php";
require_once "../templates/pages/login/reset2fa.php";
require_once "../templates/includes/login-footer.php";
