<?php
// Disposable fixtures only; invoked by mail-password-upgrade.py.
declare(strict_types=1);

$base = $argv[1];
$scenario = json_decode($argv[2], true);
$hestia = $base . "/hestia";
$homes = $base . "/home";
$domains = $base . "/domains";
$mail = $homes . "/alice/conf/mail/example.com";
$metadata = $hestia . "/data/users/alice/mail/example.com.conf";
foreach (
	[$hestia . "/conf", dirname($metadata), $hestia . "/func", $mail, $domains]
	as $directory
) {
	mkdir($directory, 0700, true);
}
$password = $scenario["long"] ?? false ? str_repeat("x", 100) : "Legacy-Mail123!";
$legacy = crypt($password, '$1$testSalt$');
$hash = ($scenario["prefix"] ?? "{MD5}") . $legacy;
if ($scenario["modern"] ?? false) {
	$hash = "{BLF-CRYPT}" . password_hash($password, PASSWORD_BCRYPT, ["cost" => 4]);
}
if ($scenario["locked"] ?? false) {
	$hash = "SUSPENDED";
}
$config = $hestia . "/conf/hestia.conf";
file_put_contents($config, "IMAP_SYSTEM='dovecot'\nMAIL_SYSTEM='exim4'\n");
if (!($scenario["missing"] ?? false)) {
	file_put_contents(
		$config,
		"POLICY_MAIL_UPGRADE_MD5='" . ($scenario["policy"] ?? "yes") . "'\n",
		FILE_APPEND,
	);
}
if ($scenario["demo"] ?? false) {
	file_put_contents($config, "DEMO_MODE='yes'\n", FILE_APPEND);
}
$userFile = $hestia . "/data/users/alice/user.conf";
$domainFile = $hestia . "/data/users/alice/mail.conf";
file_put_contents(
	$userFile,
	"USER='alice'\nSUSPENDED='" . ($scenario["user_suspended"] ?? false ? "yes" : "no") . "'\n",
);
file_put_contents(
	$domainFile,
	"DOMAIN='example.com' SUSPENDED='" .
		($scenario["domain_suspended"] ?? false ? "yes" : "no") .
		"'\n",
);
$cached = $hash;
if ($scenario["partial"] ?? false) {
	$cached = "{BLF-CRYPT}" . password_hash($password, PASSWORD_BCRYPT, ["cost" => 4]);
}
if ($scenario["cache_reset"] ?? false) {
	$cached =
		"{BLF-CRYPT}" . password_hash("Different-Password123!", PASSWORD_BCRYPT, ["cost" => 4]);
}
$row =
	"ACCOUNT='bob' MD5='$cached' QUOTA='50' SUSPENDED='" .
	($scenario["account_suspended"] ?? false ? "yes" : "no") .
	"'\n";
$other = "ACCOUNT='other' MD5='untouched' QUOTA='100' SUSPENDED='no'\n";
file_put_contents($metadata, $row . $other);
$runtimeRow = "bob:$hash:alice:mail::$homes/alice:50:userdb_quota_rule=*:storage=50M\n";
file_put_contents(
	$mail . "/passwd",
	$runtimeRow . "other:untouched:alice:mail::$homes/alice:100:\n",
);
$suffixRow = "prefix" . $runtimeRow;
if ($scenario["suffix_account"] ?? false) {
	file_put_contents($mail . "/passwd", $suffixRow, FILE_APPEND);
}
chmod($metadata, 0640);
chmod($mail . "/passwd", 0640);
symlink($mail, $domains . "/example.com");

file_put_contents($base . "/schemes", $scenario["schemes"] ?? "ARGON2ID BLF-CRYPT");
$probe = "#!/bin/bash\nprintf '%s\\n' \"\$*\" > " . escapeshellarg($base . "/probe-args") . "\n";
if ($scenario["probe_fail"] ?? false) {
	$probe .= "exit 1\n";
} elseif ($scenario["race"] ?? false) {
	$probe .=
		"printf '%s\\n' " .
		escapeshellarg("ACCOUNT='bob' MD5='reset-by-admin' SUSPENDED='no'") .
		" > " .
		escapeshellarg($metadata) .
		"\n";
} elseif ($scenario["disable_during_hash"] ?? false) {
	$probe .=
		"printf '%s\\n' " .
		escapeshellarg("POLICY_MAIL_UPGRADE_MD5='no'") .
		" > " .
		escapeshellarg($config) .
		"\n";
}
$probe .= "cat " . escapeshellarg($base . "/schemes") . "\n";
file_put_contents($base . "/doveadm", $probe);
chmod($base . "/doveadm", 0700);
$source = file_get_contents(__DIR__ . "/../func/dovecot-password-upgrade.php");
$source = str_replace('"/usr/bin/doveadm"', var_export($base . "/doveadm", true), $source);
file_put_contents($hestia . "/func/dovecot-password-upgrade.php", $source);
require $hestia . "/func/dovecot-password-upgrade.php";

if ($scenario["duplicate"] ?? false) {
	file_put_contents($mail . "/passwd", $runtimeRow, FILE_APPEND);
}
if ($scenario["metadata_symlink"] ?? false) {
	rename($metadata, $base . "/outside");
	symlink($base . "/outside", $metadata);
}
if ($scenario["passwd_symlink"] ?? false) {
	rename($mail . "/passwd", $base . "/outside");
	symlink($base . "/outside", $mail . "/passwd");
}
if ($scenario["hardlink"] ?? false) {
	link($metadata, $base . "/outside");
}
if ($scenario["lock_symlink"] ?? false) {
	file_put_contents($base . "/outside", "do-not-touch");
	symlink($base . "/outside", $metadata . ".password-upgrade.lock");
}
if ($scenario["commit_lock_symlink"] ?? false) {
	file_put_contents($base . "/outside", "do-not-touch");
	symlink($base . "/outside", $hestia . "/data/.mail-password-upgrade.lock");
}
if ($scenario["commit_lock_hardlink"] ?? false) {
	file_put_contents($base . "/outside", "do-not-touch");
	link($base . "/outside", $hestia . "/data/.mail-password-upgrade.lock");
}
if ($scenario["commit_lock_busy"] ?? false) {
	$administrativeLock = fopen($hestia . "/data/.mail-password-upgrade.lock", "c");
	flock($administrativeLock, LOCK_SH);
}
if ($scenario["owner_mismatch"] ?? false) {
	file_put_contents(
		$mail . "/passwd",
		str_replace(":alice:mail:", ":mallory:mail:", file_get_contents($mail . "/passwd")),
	);
}
$beforeMetadata = file_get_contents($metadata);
$beforeRuntime = file_get_contents($mail . "/passwd");
$beforeStat = stat($mail . "/passwd");
$migrator = new \Hestia\MailPasswordUpgrade\Migrator($hestia, $homes, $domains);
set_error_handler(static function () {
	throw new RuntimeException("Fixture I/O error");
});
$status = 111;
$again = null;
try {
	$status = $migrator->upgrade(
		$scenario["email"] ?? "bob@example.com",
		$scenario["wrong"] ?? false ? "Wrong-Password123!" : $password,
	);
	if (($scenario["repeat"] ?? false) && $status === 0) {
		$again = $migrator->upgrade("bob@example.com", $password);
	}
} catch (Throwable $error) {
	$status = 111;
}
restore_error_handler();
clearstatcache(true);
$afterMetadata = file_get_contents($metadata);
$afterRuntime = file_get_contents($mail . "/passwd");
$afterStat = stat($mail . "/passwd");
$runtimeHash = explode(":", explode("\n", $afterRuntime)[0])[1];
preg_match("/MD5='([^']*)'/", $afterMetadata, $cachedMatch);
$rawHash = preg_replace("/^\{[^}]+\}/", "", $runtimeHash);
$matches = str_starts_with($rawHash, '$1$')
	? hash_equals($rawHash, crypt($password, $rawHash))
	: password_verify($password, $rawHash);
echo json_encode([
	"status" => $status,
	"again" => $again,
	"runtime_hash" => $runtimeHash,
	"cache_hash" => $cachedMatch[1],
	"password_matches" => $matches,
	"metadata_unchanged" => $beforeMetadata === $afterMetadata,
	"runtime_unchanged" => $beforeRuntime === $afterRuntime,
	"other_preserved" =>
		str_contains($afterMetadata, $other) && str_contains($afterRuntime, "other:untouched:"),
	"suffix_preserved" =>
		!($scenario["suffix_account"] ?? false) || str_contains($afterRuntime, $suffixRow),
	"metadata" => $afterMetadata,
	"policy" => file_get_contents($config),
	"permissions_preserved" =>
		($beforeStat["mode"] & 0777) === ($afterStat["mode"] & 0777) &&
		$beforeStat["uid"] === $afterStat["uid"] &&
		$beforeStat["gid"] === $afterStat["gid"],
	"staged_files" => array_merge(
		glob($mail . "/.password-upgrade-*"),
		glob(dirname($metadata) . "/.password-upgrade-*"),
	),
	"probe_args" => file_exists($base . "/probe-args")
		? trim(file_get_contents($base . "/probe-args"))
		: null,
	"outside" => file_exists($base . "/outside") ? file_get_contents($base . "/outside") : null,
]);
