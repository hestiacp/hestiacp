<?php

declare(strict_types=1);

if (!defined("HESTIA_DIR_BIN") && !defined("HESTIA_MFP_TEST_MODE")) {
	http_response_code(404);
	exit();
}

function hst_mfp_cmd_prefix(): string {
	if (
		defined("HESTIA_MFP_CMD_PREFIX") &&
		is_string(HESTIA_MFP_CMD_PREFIX) &&
		HESTIA_MFP_CMD_PREFIX !== ""
	) {
		return HESTIA_MFP_CMD_PREFIX;
	}
	if (defined("HESTIA_CMD") && is_string(HESTIA_CMD) && HESTIA_CMD !== "") {
		return HESTIA_CMD;
	}
	return "/usr/bin/sudo /usr/local/hestia/bin/";
}

function hst_mfp_exim_domains_dir(): string {
	if (
		defined("HESTIA_MFP_EXIM_DOMAINS_DIR") &&
		is_string(HESTIA_MFP_EXIM_DOMAINS_DIR) &&
		HESTIA_MFP_EXIM_DOMAINS_DIR !== ""
	) {
		return HESTIA_MFP_EXIM_DOMAINS_DIR;
	}
	return "/etc/exim4/domains";
}

function hst_mfp_normalize_policy(?string $policy): string {
	$norm = strtolower(trim((string) $policy));
	if ($norm === "" || $norm === "yes") {
		return "yes";
	}
	if ($norm === "server") {
		return "server";
	}
	return "user";
}

function hst_mfp_normalize_domain(string $domain): string {
	$clean = strtolower(rtrim(trim($domain), "."));
	if ($clean === "" || preg_match('/[\s\x00-\x1f\x7f]/', $clean)) {
		return "";
	}
	if (preg_match('/[^\x20-\x7e]/', $clean) && function_exists("idn_to_ascii")) {
		$variant = defined("INTL_IDNA_VARIANT_UTS46") ? INTL_IDNA_VARIANT_UTS46 : 0;
		$ascii = @idn_to_ascii($clean, IDNA_DEFAULT, $variant);
		if (is_string($ascii) && $ascii !== "") {
			return strtolower($ascii);
		}
	}
	return $clean;
}

/**
 * @return string[]
 */
function hst_mfp_parse_targets(string $raw): array {
	$clean = str_replace(["\r", "\n", ","], " ", $raw);
	$clean = preg_replace("/\s+/", " ", trim($clean)) ?? "";
	if ($clean === "") {
		return [];
	}
	$out = [];
	foreach (explode(" ", $clean) as $part) {
		$item = trim($part);
		if ($item !== "") {
			$out[] = $item;
		}
	}
	return $out;
}

function hst_mfp_extract_domain(string $address): string {
	$trimmed = trim($address);
	if ($trimmed === "") {
		return "";
	}
	if (strtolower($trimmed) === ":blackhole:") {
		return ":blackhole:";
	}
	if (preg_match('/[\s\x00-\x1f\x7f\/|:]/', $trimmed)) {
		return "";
	}
	$parts = explode("@", $trimmed);
	if (count($parts) !== 2 || $parts[0] === "" || $parts[1] === "") {
		return "";
	}
	return hst_mfp_normalize_domain($parts[1]);
}

/**
 * @return array<string, mixed>
 */
function hst_mfp_exec_json(string $subcommand_with_args): array {
	$output = [];
	$rc = 0;
	exec(hst_mfp_cmd_prefix() . $subcommand_with_args, $output, $rc);
	if ($rc !== 0 || empty($output)) {
		return [];
	}
	$decoded = json_decode(implode("\n", $output), true);
	return is_array($decoded) ? $decoded : [];
}

/**
 * @param array<string, true> $map
 */
function hst_mfp_add_domain_key(array &$map, string $domain): void {
	$raw = strtolower(rtrim(trim($domain), "."));
	if ($raw !== "" && !preg_match('/[\s\x00-\x1f\x7f]/', $raw)) {
		$map[$raw] = true;
	}
	$norm = hst_mfp_normalize_domain($domain);
	if ($norm !== "") {
		$map[$norm] = true;
	}
}

/**
 * @return array<string, true>
 */
function hst_mfp_get_allowed_domains(string $target_user, string $mode): array {
	$allowed = [];

	if ($mode === "server") {
		$users = hst_mfp_exec_json("v-list-users json");
		if (!empty($users)) {
			foreach ($users as $u => $user_meta) {
				if (
					!is_string($u) ||
					$u === "." ||
					$u === ".." ||
					!preg_match('/^[a-zA-Z0-9._-]+$/', $u)
				) {
					continue;
				}
				if (is_array($user_meta) && ($user_meta["SUSPENDED"] ?? "no") === "yes") {
					continue;
				}
				$domains = hst_mfp_exec_json("v-list-mail-domains " . escapeshellarg($u) . " json");
				foreach ($domains as $dom => $meta) {
					if (is_array($meta) && ($meta["SUSPENDED"] ?? "no") === "yes") {
						continue;
					}
					if (is_string($dom)) {
						hst_mfp_add_domain_key($allowed, $dom);
					}
				}
			}
			return $allowed;
		}

		$exim_dir = hst_mfp_exim_domains_dir();
		if (is_dir($exim_dir) && is_readable($exim_dir)) {
			$entries = @scandir($exim_dir);
			if (is_array($entries)) {
				foreach ($entries as $entry) {
					if ($entry === "." || $entry === "..") {
						continue;
					}
					if (is_dir($exim_dir . "/" . $entry)) {
						hst_mfp_add_domain_key($allowed, $entry);
					}
				}
			}
		}
		return $allowed;
	}

	$clean_user = trim($target_user, "'\"");
	if (
		$clean_user !== "" &&
		$clean_user !== "." &&
		$clean_user !== ".." &&
		preg_match('/^[a-zA-Z0-9._-]+$/', $clean_user)
	) {
		$domains = hst_mfp_exec_json(
			"v-list-mail-domains " . escapeshellarg($clean_user) . " json",
		);
		foreach ($domains as $dom => $meta) {
			if (is_array($meta) && ($meta["SUSPENDED"] ?? "no") === "yes") {
				continue;
			}
			if (is_string($dom)) {
				hst_mfp_add_domain_key($allowed, $dom);
			}
		}
	}

	return $allowed;
}

/**
 * @param string[] $targets
 * @return string[]
 */
function hst_mfp_find_blocked_targets(
	array $targets,
	string $target_user,
	string $mode,
	bool $allow_blackhole = true,
): array {
	$allowed = hst_mfp_get_allowed_domains($target_user, $mode);
	$blocked = [];
	$seen = [];

	foreach ($targets as $target) {
		$item = trim((string) $target);
		if ($item === "") {
			continue;
		}
		if (strtolower($item) === ":blackhole:") {
			if (!$allow_blackhole) {
				$key = strtolower($item);
				if (!isset($seen[$key])) {
					$seen[$key] = true;
					$blocked[] = $item;
				}
			}
			continue;
		}
		$domain = hst_mfp_extract_domain($item);
		if ($domain === "" || !isset($allowed[$domain])) {
			$key = strtolower($item);
			if (!isset($seen[$key])) {
				$seen[$key] = true;
				$blocked[] = $item;
			}
		}
	}

	return $blocked;
}

/**
 * @param string[] $blocked
 */
function hst_mfp_format_error(array $blocked, string $mode, string $context_label): string {
	$list = preg_replace('/[\x00-\x1f\x7f]/', "", implode(", ", $blocked)) ?? "";
	if ($context_label === "catchall") {
		if ($mode === "server") {
			return sprintf(
				_(
					"External catch-all forwarding is disabled by server policy to protect server mail deliverability (%s). Only mail domains hosted on this server are permitted. Connect via IMAP/SMTP instead or contact the server administrator.",
				),
				$list,
			);
		}
		return sprintf(
			_(
				"External catch-all forwarding is disabled by server policy to protect server mail deliverability (%s). Only mail domains belonging to this account are permitted. Connect via IMAP/SMTP instead or contact the server administrator.",
			),
			$list,
		);
	}
	if ($mode === "server") {
		return sprintf(
			_(
				"External mail forwarding is disabled by server policy to protect server mail deliverability (%s). Only mail domains hosted on this server are permitted. Connect via IMAP/SMTP instead or contact the server administrator.",
			),
			$list,
		);
	}
	return sprintf(
		_(
			"External mail forwarding is disabled by server policy to protect server mail deliverability (%s). Only mail domains belonging to this account are permitted. Connect via IMAP/SMTP instead or contact the server administrator.",
		),
		$list,
	);
}

/**
 * @param string[] $new_targets
 */
function hst_validate_mail_forward_policy(
	array $new_targets,
	string $target_user,
	string $context_label = "forward",
	?string $policy = null,
): ?string {
	if (($_SESSION["userContext"] ?? "") === "admin") {
		return null;
	}
	if (empty($new_targets)) {
		return null;
	}

	$mode = hst_mfp_normalize_policy(
		$policy ?? ($_SESSION["POLICY_USER_EXTERNAL_MAIL_FORWARD"] ?? "yes"),
	);
	if ($mode === "yes") {
		return null;
	}

	$clean_user = trim($target_user, "'\"");
	$blocked = hst_mfp_find_blocked_targets(
		$new_targets,
		$clean_user,
		$mode,
		$context_label !== "catchall",
	);
	if (empty($blocked)) {
		return null;
	}

	if (!defined("HESTIA_MFP_SKIP_LOG") && function_exists("hst_add_history_log")) {
		$log_user = $clean_user !== "" ? $clean_user : "system";
		$safe_list = preg_replace('/[\x00-\x1f\x7f]/', "", implode(", ", $blocked)) ?? "";
		hst_add_history_log(
			"Blocked external mail " . $context_label . " target(s): " . $safe_list,
			"Mail",
			"Warning",
			$log_user,
		);
	}

	return hst_mfp_format_error($blocked, $mode, $context_label);
}

function hst_mfp_is_protected_command(string $cmd): bool {
	return in_array(
		$cmd,
		[
			"v-add-mail-account-forward",
			"v-add-mail-domain-catchall",
			"v-change-mail-domain-catchall",
		],
		true,
	);
}

function hst_mfp_is_admin_user(string $user): bool {
	$clean_user = trim($user, "'\"");
	if ($clean_user === "" || !preg_match('/^[a-zA-Z0-9._-]+$/', $clean_user)) {
		return false;
	}
	$user_data = hst_mfp_exec_json("v-list-user " . escapeshellarg($clean_user) . " json");
	return ($user_data[$clean_user]["ROLE"] ?? "") === "admin";
}

/**
 * @param array<string, string> $cmd_args
 * @return array<string, string>
 */
function hst_mfp_apply_api_force_arg(string $cmd, array $cmd_args, bool $is_admin): array {
	if (!$is_admin) {
		return $cmd_args;
	}
	if ($cmd === "v-add-mail-account-forward" && count($cmd_args) === 4) {
		$cmd_args[] = "yes";
	} elseif (
		($cmd === "v-add-mail-domain-catchall" || $cmd === "v-change-mail-domain-catchall") &&
		count($cmd_args) === 3
	) {
		$cmd_args[] = "yes";
	}
	return $cmd_args;
}

/**
 * @param array<string, string> $args
 */
function hst_validate_api_mail_forward_policy(
	string $cmd,
	array $args,
	string $key_user,
	string $root_user,
	string $policy,
): ?string {
	if ($root_user !== "" && $key_user === $root_user) {
		return null;
	}

	$mode = hst_mfp_normalize_policy($policy);
	if ($mode === "yes") {
		return null;
	}

	$context_label = "";
	$target_start_index = 0;
	if ($cmd === "v-add-mail-account-forward") {
		$context_label = "forward";
		$target_start_index = 3;
	} elseif ($cmd === "v-add-mail-domain-catchall" || $cmd === "v-change-mail-domain-catchall") {
		$context_label = "catchall";
		$target_start_index = 2;
	} else {
		return null;
	}

	$clean_user = trim($key_user, "'\"");
	if (
		$clean_user === "" ||
		$clean_user === "." ||
		$clean_user === ".." ||
		!preg_match('/^[a-zA-Z0-9._-]+$/', $clean_user)
	) {
		return hst_mfp_format_error(["invalid user"], $mode, $context_label);
	}
	if (hst_mfp_is_admin_user($clean_user)) {
		return null;
	}

	// api_connection() passes received arg1..arg13 sequentially to exec(), so omitting arg4 and
	// sending arg5 shifts arg5 into position 3 ($4 in bash). Validate positionally across all
	// trailing positional arguments, reject unexpected trailing FORCE arguments, and fail closed
	// if the target is missing or empty.
	$pos_args = array_values($args);
	if (count($pos_args) > $target_start_index + 1) {
		return hst_mfp_format_error(["unexpected trailing argument"], $mode, $context_label);
	}
	$candidates = array_slice($pos_args, $target_start_index);
	$non_empty = [];
	foreach ($candidates as $cand) {
		$t = trim((string) $cand);
		if ($t !== "") {
			$non_empty[] = $t;
		}
	}
	if (empty($non_empty)) {
		return hst_mfp_format_error(["missing target"], $mode, $context_label);
	}

	$blocked = hst_mfp_find_blocked_targets(
		$non_empty,
		$clean_user,
		$mode,
		$context_label !== "catchall",
	);
	if (empty($blocked)) {
		return null;
	}

	return hst_mfp_format_error($blocked, $mode, $context_label);
}
