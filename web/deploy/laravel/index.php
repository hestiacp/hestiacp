<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

if (empty($_GET["secret"])) {
	http_response_code(403);
	exit("Missing secret");
}

$secret = $_GET["secret"];
if (!preg_match("/^[A-Za-z0-9]{40}$/", $secret)) {
	http_response_code(403);
	exit("Invalid secret");
}

$domainFilter = $_GET["domain"] ?? "";

exec("/usr/bin/sudo /usr/local/hestia/bin/v-list-users plain", $users, $return_var);
if ($return_var !== 0) {
	http_response_code(500);
	exit("Unable to list users");
}

foreach ($users as $userLine) {
	$parts = preg_split("/\s+/", trim($userLine));
	$laravelUser = $parts[0] ?? "";
	if ($laravelUser === "") {
		continue;
	}

	$output = [];
	exec(
		"/usr/bin/sudo /usr/local/hestia/bin/v-list-laravel-apps " .
			quoteshellarg($laravelUser) .
			" json",
		$output,
		$return_var,
	);
	if ($return_var !== 0) {
		continue;
	}
	$apps = json_decode(implode("", $output), true);
	if (!is_array($apps)) {
		continue;
	}
	foreach ($apps as $domain => $app) {
		if ($domainFilter !== "" && $domainFilter !== $domain) {
			continue;
		}
		if (!hash_equals($app["WEBHOOK_SECRET"] ?? "", $secret)) {
			continue;
		}
		$deployOutput = [];
		exec(
			"/usr/bin/sudo /usr/local/hestia/bin/v-deploy-laravel-app " .
				quoteshellarg($laravelUser) .
				" " .
				quoteshellarg($domain) .
				" webhook",
			$deployOutput,
			$deployReturn,
		);
		if ($deployReturn === 0) {
			exit("Laravel deployment queued");
		}
		http_response_code(500);
		exit(implode("\n", $deployOutput));
	}
}

http_response_code(404);
exit("Laravel app not found");
