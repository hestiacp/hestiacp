<?php

/* Hestia way to enable support for SSO to PHPmyAdmin */
/* To install please run v-add-sys-pma-sso */

/* Following keys will get replaced when calling v-add-sys-pma-sso */
define("PHPMYADMIN_KEY", "%PHPMYADMIN_KEY%");
define("API_HOST_NAME", "%API_HOST_NAME%");
define("API_HESTIA_PORT", "%API_HESTIA_PORT%");
define("API_KEY", "%API_KEY%");

class Hestia_API {
	/** @var string */
	public $hostname;
	/** @var string */
	public $key;
	/** @var string */
	public $pma_key;
	/** @var string */
	private $api_url;
	public function __construct() {
		$this->hostname = "https://" . API_HOST_NAME . ":" . API_HESTIA_PORT . "/api/";
		$this->key = API_KEY;
		$this->pma_key = PHPMYADMIN_KEY;
	}

	/* Creates curl request */
	public function request($postvars) {
		$postdata = http_build_query($postvars);
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $this->hostname);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
		$answer = curl_exec($curl);
		return $answer;
	}

	/* Creates an new temp user in mysql */
	public function create_temp_user($database, $user, $host) {
		$post_request = [
			"hash" => $this->key,
			"returncode" => "no",
			"cmd" => "v-add-database-temp-user",
			"arg1" => $user,
			"arg2" => $database,
			"arg3" => "mysql",
			"arg4" => $host,
		];
		$request = $this->request($post_request);
		// json_decode() returns null on parse failure. The credential check rejects
		// that result and valid JSON responses that lack credentials.
		$json = is_string($request) ? json_decode($request) : null;
		if (
			!is_string($json->login->user ?? null) ||
			$json->login->user === "" ||
			!is_string($json->login->password ?? null) ||
			$json->login->password === ""
		) {
			if (!is_string($request) || json_last_error() !== JSON_ERROR_NONE) {
				trigger_error(
					"Unable to connect over API please check api connection",
					E_USER_WARNING,
				);
			}
			return false;
		}
		return $json;
	}

	/* Delete an new temp user in mysql */
	public function delete_temp_user($database, $user, $dbuser, $host) {
		$post_request = [
			"hash" => $this->key,
			"returncode" => "yes",
			"cmd" => "v-delete-database-temp-user",
			"arg1" => $user,
			"arg2" => $database,
			"arg3" => $dbuser,
			"arg4" => "mysql",
			"arg5" => $host,
		];
		$request = $this->request($post_request);
		if (is_numeric($request) && $request == 0) {
			return true;
		} else {
			return false;
		}
	}

	public function get_user_ip() {
		// Saving user IPs to the session for preventing session hijacking
		$user_combined_ip = [];
		if ($_SERVER["REMOTE_ADDR"] != $_SERVER["SERVER_ADDR"]) {
			$user_combined_ip[] = $_SERVER["REMOTE_ADDR"];
		}
		if (isset($_SERVER["HTTP_CLIENT_IP"])) {
			$user_combined_ip .= "|" . $_SERVER["HTTP_CLIENT_IP"];
		}
		if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
			if ($_SERVER["REMOTE_ADDR"] != $_SERVER["HTTP_X_FORWARDED_FOR"]) {
				$user_combined_ip[] = $_SERVER["HTTP_X_FORWARDED_FOR"];
			}
		}
		if (isset($_SERVER["HTTP_FORWARDED_FOR"])) {
			if ($_SERVER["REMOTE_ADDR"] != $_SERVER["HTTP_FORWARDED_FOR"]) {
				$user_combined_ip[] = $_SERVER["HTTP_FORWARDED_FOR"];
			}
		}
		if (isset($_SERVER["HTTP_X_FORWARDED"])) {
			if ($_SERVER["REMOTE_ADDR"] != $_SERVER["HTTP_X_FORWARDED"]) {
				$user_combined_ip[] = $_SERVER["HTTP_X_FORWARDED"];
			}
		}
		if (isset($_SERVER["HTTP_FORWARDED"])) {
			if ($_SERVER["REMOTE_ADDR"] != $_SERVER["HTTP_FORWARDED"]) {
				$user_combined_ip[] = "|" . $_SERVER["HTTP_FORWARDED"];
			}
		}
		if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
			if (!empty($_SERVER["HTTP_CF_CONNECTING_IP"])) {
				$user_combined_ip[] = $_SERVER["HTTP_CF_CONNECTING_IP"];
			}
		}
		return implode("|", $user_combined_ip);
	}
}

/*
 * Keyed signature over every field the request carries, including host.
 *
 * This must never fall back to bcrypt-based password hashing: bcrypt only
 * considers the first 72 bytes of its input. With the key appended last, a long
 * attacker-controlled field (e.g. an oversized "exp") could push the key past
 * byte 72 so it was ignored, letting a token be forged offline without knowing
 * the key. HMAC-SHA256 has no such length limit, covers the whole message, and
 * is compared in constant time with hash_equals().
 */
function sso_expected_token($database, $user, $ip, $time, $host) {
	return hash_hmac(
		"sha256",
		implode("\0", [$database, $user, $ip, (string) $time, $host]),
		PHPMYADMIN_KEY,
	);
}

function verify_token($database, $user, $ip, $time, $host, $token): bool {
	// get_user_ip() may or may not prefix the server address, so accept both.
	foreach ([$ip, $_SERVER["SERVER_ADDR"] . "|" . $ip] as $candidate_ip) {
		$expected = sso_expected_token($database, $user, $candidate_ip, $time, $host);
		if (hash_equals($expected, $token)) {
			return true;
		}
	}
	trigger_error("Access denied: There is a security token mismatch " . $time, E_USER_WARNING);
	return false;
}
/* Need to have cookie visible from parent directory */
session_set_cookie_params(0, "/", "", true, true);
/* Create signon session */
$session_name = "SignonSession";
session_name($session_name);
@session_start();

function signon_failure(string $message): void {
	http_response_code(403);
	// Keep the signed URL out of referrer headers for assets on the error page.
	header("Referrer-Policy: no-referrer");
	// Load phpMyAdmin's renderer directly to avoid restarting authentication.
	$constants = __DIR__ . "/libraries/constants.php";
	if (is_readable($constants)) {
		try {
			define("ROOT_PATH", __DIR__ . DIRECTORY_SEPARATOR);
			define("PHPMYADMIN", true);
			require_once $constants;
			if (defined("AUTOLOAD_FILE") && is_readable(AUTOLOAD_FILE)) {
				require_once AUTOLOAD_FILE;
				$GLOBALS["config"] = null;
				$GLOBALS["cfg"] = ["environment" => "production"];
				$GLOBALS["containerBuilder"] = \PhpMyAdmin\Core::getContainerBuilder();
				// The sign-on tab needs the HTML page. The AJAX flag would select JSON.
				unset($_REQUEST["ajax_request"]);
				header("Content-Type: text/html; charset=UTF-8");
				\PhpMyAdmin\Core::fatalError($message);
			}
		} catch (\Throwable) {
			error_log("phpMyAdmin could not render the sign-in error page.");
		}
	}

	// Keep the message visible if phpMyAdmin's renderer fails to load.
	header("Content-Type: text/plain; charset=UTF-8");
	exit($message);
}

function session_invalid(?string $message = null) {
	global $session_name;
	// Clear the sign-on session and its root-path cookie to stop authentication retries.
	session_destroy();
	setcookie($session_name, "", 1, "/");
	if ($message !== null) {
		signon_failure($message);
	}
	header("Location: " . dirname($_SERVER["PHP_SELF"]) . "/index.php");
	die();
}

/* Find the phpMyAdmin server index in config.inc.php
 that corresponds to the host received from Hestia */
function find_pma_server_id($host) {
	$cfg = [];
	(function () use (&$cfg) {
		require "/etc/phpmyadmin/config.inc.php";
	})();
	if (empty($cfg["Servers"])) {
		return 1;
	}
	$wanted = strtolower(trim($host));
	foreach ($cfg["Servers"] as $id => $server) {
		if (!isset($server["host"])) {
			continue;
		}
		$cfg_host = strtolower(trim($server["host"]));
		if ($cfg_host === $wanted) {
			return $id;
		}
		// in case "localhost" comes in as 127.0.0.1 or vice versa
		if (
			in_array($wanted, ["localhost", "127.0.0.1"], true) &&
			in_array($cfg_host, ["localhost", "127.0.0.1"], true)
		) {
			return $id;
		}
	}
	return 1; // fallback: localhost
}

$api = new Hestia_API();
$invalid_link = "The phpMyAdmin sign-in link is invalid. Open phpMyAdmin again from Databases.";
if (!empty($_GET)) {
	if (isset($_GET["logout"])) {
		$api->delete_temp_user(
			$_SESSION["HESTIA_sso_database"],
			$_SESSION["HESTIA_sso_user"],
			$_SESSION["PMA_single_signon_user"],
			$_SESSION["HESTIA_sso_host"],
		);
		//remove session
		session_invalid();
	} else {
		if (isset($_GET["user"]) && isset($_GET["hestia_token"])) {
			$database = $_GET["database"] ?? null;
			$user = $_GET["user"];
			$host = !empty($_GET["host"]) ? $_GET["host"] : "localhost";
			$token = $_GET["hestia_token"];
			$exp = $_GET["exp"] ?? null;
			// The HMAC uses NUL separators. Reject them in signed fields before verification.
			foreach ([$database, $user, $host, $token, $exp] as $value) {
				if (!is_string($value) || $value === "" || strpos($value, "\0") !== false) {
					session_invalid($invalid_link);
				}
			}
			// Distinguish malformed timestamps from links past their 60-second lifetime.
			if (!ctype_digit($exp) || strlen($exp) > 12) {
				session_invalid($invalid_link);
			}
			$time = (int) $exp;

			if ($time + 60 > time()) {
				//note: Possible issues with cloudflare due to ip obfuscation
				$ip = $api->get_user_ip();
				if (!verify_token($database, $user, $ip, $time, $host, $token)) {
					session_invalid($invalid_link);
				}
				$id = session_id();
				//create a new temp user
				$data = $api->create_temp_user($database, $user, $host);
				if ($data) {
					$_SESSION["PMA_single_signon_user"] = $data->login->user;
					$_SESSION["PMA_single_signon_password"] = $data->login->password;
					$_SESSION["PMA_single_signon_host"] = $host;
					//save database / username to be used for sending logout notification.
					$_SESSION["HESTIA_sso_user"] = $user;
					$_SESSION["HESTIA_sso_database"] = $database;
					$_SESSION["HESTIA_sso_host"] = $host;

					$server_id = find_pma_server_id($host);

					@session_write_close();
					setcookie($session_name, $id, 0, "/");
					header(
						"Location: " .
							dirname($_SERVER["PHP_SELF"]) .
							"/index.php?server=" .
							$server_id,
					);
					die();
				} else {
					session_invalid(
						"Hestia could not prepare temporary access to this database for phpMyAdmin.",
					);
				}
			} else {
				trigger_error(
					"Link has been expired: System time: " .
						time() .
						" / Time provided in link: " .
						$time,
					E_USER_WARNING,
				);
				session_invalid(
					"The phpMyAdmin sign-in request expired. Open phpMyAdmin again from Databases.",
				);
			}
		} else {
			session_invalid($invalid_link);
		}
	}
} else {
	// phpMyAdmin stores the MySQL error before redirecting to SignonURL.
	// Use the flag to identify the failure while keeping the connection details private.
	if (isset($_SESSION["PMA_single_signon_error_message"])) {
		session_invalid(
			"phpMyAdmin could not connect using the temporary database credentials. Open phpMyAdmin again from Databases.",
		);
	}
	session_invalid();
}
