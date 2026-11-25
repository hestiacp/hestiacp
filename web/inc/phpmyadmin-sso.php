<?php

function phpmyadmin_sso_messages(): array {
	return [
		"expired" => _("The phpMyAdmin sign-in request expired. Open phpMyAdmin again from Databases."),
		"invalid" => _("The phpMyAdmin sign-in link is invalid. Open phpMyAdmin again from Databases."),
		"credentials" => _("Hestia could not prepare temporary access to this database for phpMyAdmin."),
		"unknown" => _("Could not sign in to phpMyAdmin. Open phpMyAdmin again from Databases."),
		"open" => _("Open phpMyAdmin"),
		"ready" => _("phpMyAdmin is ready. Open it in a new tab."),
	];
}

function phpmyadmin_sso_launch(string $database, string $user, string $host): array {
	$authority = strtolower($_SERVER["HTTP_HOST"]);
	if (!preg_match('/\A([a-z0-9][a-z0-9.-]*)(?::([0-9]{1,5}))?\z/', $authority, $parts)) {
		return ["error" => "invalid"];
	}
	$port = isset($parts[2]) ? (int) $parts[2] : 443;
	if ($port <= 0 || $port > 65535) {
		return ["error" => "invalid"];
	}
	$origin = "https://" . $parts[1] . ($port === 443 ? "" : ":" . $port);
	$time = time();
	// Keep the original SSO token format for existing callers.
	$hestia_sso_token = hash_hmac(
		"sha256",
		implode("\0", [$database, $user, $_SESSION["user_combined_ip"], (string) $time, $host]),
		$_SESSION["PHPMYADMIN_KEY"],
	);
	$alias = ($_SESSION["DB_PMA_ALIAS"] ?? "") ?: "phpmyadmin";
	return [
		"endpoint" => "https://" . $parts[1] . "/" . rawurlencode($alias) . "/hestia-sso.php",
		"parameters" => [
			"database" => $database,
			"user" => $user,
			"host" => $host,
			"exp" => $time,
			"hestia_token" => $hestia_sso_token,
			"panel_signature" => hash_hmac(
				"sha256",
				"phpmyadmin-ajax\0" . $origin . "\0" . $hestia_sso_token,
				$_SESSION["PHPMYADMIN_KEY"],
			),
		],
	];
}
