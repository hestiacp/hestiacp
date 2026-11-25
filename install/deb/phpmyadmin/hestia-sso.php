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
		curl_setopt($curl, CURLOPT_FAILONERROR, true);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($curl, CURLOPT_TIMEOUT, 15);
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
		$response = $this->request($post_request);
		$data = is_string($response) ? json_decode($response) : null;
		if (
			!is_string($data->login->user ?? null) ||
			$data->login->user === "" ||
			!is_string($data->login->password ?? null) ||
			$data->login->password === ""
		) {
			error_log("phpMyAdmin SSO could not obtain temporary database credentials from the Hestia API.");
			return false;
		}
		return $data->login;
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
			$user_combined_ip[] = $_SERVER["HTTP_CLIENT_IP"];
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

function verify_token($database, $user, $ip, $time, $host, $token) {
	// get_user_ip() may or may not prefix the server address, so accept both.
	foreach ([$ip, $_SERVER["SERVER_ADDR"] . "|" . $ip] as $candidate_ip) {
		$expected = sso_expected_token($database, $user, $candidate_ip, $time, $host);
		if (hash_equals($expected, $token)) {
			return true;
		}
	}
	return false;
}

function valid_panel_origin($origin): bool {
	return
		is_string($origin) &&
		strlen($origin) <= 270 &&
		preg_match('/\Ahttps:\/\/[a-z0-9][a-z0-9.-]*(?::([0-9]{1,5}))?\z/i', $origin, $parts) === 1 &&
		(!isset($parts[1]) || ((int) $parts[1] > 0 && (int) $parts[1] <= 65535));
}

function authorize_panel_request(array $input): bool {
	$origin = $_SERVER["HTTP_ORIGIN"] ?? null;
	$token = $input["hestia_token"] ?? null;
	$signature = $input["panel_signature"] ?? null;
	if (
		valid_panel_origin($origin) &&
		is_string($token) && preg_match('/\A[a-f0-9]{64}\z/', $token) === 1 &&
		is_string($signature) &&
		hash_equals(
			hash_hmac("sha256", "phpmyadmin-ajax\0" . $origin . "\0" . $token, PHPMYADMIN_KEY),
			$signature,
		)
	) {
		header("Access-Control-Allow-Origin: " . $origin);
		header("Access-Control-Allow-Credentials: true");
		return true;
	}
	return false;
}

function start_signon_session(): bool {
	// The phpMyAdmin index must receive the same secure cookie as the sign-on endpoint.
	session_set_cookie_params(0, "/", "", true, true);
	session_name("SignonSession");
	return @session_start(["use_strict_mode" => 1]);
}

function clear_signon_session() {
	if (session_status() === PHP_SESSION_ACTIVE) {
		$_SESSION = [];
		session_destroy();
	}
	setcookie("SignonSession", "", 1, "/", "", true, true);
}

function signon_failure(string $reason, bool $ajax) {
	if ($ajax) {
		header("Content-Type: application/json");
		echo json_encode(["error" => $reason]);
	} else {
		header("Location: index.php");
	}
	exit;
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

header("Cache-Control: no-store");
$ajax = ($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST";
$input = $ajax ? $_POST : $_GET;
if ($ajax) {
	header("Vary: Origin");
	if (!authorize_panel_request($input)) {
		http_response_code(403);
		exit;
	}
}

$api = new Hestia_API();
if (!$ajax && isset($_GET["logout"])) {
	start_signon_session();
	if (
		isset(
			$_SESSION["HESTIA_sso_database"],
			$_SESSION["HESTIA_sso_user"],
			$_SESSION["PMA_single_signon_user"],
			$_SESSION["HESTIA_sso_host"],
		)
	) {
		$api->delete_temp_user(
			$_SESSION["HESTIA_sso_database"],
			$_SESSION["HESTIA_sso_user"],
			$_SESSION["PMA_single_signon_user"],
			$_SESSION["HESTIA_sso_host"],
		);
	}
	clear_signon_session();
	header("Location: index.php");
	exit;
}

if (
	!$ajax &&
	!isset($_GET["hestia_token"]) &&
	!isset($_GET["user"]) &&
	!isset($_GET["database"]) &&
	!isset($_GET["exp"])
) {
	// phpMyAdmin redirects here with no parameters after rejecting sign-on.
	start_signon_session();
	if (isset($_SESSION["PMA_single_signon_error_message"])) {
		clear_signon_session();
		http_response_code(403);
		header("Content-Type: text/plain; charset=UTF-8");
		exit("Could not sign in to phpMyAdmin. Open phpMyAdmin again from Hestia.");
	}
	clear_signon_session();
	header("Location: index.php");
	exit;
}

$database = $input["database"] ?? null;
$user = $input["user"] ?? null;
$host = $input["host"] ?? "";
$token = $input["hestia_token"] ?? null;
$exp = $input["exp"] ?? null;
foreach ([$database, $user, $host, $token, $exp] as $value) {
	if (!is_string($value) || str_contains($value, "\0")) {
		signon_failure("invalid", $ajax);
	}
}
if ($database === "" || $user === "" || $token === "" || !ctype_digit($exp) || strlen($exp) > 12) {
	signon_failure("invalid", $ajax);
}
$host = $host === "" ? "localhost" : $host;
$time = (int) $exp;
if ($time + 60 <= time()) {
	signon_failure("expired", $ajax);
}
if (!verify_token($database, $user, $api->get_user_ip(), $time, $host, $token)) {
	signon_failure("invalid", $ajax);
}
// The SSO-only configuration uses this flag while loading the server definitions.
$_GET["hestia_token"] = $token;
$server_id = (int) find_pma_server_id($host);
$credentials = $api->create_temp_user($database, $user, $host);
if ($credentials === false) {
	signon_failure("credentials", $ajax);
}
if (!start_signon_session() || !session_regenerate_id(true)) {
	$api->delete_temp_user($database, $user, $credentials->user, $host);
	signon_failure("credentials", $ajax);
}

unset($_SESSION["PMA_single_signon_error_message"]);
$_SESSION["PMA_single_signon_user"] = $credentials->user;
$_SESSION["PMA_single_signon_password"] = $credentials->password;
$_SESSION["PMA_single_signon_host"] = $host;
$_SESSION["HESTIA_sso_user"] = $user;
$_SESSION["HESTIA_sso_database"] = $database;
$_SESSION["HESTIA_sso_host"] = $host;

if (!session_write_close()) {
	clear_signon_session();
	$api->delete_temp_user($database, $user, $credentials->user, $host);
	signon_failure("credentials", $ajax);
}
if ($ajax) {
	header("Content-Type: application/json");
	echo json_encode(["server" => $server_id]);
	exit;
}
header("Location: index.php?server=" . $server_id);
exit;
