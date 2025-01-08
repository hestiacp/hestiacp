<?php

declare(strict_types=1);

namespace Hestia\System;
use Exception;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use function _;
use function array_filter;
use function chmod;
use function explode;
use function realpath;
use function sprintf;
use function str_starts_with;
use function trigger_error;
use function unlink;
use const DIRECTORY_SEPARATOR;

class HestiaApp {
	/** @var string[] */
	public $errors;
	protected $phpsupport = false;

	public function addDirectory(string $path): void
	{
		try {
			$this->runUser("v-add-fs-directory", [$path]);
		} catch (ProcessFailedException) {
			throw new RuntimeException(sprintf('Failed to add directory "%s"', $path));
		}
	}

	public function copyDirectory(string $fromPath, string $toPath): void
	{
		try {
			$this->runUser("v-copy-fs-directory", [$fromPath, $toPath]);
		} catch (ProcessFailedException) {
			throw new RuntimeException(
				sprintf('Failed to copy directory "%s" to "%s"', $fromPath, $toPath)
			);
		}
	}

	public function deleteDirectory(string $path): void
	{
		try {
			$this->runUser("v-delete-fs-directory", [$path]);
		} catch (ProcessFailedException) {
			throw new RuntimeException(sprintf('Failed to remove directory "%s"', $path));
		}
	}

	/**
	 * @param string $path
	 * @return string[]
	 */
	public function listFiles(string $path): array
	{
		try {
			$result = $this->runUser("v-run-cli-cmd", ["ls", $path]);

			return array_filter(explode('\n', $result->output));
		} catch (ProcessFailedException) {
			throw new RuntimeException("Cannot list domain files");
		}
	}

	public function readFile(string $path): string
	{
		try {
			$result = $this->runUser("v-open-fs-file", [$path]);

			return $result->output;
		} catch (ProcessFailedException) {
			throw new RuntimeException(sprintf('Failed to remove directory "%s"', $path));
		}
	}

	public function createFile(string $path, string $contents): void
	{
		$tmpFile = tempnam("/tmp", "hst.");

		if (!$tmpFile) {
			throw new RuntimeException("Error creating temp file");
		}

		if (!file_put_contents($tmpFile, $contents)) {
			throw new RuntimeException("Error writing to temp file");
		}

		chmod($tmpFile, 0644);

		$this->runUser("v-copy-fs-file", [$tmpFile, $path]);

		unlink($tmpFile);
	}

	public function moveFile(string $fromPath, string $toPath): void
	{
		try {
			$this->runUser("v-move-fs-file", [$fromPath, $toPath]);
		} catch (ProcessFailedException) {
			throw new RuntimeException(
				sprintf('Failed to move file "%s" to "%s"', $fromPath, $toPath)
			);
		}
	}

	public function changeFilePermissions(string $filePath, string $permission): void
	{
		try {
			$this->runUser("v-change-fs-file-permission", [$filePath, $permission]);
		} catch (ProcessFailedException) {
			throw new RuntimeException(
				sprintf('Failed to change file "%s" permissions to "%s"', $filePath, $permission)
			);
		}
	}

	public function deleteFile(string $filePath): void
	{
		try {
			$this->runUser("v-delete-fs-file", [$filePath]);
		} catch (ProcessFailedException) {
			throw new RuntimeException(sprintf('Failed to delete file "%s"', $filePath));
		}
	}

	public function archiveExtract(string $filePath, string $extractDirectoryPath): void {
		if (!realpath($filePath)) {
			throw new Exception("Error extracting archive: archive file not found");
		}

		if (empty($extractDirectoryPath)) {
			throw new Exception("Error extracting archive: missing target folder");
		}

		try {
			$this->runUser("v-extract-fs-archive", [$filePath, $extractDirectoryPath]);

			unlink($filePath);
		} catch (ProcessFailedException) {
			throw new RuntimeException(sprintf('Failed to extract "%s"', $filePath));
		}
	}

	public function sendPostRequest($url, array $formData, array $headers = []): void {
		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($formData));

		if ($headers !== []) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}

		curl_exec($ch);

		$error = curl_error($ch);
		$errno = curl_errno($ch);

		curl_close($ch);

		if (0 !== $errno) {
			throw new RuntimeException($error, $errno);
		}
	}

	public function downloadUrl(string $url, string $path): void {
		try {
			$this->runUser(
				"v-run-cli-cmd",
				[
					"/usr/bin/wget",
					"--tries",
					"3",
					"--timeout=30",
					"--no-dns-cache",
					"-nv",
					$url,
					"-P",
					$path,
				],
			);
		} catch (ProcessFailedException) {
			throw new RuntimeException(sprintf('Failed to download "%s"', $url));
		}
	}

	// Effective user
	public function user(): string {
		$user = $_SESSION["user"];

		if ($_SESSION["userContext"] === "admin" && !empty($_SESSION["look"])) {
			$user = $_SESSION["look"];
		}

		if (strpos($user, DIRECTORY_SEPARATOR) !== false) {
			throw new Exception("illegal characters in username");
		}
		return $user;
	}

	public function getUserHomeDir() {
		$info = posix_getpwnam($this->user());
		return $info["dir"];
	}

	public function userOwnsDomain(string $domain): bool {
		try {
			$this->runUser("v-list-web-domain", [$domain, "json"]);

			return true;
		} catch (ProcessFailedException) {
			return false;
		}
	}

	public function checkDatabaseLimit(): bool {
		try {
			$result = $this->runUser("v-list-user", ["json"]);

			$userInfo = $result->getOutputJson()[$this->user()];

			return $userInfo["DATABASES"] === "unlimited"
				|| ($userInfo["DATABASES"] - $userInfo["U_DATABASES"]) < 1;
		} catch (ProcessFailedException) {
			throw new RuntimeException('Unable to check database limit');
		}
	}

	public function databaseAdd(
		string $dbname,
		string $dbuser,
		string $dbpass,
		string $dbtype = "mysql",
		string $dbhost = "localhost",
		string $charset = "utf8mb4",
	) {
		$passwordFile = tempnam("/tmp", "hst");

		$fp = fopen($passwordFile, "w");
		fwrite($fp, $dbpass . "\n");
		fclose($fp);

		try {
			$this->runUser("v-add-database", [
				$dbname,
				$dbuser,
				$passwordFile,
				$dbtype,
				$dbhost,
				$charset,
			]);
		} catch (ProcessFailedException) {
			throw new RuntimeException(_("Unable to add database!"));
		} finally {
			unlink($passwordFile);
		}
	}

	public function getCurrentBackendTemplate(string $domain) {
		$result = $this->runUser("v-list-web-domain", [$domain, "json"]);
		$version = $result->getOutputJson()[$domain]["BACKEND"];
		if (!empty($version)) {
			if ($version != "default") {
				$test = preg_match("/^.*PHP-([0-9])\_([0-9])/", $version, $match);
				return $match[1] . "." . $match[2];
			} else {
				$result = $this->run("v-list-sys-php", ["json"]);
				return $result->getOutputJson()[0];
			}
		} else {
			$result = $this->run("v-list-sys-php", ["json"]);
			return $result->getOutputJson()[0];
		}
	}

	public function changeWebTemplate(string $domain, string $template) {
		$this->runUser("v-change-web-domain-tpl", [$domain, $template]);
	}

	public function changeWebDocumentRoot(string $domain, string $docroot) {
		$docroot = rtrim($docroot, "/");

		$this->runUser("v-change-web-domain-docroot", [$domain, $domain, $docroot, 'yes']);
	}

	public function changeBackendTemplate(string $domain, string $template) {
		$this->runUser("v-change-web-domain-backend-tpl", [$domain, $template]);
	}

	public function listSuportedPHP() {
		if (!$this->phpsupport) {
			$result = $this->run("v-list-sys-php", ["json"]);
			$this->phpsupport = $result->getOutputJson();
		}
		return $this->phpsupport;
	}

	/*
		Return highest available supported php version
		Eg: Package requires: 7.3 or 7.4 and system has 8.0 and 7.4 it will return 7.4
				Package requires: 8.0 or 8.1 and system has 8.0 and 7.4 it will return 8.0
				Package requires: 7.4 or 8.0 and system has 8.0 and 7.4 it will return 8.0
				If package isn't supported by the available php version false will returned
		*/
	public function getSupportedPHP($support) {
		$versions = $this->listSuportedPHP();
		$supported = false;
		$supported_versions = [];

		foreach ($versions as $version) {
			if (in_array($version, $support)) {
				$supported = true;
				$supported_versions[] = $version;
			}
		}
		if ($supported) {
			return $supported_versions;
		} else {
			return false;
		}
	}

	public function getWebDomain(string $domainName): WebDomain
	{
		try {
			$result = $this->runUser("v-list-web-domain", [$domainName, "json"]);

			return new WebDomain(
				$domainName,
				Util::join_paths($this->getUserHomeDir(), "web", $domainName),
				filter_var($result->getOutputJson()[$domainName]["IP"], FILTER_VALIDATE_IP),
				$result->getOutputJson()[$domainName]["SSL"] === "yes"
			);
		} catch (ProcessFailedException) {
			throw new Exception("Cannot find domain for user");
		}
	}

	public function getWebDomainPath(string $domain) {
		return Util::join_paths($this->getUserHomeDir(), "web", $domain);
	}

	public function runComposer($phpVersion, $args): HestiaCommandResult {
		$this->runUser("v-add-user-composer", ["2", "yes"]);

		$composerBin = $this->getUserHomeDir() . "/.composer/composer";

		return $this->runPHP($phpVersion, $composerBin, $args);
	}

	public function runWp($phpVersion, $args): HestiaCommandResult {
		$this->runUser("v-add-user-wp-cli", ["yes"]);

		$wpCliBin = $this->getUserHomeDir() . "/.wp-cli/wp";

		return $this->runPHP($phpVersion, $wpCliBin, $args);
	}

	public function runPHP(string $phpVersion, string $command, array $arguments): HestiaCommandResult
	{
		$phpCommand = [
			"/usr/bin/php" . $phpVersion,
			$command,
			...$arguments,
		];

		try {
			return $this->runUser("v-run-cli-cmd", $phpCommand);
		} catch (ProcessFailedException $exception) {
			throw new RuntimeException(
				sprintf(
					'Failed to run php command "%s"',
					$exception->getProcess()->getCommandLine(),
				),
			);
		}
	}

	private function runUser(string $cmd, array $args): HestiaCommandResult {
		return $this->run($cmd, [$this->user(), ...$args]);
	}

	private function run(string $cmd, array $args): HestiaCommandResult
	{
		$cli_script = realpath(HESTIA_DIR_BIN . $cmd);

		$command = [
			'/usr/bin/sudo',
			$cli_script,
			...$args,
		];

		// Escape spaces to disallow splitting commands and allow spaces in names like site names
		$command = array_map(
			fn (string $argument) => str_replace(" ", "\\ ", $argument),
			$command,
		);

		$process = new Process($command);
		$process->run();

		if (!$process->isSuccessful()) {
			//log error message in nginx-error.log
			trigger_error($process->getCommandLine() . " | " . $process->getOutput());
			//throw exception if command fails
			throw new ProcessFailedException($process);
		}

		return new HestiaCommandResult(
			$process->getCommandLine(),
			$process->getExitCode(),
			$process->getOutput(),
		);
	}
}
