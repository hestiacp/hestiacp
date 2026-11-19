<?php
// Test-only auto_prepend_file: never installed as part of the migration hook.
namespace Hestia\MailPasswordUpgrade;

function rename(string $from, string $to): bool {
	static $first = true;
	if ($first) {
		$first = false;
		\file_put_contents("/scratch/migration-ready", "ready\n");
		$deadline = \microtime(true) + 10;
		while (!\is_file("/scratch/migration-release")) {
			if (\microtime(true) > $deadline) {
				throw new \RuntimeException("Fixture barrier timed out");
			}
			\usleep(10000);
		}
	}
	return \rename($from, $to);
}
