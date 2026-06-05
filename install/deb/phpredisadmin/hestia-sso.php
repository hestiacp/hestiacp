<?php

define("PHPREDISADMIN_KEY", "%PHPREDISADMIN_KEY%");
define("API_HOST_NAME", "%API_HOST_NAME%");
define("API_HESTIA_PORT", "%API_HESTIA_PORT%");
define("API_KEY", "%API_KEY%");

class Hestia_Redis_API {
	public $hostname;
	public $key;
	public $pra_key;

	public function __construct() {
		$this->hostname = "https://" . API_HOST_NAME . ":" . API_HESTIA_PORT . "/api/";
		$this->key = API_KEY;
		$this->pra_key = PHPREDISADMIN_KEY;
	}

	public function request($postvars) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $this->hostname);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postvars));
		return curl_exec($curl);
	}

	public function create_temp_user($database, $user, $host, $port) {
		$request = $this->request([
			"hash" => $this->key,
			"returncode" => "no",
			"cmd" => "v-add-database-temp-user",
			"arg1" => $user,
			"arg2" => $database,
			"arg3" => "redis",
			"arg4" => $host,
			"arg5" => "60",
			"arg6" => $port,
		]);
		$json = json_decode($request);
		return json_last_error() === JSON_ERROR_NONE ? $json : false;
	}

	public function delete_temp_user($database, $user, $dbuser, $host, $port) {
		$request = $this->request([
			"hash" => $this->key,
			"returncode" => "yes",
			"cmd" => "v-delete-database-temp-user",
			"arg1" => $user,
			"arg2" => $database,
			"arg3" => $dbuser,
			"arg4" => "redis",
			"arg5" => $host,
			"arg6" => $port,
		]);
		return is_numeric($request) && $request == 0;
	}

	public function get_user_ip() {
		$user_combined_ip = [];
		if ($_SERVER["REMOTE_ADDR"] != $_SERVER["SERVER_ADDR"]) {
			$user_combined_ip[] = $_SERVER["REMOTE_ADDR"];
		}
		foreach (
			[
				"HTTP_CLIENT_IP",
				"HTTP_X_FORWARDED_FOR",
				"HTTP_FORWARDED_FOR",
				"HTTP_X_FORWARDED",
				"HTTP_FORWARDED",
				"HTTP_CF_CONNECTING_IP",
			]
			as $header
		) {
			if (!empty($_SERVER[$header]) && $_SERVER["REMOTE_ADDR"] != $_SERVER[$header]) {
				$user_combined_ip[] = $_SERVER[$header];
			}
		}
		return implode("|", $user_combined_ip);
	}
}

function session_invalid() {
	session_destroy();
	setcookie("HestiaRedisAdmin", null, -1, "/");
	header("Location: " . dirname($_SERVER["PHP_SELF"]) . "/index.php");
	die();
}

function verify_token($database, $user, $ip, $time, $token, $host, $port, $prefix) {
	if (
		!password_verify(
			$database . $user . $ip . $time . $host . $port . $prefix . PHPREDISADMIN_KEY,
			$token,
		)
	) {
		if (
			!password_verify(
				$database .
					$user .
					$_SERVER["SERVER_ADDR"] .
					"|" .
					$ip .
					$time .
					$host .
					$port .
					$prefix .
					PHPREDISADMIN_KEY,
				$token,
			)
		) {
			trigger_error("Access denied: there is a security token mismatch", E_USER_WARNING);
			session_invalid();
		}
	}
}

session_set_cookie_params(0, "/", "", true, true);
session_name("HestiaRedisAdmin");
@session_start();

$api = new Hestia_Redis_API();

if (isset($_GET["logout"])) {
	$api->delete_temp_user(
		$_SESSION["HESTIA_redis_database"],
		$_SESSION["HESTIA_redis_user"],
		$_SESSION["HestiaRedisAdmin_user"],
		$_SESSION["HestiaRedisAdmin_host"],
		$_SESSION["HestiaRedisAdmin_port"],
	);
	session_invalid();
}

if (
	!isset(
		$_GET["user"],
		$_GET["database"],
		$_GET["host"],
		$_GET["port"],
		$_GET["prefix"],
		$_GET["exp"],
		$_GET["hestia_token"],
	)
) {
	session_invalid();
}

$database = $_GET["database"];
$user = $_GET["user"];
$host = $_GET["host"];
$port = is_numeric($_GET["port"]) ? $_GET["port"] : "6379";
$prefix = $_GET["prefix"];
$time = is_numeric($_GET["exp"]) ? (int) $_GET["exp"] : 0;
$token = $_GET["hestia_token"];

if ($time + 60 <= time()) {
	session_invalid();
}

verify_token($database, $user, $api->get_user_ip(), $time, $token, $host, $port, $prefix);
$temp_user = $api->create_temp_user($database, $user, $host, $port);
if (!$temp_user) {
	session_invalid();
}

$_SESSION["HESTIA_redis_database"] = $database;
$_SESSION["HESTIA_redis_user"] = $user;
$_SESSION["HestiaRedisAdmin_host"] = $host;
$_SESSION["HestiaRedisAdmin_port"] = $port;
$_SESSION["HestiaRedisAdmin_prefix"] = $prefix;
$_SESSION["HestiaRedisAdmin_user"] = $temp_user->login->user;
$_SESSION["HestiaRedisAdmin_password"] = $temp_user->login->password;

header("Location: index.php");
