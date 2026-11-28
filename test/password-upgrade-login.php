<?php
// Loaded by password-upgrade.py, not a web endpoint.
namespace PasswordUpgradeTest;

const HESTIA_CMD = "mock ";
$scenario = json_decode($argv[1], true) + [
	"policy" => "yes",
	"method" => "md5",
	"csrf" => true,
	"password_valid" => true,
	"suspended" => false,
	"disabled" => false,
	"ip_allowed" => true,
	"twofa" => "123456",
	"twofa_valid" => true,
	"upgrade_valid" => true,
	"tempfile_valid" => true,
	"write_valid" => true,
];
$upgrades = 0;
$before_session = false;
$_SESSION = [
	"POLICY_SYSTEM_UPGRADE_MD5" => $scenario["policy"],
	"POLICY_USER_CHANGE_THEME" => "yes",
];
$_SERVER["HTTP_USER_AGENT"] = "test";
$_POST = ["token" => "dummy"];

function quoteshellarg($value) {
	return escapeshellarg($value);
}
function verify_csrf(...$args) {
	return $GLOBALS["scenario"]["csrf"];
}
function get_real_user_ip() {
	return "192.0.2.1";
}
function sleep($seconds) {}
function _($value) {
	return $value;
}
function header($value) {}
function session_regenerate_id($delete) {}
function session_write_close() {}
function session_unset() {
	$_SESSION = [];
}
function session_destroy() {}
function session_start() {}
function tmpfile() {
	return $GLOBALS["scenario"]["tempfile_valid"] ? \tmpfile() : false;
}
function fwrite($stream, $data) {
	if ($data === "Legacy-Pass123!\n" && !$GLOBALS["scenario"]["write_valid"]) {
		return 0;
	}
	return \fwrite($stream, $data);
}

function exec($command, &$output = null, &$status = null) {
	global $scenario, $upgrades, $before_session;
	$output = [];
	$status = 0;
	if (str_starts_with($command, "mktemp ")) {
		return tempnam(sys_get_temp_dir(), "hash-");
	}
	$parts = str_getcsv($command, " ", "'", "\\");
	switch ($parts[1]) {
		case "v-get-user-salt":
			$status = $scenario["suspended"] ? 5 : 0;
			$output[] = json_encode([
				"alice" => ["METHOD" => $scenario["method"], "SALT" => "testSalt"],
			]);
			break;
		case "v-check-user-hash":
			$hash = trim(file_get_contents($parts[3]));
			$salt = $scenario["method"] === "md5" ? '$1$testSalt$' : '$6$testSalt$';
			$status =
				$scenario["password_valid"] && $hash === crypt("Legacy-Pass123!", $salt) ? 0 : 9;
			break;
		case "v-list-user":
			$output[] = json_encode([
				"alice" => [
					"LOGIN_DISABLED" => $scenario["disabled"] ? "yes" : "no",
					"LOGIN_USE_IPLIST" => "yes",
					"LOGIN_ALLOW_IPS" => $scenario["ip_allowed"] ? "192.0.2.1" : "192.0.2.2",
					"TWOFA" => "enabled",
					"ROLE" => "admin",
					"THEME" => "default",
					"LANGUAGE" => "en",
					"PREF_UI_SORT" => "name",
				],
			]);
			break;
		case "v-check-user-2fa":
			$status = $scenario["twofa_valid"] ? 0 : 9;
			break;
		case "v-upgrade-user-password-hash":
			$upgrades++;
			$before_session = !isset($_SESSION["user"]);
			if (trim(file_get_contents($parts[3])) !== "Legacy-Pass123!") {
				throw new \RuntimeException("Password file was not available during migration");
			}
			$status = $scenario["upgrade_valid"] ? 0 : 9;
			break;
		case "v-list-sys-languages":
			$output[] = json_encode(["en"]);
			break;
		case "v-log-user-login":
			break;
		default:
			throw new \RuntimeException("Unexpected command: " . $command);
	}
	return "";
}

register_shutdown_function(function () {
	global $upgrades, $before_session;
	echo json_encode([
		"upgrades" => $upgrades,
		"before_session" => $before_session,
		"user" => $_SESSION["user"] ?? null,
	]);
});

// AUTHENTICATE_USER

authenticate_user("alice", "Legacy-Pass123!", $scenario["twofa"]);
