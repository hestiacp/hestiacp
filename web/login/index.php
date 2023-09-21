<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

define("NO_AUTH_REQUIRED", true);
// Main include

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

$TAB = "LOGIN";

if (isset($_GET["logout"])) {
	unset($_SESSION);
	session_unset();
	session_destroy();
	header("Location: /login/");
}

/* ACTIONS FOR CURRENT USER SESSION */
if (isset($_SESSION["user"])) {
	// User impersonation
	// Allow administrators to view and manipulate contents of other user accounts
	if ($_SESSION["userContext"] === "admin" && !empty($_GET["loginas"])) {
		// Ensure token is passed and matches before granting user impersonation access
		if (verify_csrf($_GET)) {
			$v_user = quoteshellarg($_GET["loginas"]);
			$v_impersonator = quoteshellarg($_SESSION["user"]);
			exec(HESTIA_CMD . "v-list-user " . $v_user . " json", $output, $return_var);
			if ($return_var == 0) {
				$data = json_decode(implode("", $output), true);
				reset($data);
				$_SESSION["look"] = key($data);
				// Log impersonation events
				exec(
					HESTIA_CMD .
						"v-log-action " .
						$v_impersonator .
						" 'Info' 'Security' 'Logged in as another user (User: $v_user)'",
					$output,
					$return_var,
				);
				exec(
					HESTIA_CMD .
						"v-log-action system 'Warning' 'Security' 'User impersonation session started (User: $v_user, Administrator: $v_impersonator)'",
					$output,
					$return_var,
				);
				// Reset account details for File Manager to impersonated user
				unset($_SESSION["_sf2_attributes"]);
				unset($_SESSION["_sf2_meta"]);
				if (!empty($_GET["edit_link"])) {
					$edit_link = urldecode($_GET["edit_link"]);
					$url = $edit_link . "&token=" . $_SESSION["token"];
					header("Location: " . $url);
					die();
				}
				header("Location: /login/");
			} else {
				# User doesn't exists
				header("Location: /");
			}
		}
		exit();
	}

	// Set view based on account properties
	if (empty($_GET["loginas"])) {
		// Default view to Users list for administrator accounts
		if ($_SESSION["userContext"] === "admin" && !isset($_SESSION["look"])) {
			header("Location: /list/user/");
			exit();
		}

		// Obtain account properties
		$v_user = quoteshellarg(
			$_SESSION[
				$_SESSION["userContext"] === "admin" && $_SESSION["look"] !== "" ? "look" : "user"
			],
		);

		exec(HESTIA_CMD . "v-list-user " . $v_user . " json", $output, $return_var);
		$data = json_decode(implode("", $output), true);
		unset($output);

		// Determine package features and land user at the first available page
		if ($data[$user_plain]["WEB_DOMAINS"] !== "0") {
			header("Location: /list/web/");
		} elseif ($data[$user_plain]["DNS_DOMAINS"] !== "0") {
			header("Location: /list/dns/");
		} elseif ($data[$user_plain]["MAIL_DOMAINS"] !== "0") {
			header("Location: /list/mail/");
		} elseif ($data[$user_plain]["DATABASES"] !== "0") {
			header("Location: /list/db/");
		} elseif ($data[$user_plain]["CRON_JOBS"] !== "0") {
			header("Location: /list/cron/");
		} elseif ($data[$user_plain]["BACKUPS"] !== "0") {
			header("Location: /list/backup/");
		} else {
			header("Location: /error/");
		}
		exit();
	}

	// Do not allow non-administrators to access account impersonation
	if ($_SESSION["userContext"] !== "admin" && !empty($_GET["loginas"])) {
		header("Location: /login/");
		exit();
	}

	exit();
}

function authenticate_user($user, $password, $twofa = "") {
	unset($_SESSION["login"]);
	if (verify_csrf($_POST, true)) {
		$v_user = quoteshellarg($user);
		$ip = $_SERVER["REMOTE_ADDR"];
		$user_agent = $_SERVER["HTTP_USER_AGENT"];
		if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
			if (!empty($_SERVER["HTTP_CF_CONNECTING_IP"])) {
				$ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
			}
		}
		$v_ip = quoteshellarg($ip);
		$v_user_agent = quoteshellarg($user_agent);

		// Get user's salt
		$output = "";
		exec(
			HESTIA_CMD . "v-get-user-salt " . $v_user . " " . $v_ip . " json",
			$output,
			$return_var,
		);
		$pam = json_decode(implode("", $output), true);
		unset($output);
		if ($return_var > 0) {
			sleep(2);
			if ($return_var == 5) {
				$error = _("Account has been suspended");
			} elseif ($return_var == 1) {
				$error = _("Unsupported hash method");
			} else {
				$error = _("Invalid username or password");
			}
			return $error;
		} else {
			$salt = $pam[$user]["SALT"];
			$method = $pam[$user]["METHOD"];

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
				$hash = $output[0];
				fclose($fp);
				unset($output, $fp, $v_password);
			}
			if ($method == "des") {
				$hash = crypt($password, $salt);
			}

			// Send hash via tmp file
			$v_hash = exec("mktemp -p /tmp");
			$fp = fopen($v_hash, "w");
			fwrite($fp, $hash . "\n");
			fclose($fp);

			// Check user hash
			exec(
				HESTIA_CMD . "v-check-user-hash " . $v_user . " " . $v_hash . " " . $v_ip,
				$output,
				$return_var,
			);
			unset($output);

			// Remove tmp file
			unlink($v_hash);
			// Check API answer
			if ($return_var > 0) {
				sleep(2);
				$error = _("Invalid username or password");
				$v_session_id = quoteshellarg($_POST["token"]);
				exec(
					HESTIA_CMD .
						"v-log-user-login " .
						$v_user .
						" " .
						$v_ip .
						" failed " .
						$v_session_id .
						" " .
						$v_user_agent,
					$output,
					$return_var,
				);
				return $error;
			} else {
				// Get user specific parameters
				exec(HESTIA_CMD . "v-list-user " . $v_user . " json", $output, $return_var);
				$data = json_decode(implode("", $output), true);
				unset($output);
				if ($data[$user]["LOGIN_DISABLED"] === "yes") {
					sleep(2);
					$error = _("Invalid username or password");
					$v_session_id = quoteshellarg($_POST["token"]);
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
							' yes "Login disabled for this user"',
						$output,
						$return_var,
					);
					return $error;
				}

				if ($data[$user]["LOGIN_USE_IPLIST"] === "yes") {
					$v_login_user_allowed_ips = explode(",", $data[$user]["LOGIN_ALLOW_IPS"]);
					$v_login_user_allowed_ips = array_map("trim", $v_login_user_allowed_ips);
					if (!in_array($ip, $v_login_user_allowed_ips, true)) {
						sleep(2);
						$error = _("Invalid username or password");
						$v_session_id = quoteshellarg($_POST["token"]);
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
								' yes "IP address not in allowed list"',
							$output,
							$return_var,
						);
						return $error;
					}
				}

				if ($data[$user]["TWOFA"] != "") {
					exec(
						HESTIA_CMD . "v-check-user-2fa " . $v_user . " " . $v_twofa,
						$output,
						$return_var,
					);
					$error = _("Invalid or missing 2FA token");
					if (empty($twofa)) {
						$_SESSION["login"]["username"] = $user;
						$_SESSION["login"]["password"] = $password;
						return false;
					} else {
						$v_twofa = quoteshellarg($twofa);
						exec(
							HESTIA_CMD . "v-check-user-2fa " . $v_user . " " . $v_twofa,
							$output,
							$return_var,
						);
						unset($output);
						if ($return_var > 0) {
							sleep(2);
							$error = _("Invalid or missing 2FA token");
							$_SESSION["login"]["username"] = $user;
							$_SESSION["login"]["password"] = $password;
							$v_session_id = quoteshellarg($_POST["token"]);
							if (isset($_SESSION["failed_twofa"])) {
								//allow a few failed attemps before start of logging.
								if ($_SESSION["failed_twofa"] > 2) {
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
											' yes "Invalid or missing 2FA token"',
										$output,
										$return_var,
									);
								}
								$_SESSION["failed_twofa"]++;
							} else {
								$_SESSION["failed_twofa"] = 1;
							}
							unset($_POST["twofa"]);
							return $error;
						}
					}
				}

				// Define session user
				$_SESSION["user"] = key($data);
				$v_user = $_SESSION["user"];
				//log successfull login attempt
				$v_session_id = quoteshellarg($_POST["token"]);
				exec(
					HESTIA_CMD .
						"v-log-user-login " .
						$v_user .
						" " .
						$v_ip .
						" success " .
						$v_session_id .
						" " .
						$v_user_agent,
					$output,
					$return_var,
				);

				$_SESSION["LAST_ACTIVITY"] = time();

				// Define user role / context
				$_SESSION["userContext"] = $data[$user]["ROLE"];

				// Set active user theme on login
				$_SESSION["userTheme"] = $data[$user]["THEME"];
				if ($_SESSION["POLICY_USER_CHANGE_THEME"] !== "yes") {
					unset($_SESSION["userTheme"]);
				}

				$_SESSION["userSortOrder"] = !empty($data[$user]["PREF_UI_SORT"])
					? $data[$user]["PREF_UI_SORT"]
					: "name";

				// Define language
				$output = "";
				exec(HESTIA_CMD . "v-list-sys-languages json", $output, $return_var);
				$languages = json_decode(implode("", $output), true);
				$_SESSION["language"] = in_array($data[$v_user]["LANGUAGE"], $languages)
					? $data[$user]["LANGUAGE"]
					: "en";

				// Regenerate session id to prevent session fixation
				session_regenerate_id(true);

				// Redirect request to control panel interface
				if (!empty($_SESSION["request_uri"])) {
					header("Location: " . $_SESSION["request_uri"]);
					unset($_SESSION["request_uri"]);
					exit();
				} else {
					if ($_SESSION["userContext"] === "admin") {
						header("Location: /list/user/");
					} else {
						if ($data[$user]["WEB_DOMAINS"] != "0") {
							header("Location: /list/web/");
						} elseif ($data[$user]["DNS_DOMAINS"] != "0") {
							header("Location: /list/dns/");
						} elseif ($data[$user]["MAIL_DOMAINS"] != "0") {
							header("Location: /list/mail/");
						} elseif ($data[$user]["DATABASES"] != "0") {
							header("Location: /list/db/");
						} elseif ($data[$user]["CRON_JOBS"] != "0") {
							header("Location: /list/cron/");
						} elseif ($data[$user]["BACKUPS"] != "0") {
							header("Location: /list/backup/");
						} else {
							header("Location: /error/");
						}
					}
					exit();
				}
			}
		}
	} else {
		unset($_POST);
		unset($_GET);
		unset($_SESSION);
		// Delete old session and start a new one
		session_write_close();
		session_unset();
		session_destroy();
		session_start();
		return false;
	}
}
if (empty($_POST["user"])) {
	$user = "";
} else {
	if (preg_match('/^[[:alnum:]][-|\.|_[:alnum:]]{0,28}[[:alnum:]]$/', $_POST["user"])) {
		$_SESSION["login"]["username"] = $_POST["user"];
	} else {
		$user = "";
	}
}
if (
	!empty($_SESSION["login"]["username"]) &&
	!empty($_SESSION["login"]["password"]) &&
	!empty($_POST["twofa"])
) {
	$error = authenticate_user(
		$_SESSION["login"]["username"],
		$_SESSION["login"]["password"],
		$_POST["twofa"],
	);
	unset($_POST);
} elseif (!empty($_SESSION["login"]["username"]) && !empty($_POST["password"])) {
	$error = authenticate_user($_SESSION["login"]["username"], $_POST["password"]);
	unset($_POST);
}
// Check system configuration
load_hestia_config();

// Detect language
if (empty($_SESSION["language"])) {
	$output = "";
	exec(HESTIA_CMD . "v-list-sys-config json", $output, $return_var);
	$config = json_decode(implode("", $output), true);
	$lang = $config["config"]["LANGUAGE"];

	$output = "";
	exec(HESTIA_CMD . "v-list-sys-languages json", $output, $return_var);
	$languages = json_decode(implode("", $output), true);
	$_SESSION["language"] = in_array($lang, $languages) ? $lang : "en";
}

// Generate CSRF token
$token = bin2hex(random_bytes(16));
$_SESSION["token"] = $token;

require_once "../templates/header.php";
if (!empty($_SESSION["login"]["password"])) {
	require_once "../templates/pages/login/login_2.php";
} elseif (empty($_SESSION["login"]["username"])) {
	require_once "../templates/pages/login/login" .
		($_SESSION["LOGIN_STYLE"] != "old" ? "" : "_a") .
		".php";
} elseif (empty($_POST["password"])) {
	require_once "../templates/pages/login/login_1.php";
} else {
	require_once "../templates/pages/login/login" .
		($_SESSION["LOGIN_STYLE"] != "old" ? "" : "_a") .
		".php";
}
