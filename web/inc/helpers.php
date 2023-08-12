<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

# Return codes
const E_ARGS = 1;
const E_INVALID = 2;
const E_NOTEXIST = 3;
const E_EXISTS = 4;
const E_SUSPENDED = 5;
const E_UNSUSPENDED = 6;
const E_INUSE = 7;
const E_LIMIT = 8;
const E_PASSWORD = 9;
const E_FORBIDEN = 10;
const E_FORBIDDEN = 10;
const E_DISABLED = 11;
const E_PARSING = 12;
const E_DISK = 13;
const E_LA = 14;
const E_CONNECT = 15;
const E_FTP = 16;
const E_DB = 17;
const E_RRD = 18;
const E_UPDATE = 19;
const E_RESTART = 20;
const E_API_DISABLED = 21;

/**
 * Looks for a code equivalent to "exit_code" to use in http_code.
 *
 * @param int $exit_code
 * @param int $default
 * @return int
 */
function exit_code_to_http_code(int $exit_code, int $default = 400): int {
	switch ($exit_code) {
		case 0:
			return 200;
		case E_ARGS:
			// return 500;
			return 400;
		case E_INVALID:
			return 422;
		// case E_NOTEXIST:
		// 	return 404;
		// case E_EXISTS:
		// 	return 302;
		case E_PASSWORD:
			return 401;
		case E_SUSPENDED:
		case E_UNSUSPENDED:
		case E_FORBIDEN:
		case E_FORBIDDEN:
		case E_API_DISABLED:
			return 401;
		// return 403;
		case E_DISABLED:
			return 400;
		// return 503;
	}

	return $default;
}

function check_local_ip($addr) {
	if (in_array($addr, [$_SERVER["SERVER_ADDR"], "127.0.0.1"])) {
		return true;
	} else {
		return false;
	}
}

function get_real_user_ip() {
	$ip = $_SERVER["REMOTE_ADDR"];
	if (isset($_SERVER["HTTP_CLIENT_IP"]) && !check_local_ip($_SERVER["HTTP_CLIENT_IP"])) {
		if (filter_var($_SERVER["HTTP_CLIENT_IP"], FILTER_VALIDATE_IP)) {
			$ip = $_SERVER["HTTP_CLIENT_IP"];
		}
	}

	if (
		isset($_SERVER["HTTP_X_FORWARDED_FOR"]) &&
		!check_local_ip($_SERVER["HTTP_X_FORWARDED_FOR"])
	) {
		if (filter_var($_SERVER["HTTP_X_FORWARDED_FOR"], FILTER_VALIDATE_IP)) {
			$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		}
	}

	if (isset($_SERVER["HTTP_FORWARDED_FOR"]) && !check_local_ip($_SERVER["HTTP_FORWARDED_FOR"])) {
		if (filter_var($_SERVER["HTTP_FORWARDED_FOR"], FILTER_VALIDATE_IP)) {
			$ip = $_SERVER["HTTP_FORWARDED_FOR"];
		}
	}

	if (isset($_SERVER["HTTP_X_FORWARDED"]) && !check_local_ip($_SERVER["HTTP_X_FORWARDED"])) {
		if (filter_var($_SERVER["HTTP_X_FORWARDED"], FILTER_VALIDATE_IP)) {
			$ip = $_SERVER["HTTP_X_FORWARDED"];
		}
	}

	if (isset($_SERVER["HTTP_FORWARDED"]) && !check_local_ip($_SERVER["HTTP_FORWARDED"])) {
		if (filter_var($_SERVER["HTTP_FORWARDED"], FILTER_VALIDATE_IP)) {
			$ip = $_SERVER["HTTP_FORWARDED"];
		}
	}

	if (
		isset($_SERVER["HTTP_CF_CONNECTING_IP"]) &&
		!check_local_ip($_SERVER["HTTP_CF_CONNECTING_IP"])
	) {
		if (filter_var($_SERVER["HTTP_CF_CONNECTING_IP"], FILTER_VALIDATE_IP)) {
			$ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
		}
	}
	return $ip;
}

/**
 * Create a history log using 'v-log-action' script.
 *
 * @param string $message The message for log.
 * @param string $category A category for log. Ex: Auth, Firewall, API...
 * @param string $level Info|Warning|Error.
 * @param string $user A username for save in the user history ou 'system' to save in Hestia history.
 * @return int The script result code.
 */
function hst_add_history_log($message, $category = "System", $level = "Info", $user = "system") {
	//$message = ucfirst($message);
	//$message = str_replace("'", "`", $message);
	$category = ucfirst(strtolower($category));
	$level = ucfirst(strtolower($level));

	$command_args =
		quoteshellarg($user) .
		" " .
		quoteshellarg($level) .
		" " .
		quoteshellarg($category) .
		" " .
		quoteshellarg($message);
	exec(HESTIA_CMD . "v-log-action " . $command_args, $output, $return_var);
	unset($output);

	return $return_var;
}

function get_hostname() {
	$badValues = [
		false,
		null,
		0,
		"",
		"localhost",
		"127.0.0.1",
		"::1",
		"0000:0000:0000:0000:0000:0000:0000:0001",
	];
	$ret = gethostname();
	if (in_array($ret, $badValues, true)) {
		throw new Exception("gethostname() failed");
	}
	$ret2 = gethostbyname($ret);
	if (in_array($ret2, $badValues, true)) {
		return $ret;
	}
	$ret3 = gethostbyaddr($ret2);
	if (in_array($ret3, $badValues, true)) {
		return $ret2;
	}
	return $ret3;
}

function display_title($tab) {
	$array1 = ["{{page}}", "{{hostname}}", "{{ip}}", "{{appname}}"];
	$array2 = [$tab, get_hostname(), $_SERVER["REMOTE_ADDR"], $_SESSION["APP_NAME"]];
	return str_replace($array1, $array2, $_SESSION["TITLE"]);
}
