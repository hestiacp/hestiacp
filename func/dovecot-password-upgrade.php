<?php
declare(strict_types=1);

namespace Hestia\MailPasswordUpgrade;

use RuntimeException;

/**
 * Optional MD5-crypt preflight. This is NOT a replacement for Dovecot's passdb.
 * Status 0: migrated; 10: not applicable; 1: authentication denied; 111: error.
 */
final class Migrator {
	private ?string $runtimePath = null;

	public function __construct(
		private string $hestia,
		private string $homes = "/home",
		private string $domains = "/etc/exim4/domains",
	) {}

	private function read(string $path): string {
		clearstatcache(true, $path);
		$before = lstat($path);
		if (
			$before === false ||
			($before["mode"] & 0170000) !== 0100000 ||
			$before["nlink"] !== 1
		) {
			throw new RuntimeException("Unsafe password database");
		}
		$file = fopen($path, "rb");
		if ($file === false) {
			throw new RuntimeException("Cannot read password database");
		}
		try {
			$opened = fstat($file);
			if ($opened["ino"] !== $before["ino"] || $opened["dev"] !== $before["dev"]) {
				throw new RuntimeException("Password database changed");
			}
			$content = stream_get_contents($file, 8 * 1024 * 1024 + 1);
			if ($content === false || strlen($content) > 8 * 1024 * 1024) {
				throw new RuntimeException("Password database too large");
			}
			return $content;
		} finally {
			fclose($file);
		}
	}

	// Parse the canonical KEY='value' fields written by Hestia, never eval them.
	private function value(string $line, string $key): ?string {
		preg_match_all(
			"/(?:^|[ \t\r\n])" . preg_quote($key, "/") . "='([^']*)'(?=[ \t\r\n]|$)/",
			$line,
			$matches,
		);
		return count($matches[1]) === 1 ? $matches[1][0] : null;
	}

	private function row(string $text, string $key, string $value): string {
		$rows = array_filter(
			explode("\n", $text),
			fn($line) => $this->value($line, $key) === $value,
		);
		if (count($rows) !== 1) {
			throw new RuntimeException("Missing or duplicate mail configuration");
		}
		return array_values($rows)[0];
	}

	private function md5Crypt(string $hash): ?string {
		$hash = preg_replace("/^\{(?:MD5|MD5-CRYPT|CRYPT)\}/i", "", $hash);
		return preg_match('/^\$1\$[.\/0-9A-Za-z]{1,8}\$[.\/0-9A-Za-z]{22}$/D', $hash)
			? $hash
			: null;
	}

	private function replaceRow(string $text, string $old, string $new): string {
		$lines = explode("\n", $text);
		$matches = array_keys($lines, $old, true);
		if (count($matches) !== 1) {
			throw new RuntimeException("Missing or duplicate password record");
		}
		$lines[$matches[0]] = $new;
		return implode("\n", $lines);
	}

	private function verifyModern(string $hash, string $password): bool {
		if (!preg_match('/^\{(?:BLF-CRYPT|ARGON2ID)\}(.+)$/D', $hash, $match)) {
			return false;
		}
		if (str_starts_with($hash, "{BLF-CRYPT}") && strlen($password) > 72) {
			return false;
		}
		return password_verify($password, $match[1]);
	}

	private function modernHash(string $password): string {
		// Discover actual Dovecot support, without sending it a plaintext password.
		$process = proc_open(
			["/usr/bin/doveadm", "-O", "pw", "-l"],
			[0 => ["file", "/dev/null", "r"], 1 => ["pipe", "w"], 2 => ["file", "/dev/null", "w"]],
			$pipes,
			"/",
			["PATH" => "/usr/bin:/bin", "LC_ALL" => "C"],
		);
		if (!is_resource($process)) {
			throw new RuntimeException("Cannot discover password schemes");
		}
		$output = stream_get_contents($pipes[1], 16384);
		fclose($pipes[1]);
		$status = proc_close($process);
		if ($status !== 0 || $output === false) {
			throw new RuntimeException("Cannot discover password schemes");
		}
		$schemes = preg_split("/\s+/", trim($output));
		if (in_array("ARGON2ID", $schemes, true) && defined("PASSWORD_ARGON2ID")) {
			return "{ARGON2ID}" .
				password_hash($password, PASSWORD_ARGON2ID, [
					"memory_cost" => 65536,
					"time_cost" => 3,
					"threads" => 1,
				]);
		}
		if (in_array("BLF-CRYPT", $schemes, true) && strlen($password) <= 72) {
			return "{BLF-CRYPT}" . password_hash($password, PASSWORD_BCRYPT, ["cost" => 12]);
		}
		// bcrypt would silently truncate longer passwords. Never do that or use MD5.
		throw new RuntimeException("No suitable modern password scheme");
	}

	/** Prepare a complete replacement beside its destination, preserving access. */
	private function stage(string $path, string $content): string {
		$stat = lstat($path);
		if ($stat === false || ($stat["mode"] & 0170000) !== 0100000 || $stat["nlink"] !== 1) {
			throw new RuntimeException("Unsafe password database");
		}
		$temp = dirname($path) . "/.password-upgrade-" . bin2hex(random_bytes(16));
		$file = fopen($temp, "xb");
		if ($file === false) {
			throw new RuntimeException("Cannot prepare password database");
		}
		try {
			if (
				!chmod($temp, 0600) ||
				fwrite($file, $content) !== strlen($content) ||
				!fflush($file) ||
				!fsync($file) ||
				!chown($temp, $stat["uid"]) ||
				!chgrp($temp, $stat["gid"]) ||
				!chmod($temp, $stat["mode"] & 0777)
			) {
				throw new RuntimeException("Cannot prepare password database");
			}
		} catch (\Throwable $error) {
			unlink($temp);
			throw $error;
		} finally {
			fclose($file);
		}
		return $temp;
	}

	/** A separate lock file survives atomic renames. Bound contention. */
	private function lock(string $path) {
		$stat = file_exists($path) || is_link($path) ? lstat($path) : false;
		if ($stat !== false && (($stat["mode"] & 0170000) !== 0100000 || $stat["nlink"] !== 1)) {
			throw new RuntimeException("Unsafe mail configuration lock");
		}
		$lock = fopen($path, "c");
		if ($lock === false) {
			throw new RuntimeException("Cannot lock mail configuration");
		}
		try {
			clearstatcache(true, $path);
			$stat = lstat($path);
			$opened = fstat($lock);
			if (
				$stat === false ||
				($stat["mode"] & 0170000) !== 0100000 ||
				$stat["nlink"] !== 1 ||
				$opened["ino"] !== $stat["ino"] ||
				$opened["dev"] !== $stat["dev"] ||
				!chmod($path, 0600)
			) {
				throw new RuntimeException("Unsafe mail configuration lock");
			}
			for ($attempt = 0; $attempt < 100; $attempt++) {
				if (flock($lock, LOCK_EX | LOCK_NB)) {
					return $lock;
				}
				usleep(20000);
			}
			throw new RuntimeException("Mail configuration busy");
		} catch (\Throwable $error) {
			fclose($lock);
			throw $error;
		}
	}

	public function waitForPasswdFileSync(int $status): void {
		if (($status !== 0 && $status !== 10) || $this->runtimePath === null) {
			return;
		}
		clearstatcache(true, $this->runtimePath);
		$stat = lstat($this->runtimePath);
		if ($stat === false) {
			throw new RuntimeException("Cannot inspect password database");
		}
		if ($status === 0 || max($stat["mtime"], $stat["ctime"]) >= time() - 1) {
			// Dovecot passwd-file only checks for changes once per wall-clock
			// second, even with auth_cache_size=0. Returning NEXT immediately
			// can therefore authenticate against a different account's stale
			// cached MD5 row. Wait beyond that tick, AFTER releasing all locks.
			// Recent modern rows need the same guard for parallel logins while
			// another worker is finishing a migration. Old files add no delay.
			// Always guard our own commit: a staged file may have waited at
			// the commit gate, so its mtime need not reflect the rename time.
			usleep(1100000);
		}
	}

	public function upgrade(string $email, string $password): int {
		$this->runtimePath = null;
		$config = $this->read($this->hestia . "/conf/hestia.conf");
		if (
			$this->value($config, "POLICY_MAIL_UPGRADE_MD5") !== "yes" ||
			$this->value($config, "IMAP_SYSTEM") !== "dovecot" ||
			$this->value($config, "DEMO_MODE") === "yes" ||
			$this->value($config, "DEMO_MODE") === "true"
		) {
			return 10;
		}
		if (
			strlen($email) > 320 ||
			strlen($password) === 0 ||
			strlen($password) > 4096 ||
			preg_match('/[\x00\r\n]/', $email . $password) ||
			!preg_match('/^([a-z0-9_.-]{1,64})@([a-z0-9][a-z0-9._-]{0,252})$/D', $email, $parts) ||
			in_array($parts[1], [".", ".."], true) ||
			str_contains($parts[2], "..")
		) {
			return 1;
		}
		[$unused, $account, $domain] = $parts;
		$directory = realpath($this->domains . "/" . $domain);
		$linked = realpath($this->domains . "/" . $domain . "/passwd");
		$prefix = preg_quote($this->homes, "/");
		if (
			$linked === false ||
			$linked !== $directory . "/passwd" ||
			!preg_match(
				"/^" . $prefix . "\/([a-z0-9][a-z0-9_.-]{0,29})\/conf\/mail\/([^\/]+)\/passwd$/D",
				$linked,
				$mapping,
			)
		) {
			return 1;
		}
		[$unused, $owner, $storedDomain] = $mapping;
		$userData = $this->hestia . "/data/users/" . $owner;
		if (
			realpath($userData) !== $userData ||
			in_array($storedDomain, [".", ".."], true) ||
			preg_match('/[\x00-\x20\'\\\\]/', $storedDomain)
		) {
			return 1;
		}
		$metadata = $userData . "/mail/" . $storedDomain . ".conf";
		if (realpath(dirname($metadata)) !== dirname($metadata)) {
			return 1;
		}
		$lock = $this->lock($metadata . ".password-upgrade.lock");
		$commitLock = null;
		$staged = [];
		try {
			$runtime = $this->read($linked);
			$this->runtimePath = $linked;
			$runtimeRows = array_values(
				array_filter(
					explode("\n", $runtime),
					fn($line) => str_starts_with($line, $account . ":"),
				),
			);
			if (count($runtimeRows) !== 1) {
				return 1;
			}
			$fields = explode(":", $runtimeRows[0]);
			$legacy = $this->md5Crypt($fields[1] ?? "");
			if ($legacy === null) {
				return 10;
			}
			$users = $this->read($userData . "/user.conf");
			$mailDomains = $this->read($userData . "/mail.conf");
			$domainRow = $this->row($mailDomains, "DOMAIN", $storedDomain);
			$original = $this->read($metadata);
			$accountRow = $this->row($original, "ACCOUNT", $account);
			if (
				($fields[2] ?? "") !== $owner ||
				$this->value($users, "SUSPENDED") !== "no" ||
				$this->value($domainRow, "SUSPENDED") !== "no" ||
				$this->value($accountRow, "SUSPENDED") !== "no" ||
				!hash_equals($legacy, crypt($password, $legacy))
			) {
				return 1;
			}
			$cached = $this->value($accountRow, "MD5");
			if ($cached === null) {
				return 1;
			}
			if ($this->md5Crypt($cached) === $legacy) {
				$modern = $this->modernHash($password);
			} elseif ($this->verifyModern($cached, $password)) {
				// Recover if a prior process stopped after committing metadata.
				$modern = $cached;
			} else {
				return 1; // A different/reset password must never be overwritten.
			}
			$newRow = str_replace("MD5='" . $cached . "'", "MD5='" . $modern . "'", $accountRow);
			$newMetadata = $this->replaceRow($original, $accountRow, $newRow);
			$fields[1] = $modern;
			$newRuntime = $this->replaceRow($runtime, $runtimeRows[0], implode(":", $fields));
			$staged[] = $metaTemp = $this->stage($metadata, $newMetadata);
			$staged[] = $passTemp = $this->stage($linked, $newRuntime);

			// Administrative writers hold this gate shared throughout their CLI
			// operation. Hashing stays outside it; the recheck and BOTH renames
			// are exclusive so no reset, suspension, rebuild or restore can
			// finish between the final state check and our replacements.
			$commitLock = $this->lock($this->hestia . "/data/.mail-password-upgrade.lock");
			// Detect resets, suspensions and policy changes during hash generation.
			clearstatcache(true);
			if (
				$this->read($metadata) !== $original ||
				$this->read($linked) !== $runtime ||
				$this->read($userData . "/user.conf") !== $users ||
				$this->read($userData . "/mail.conf") !== $mailDomains ||
				$this->read($this->hestia . "/conf/hestia.conf") !== $config ||
				realpath($this->domains . "/" . $domain . "/passwd") !== $linked
			) {
				throw new RuntimeException("Mail configuration changed during upgrade");
			}
			// Metadata first lets later rebuilds use the modern hash.
			if (!rename($metaTemp, $metadata) || !rename($passTemp, $linked)) {
				throw new RuntimeException("Cannot commit password upgrade");
			}
			return 0;
		} finally {
			if (is_resource($commitLock)) {
				fclose($commitLock);
			}
			foreach ($staged as $temp) {
				if (is_file($temp)) {
					unlink($temp);
				}
			}
			fclose($lock);
		}
	}
}

if (realpath($_SERVER["SCRIPT_FILENAME"] ?? "") === __FILE__) {
	// Credentials only on stdin, with fixed bounds. Never log input or exceptions.
	set_error_handler(static function () {
		throw new RuntimeException("Password upgrade failed");
	});
	try {
		$email = fgets(STDIN, 322);
		$password = fgets(STDIN, 4098);
		if (
			$email === false ||
			$password === false ||
			!str_ends_with($email, "\n") ||
			!str_ends_with($password, "\n") ||
			fgetc(STDIN) !== false
		) {
			exit(1);
		}
		$migrator = new Migrator(dirname(__DIR__));
		$status = $migrator->upgrade(substr($email, 0, -1), substr($password, 0, -1));
		$migrator->waitForPasswdFileSync($status);
		exit($status);
	} catch (\Throwable $error) {
		// A fixed message prevents credentials or stored hashes reaching logs.
		fwrite(STDERR, "Hestia mail password upgrade failed\n");
		exit(111);
	}
}
