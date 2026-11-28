<?php
use Divinity76\CloudflareIpValidator\CloudflareIpValidator;
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

if (!function_exists("tohtml")) {
	function tohtml(string|int|float|bool|null $str): string {
		if ($str === null || $str === "") {
			return "";
		}
		if (is_int($str) || is_float($str)) {
			return (string) $str;
		}
		if (is_bool($str)) {
			return $str ? "1" : "0";
		}

		return htmlentities(
			$str,
			ENT_QUOTES | ENT_SUBSTITUTE | ENT_DISALLOWED | ENT_HTML5,
			"UTF-8",
			true,
		);
	}
}

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
	if (in_array($addr, [$_SERVER["SERVER_ADDR"], "127.0.0.1"], true)) {
		return true;
	} else {
		return false;
	}
}

function get_real_user_ip() {
	$ip = "";

	if (
		!empty($_SERVER["REMOTE_ADDR"]) &&
		filter_var($_SERVER["REMOTE_ADDR"], FILTER_VALIDATE_IP)
	) {
		$ip = $_SERVER["REMOTE_ADDR"];
	}

	if (
		!empty($_SERVER["HTTP_CF_CONNECTING_IP"]) &&
		filter_var($_SERVER["HTTP_CF_CONNECTING_IP"], FILTER_VALIDATE_IP) &&
		!empty($ip) &&
		CloudflareIpValidator::isCloudflareIp($ip)
	) {
		$ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
	}

	// Handling IPv4-mapped IPv6 address
	if (strpos($ip, ":") === 0 && strpos($ip, ".") > 0) {
		$ip = substr($ip, strrpos($ip, ":") + 1); // Strip IPv4 Compatibility notation
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

/**
 * Format a byte count as a human readable string (e.g. "52.4 MB").
 *
 * @param int|float|string|null $bytes
 * @param int $precision
 * @return string
 */
function humanize_bytes($bytes, $precision = 1) {
	if (!is_numeric($bytes) || $bytes <= 0) {
		return "0 B";
	}
	$units = ["B", "KB", "MB", "GB", "TB"];
	$i = min((int) floor(log((float) $bytes, 1024)), count($units) - 1);
	$value = $bytes / 1024 ** $i;
	return number_format($value, $i === 0 ? 0 : $precision) . " " . $units[$i];
}

/**
 * Format a duration in seconds as a compact string (e.g. "2d 3h", "5m 12s").
 *
 * @param int|string|null $seconds
 * @return string
 */
function humanize_duration($seconds) {
	if (!is_numeric($seconds) || $seconds < 0) {
		return "-";
	}
	$seconds = (int) $seconds;
	$days = intdiv($seconds, 86400);
	$hours = intdiv($seconds % 86400, 3600);
	$minutes = intdiv($seconds % 3600, 60);
	$secs = $seconds % 60;
	if ($days > 0) {
		return $days . "d " . $hours . "h";
	}
	if ($hours > 0) {
		return $hours . "h " . $minutes . "m";
	}
	if ($minutes > 0) {
		return $minutes . "m " . $secs . "s";
	}
	return $secs . "s";
}

/**
 * Map a web domain app's systemd ActiveState to an icon and label.
 *
 * @param string|null $state
 * @return array{icon: string, color: string, label: string}
 */
function get_web_app_state($state) {
	switch ($state) {
		case "active":
			return [
				"icon" => "fa-circle-check icon-green",
				"color" => "icon-green",
				"label" => _("Running"),
			];
		case "failed":
			return [
				"icon" => "fa-circle-xmark icon-red",
				"color" => "icon-red",
				"label" => _("Failed"),
			];
		case "activating":
		case "reloading":
			return [
				"icon" => "fa-circle-notch icon-orange",
				"color" => "icon-orange",
				"label" => _("Starting"),
			];
		case "deactivating":
			return [
				"icon" => "fa-circle-notch icon-orange",
				"color" => "icon-orange",
				"label" => _("Stopping"),
			];
		default:
			return ["icon" => "fa-circle-minus", "color" => "", "label" => _("Stopped")];
	}
}

/**
 * List the Node.js versions usable for web domain apps.
 *
 * @return array<string, string> VERSION key => installed version (e.g. "system" => "v24.11.0")
 */
function list_web_app_node_versions() {
	exec(HESTIA_CMD . "v-list-sys-nodejs json", $output, $return_var);
	$versions = [];
	if ($return_var == 0) {
		$data = json_decode(implode("", $output), true);
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				$versions[(string) $key] = $value["VERSION"] ?? "";
			}
		}
	}
	// Offer "system" only when it is installed, or as the only (failing with a
	// clear message) choice when no Node.js is available at all
	if (empty($versions)) {
		$versions = ["system" => ""];
	}
	return $versions;
}

/**
 * Replace the environment (.env) of a web domain app.
 *
 * @param string $user Already shell-quoted user name
 * @param string $domain Domain name (unquoted)
 * @param string $env Dotenv text
 * @param string $restart "yes" or "no"
 * @return array{0: int, 1: array} [return code, output lines]
 */
function save_web_app_env($user, $domain, $env, $restart = "yes") {
	$env = str_replace("\r\n", "\n", $env);
	$env = rtrim($env) . "\n";
	$tmpfile = tempnam("/tmp", "hst_");
	file_put_contents($tmpfile, $env);
	exec(
		HESTIA_CMD .
			"v-change-web-domain-app-env " .
			$user .
			" " .
			quoteshellarg($domain) .
			" " .
			quoteshellarg($tmpfile) .
			" " .
			quoteshellarg($restart),
		$output,
		$return_var,
	);
	unlink($tmpfile);
	return [$return_var, $output];
}
