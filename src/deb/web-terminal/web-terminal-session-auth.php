#!/usr/local/hestia/php/bin/php
<?php
declare(strict_types=1);

function deny(string $error, int $code = 1): never {
	echo json_encode(
	["ok" => false, "error" => $error],
	JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
),
		PHP_EOL;
	exit($code);
}

if (!isset($argv[1]) || !is_string($argv[1])) {
	deny("missing session id");
}

$sessionId = $argv[1];
if ($sessionId === "" || preg_match('/^[A-Za-z0-9,-]+$/', $sessionId) !== 1) {
	deny("invalid session id");
}

$hestia = getenv("HESTIA");
if (!is_string($hestia) || $hestia === "") {
	deny("missing HESTIA env");
}

session_name("HESTIASID");
session_save_path($hestia . "/data/sessions");
session_id($sessionId);

if (!@session_start()) {
	deny("session start failed");
}

$user = $_SESSION["user"] ?? "";
$look = $_SESSION["look"] ?? "";

if (!is_string($user) || $user === "") {
	deny("unauthenticated");
}

if (!is_string($look)) {
	$look = "";
}

$config = @parse_ini_file($hestia . "/conf/hestia.conf");
if ($config === false) {
	deny("unable to read configuration");
}

// This script runs as root and the config is world-readable, so enforce the protected-admin
// policy here as well: an admin impersonating ROOT_USER must not get a terminal.
$rootUser = $config["ROOT_USER"] ?? "";
if (
	($config["POLICY_SYSTEM_PROTECTED_ADMIN"] ?? "") === "yes" &&
	$rootUser !== "" &&
	$user !== $rootUser &&
	$look === $rootUser
) {
	deny("read-only protected admin session");
}

echo json_encode(
	["ok" => true, "user" => $user, "look" => $look],
	JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
),
	PHP_EOL;

