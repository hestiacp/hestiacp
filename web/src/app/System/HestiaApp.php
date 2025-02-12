<?php

declare(strict_types=1);

namespace Hestia\System;

use Exception;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

use function _;
use function array_column;
use function array_filter;
use function basename;
use function chmod;
use function explode;
use function in_array;
use function realpath;
use function sprintf;
use function str_contains;
use function trigger_error;
use function unlink;

use function var_dump;
use const DIRECTORY_SEPARATOR;

class HestiaApp
{
    public function addDirectory(string $path): void
    {
        try {
            $this->runUser('v-add-fs-directory', [$path]);
        } catch (ProcessFailedException) {
            throw new RuntimeException(sprintf('Failed to add directory "%s"', $path));
        }
    }

    public function copyDirectory(string $fromPath, string $toPath): void
    {
        try {
            $this->runUser('v-copy-fs-directory', [$fromPath, $toPath]);
        } catch (ProcessFailedException) {
            throw new RuntimeException(
                sprintf('Failed to copy directory "%s" to "%s"', $fromPath, $toPath),
            );
        }
    }

    public function deleteDirectory(string $path): void
    {
        try {
            $this->runUser('v-delete-fs-directory', [$path]);
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
            $result = $this->runUser('v-run-cli-cmd', ['ls', '-A', $path]);

            return array_filter(explode("\n", $result->output));
        } catch (ProcessFailedException) {
            throw new RuntimeException('Cannot list domain files');
        }
    }

    public function readFile(string $path): string
    {
        try {
            $result = $this->runUser('v-open-fs-file', [$path]);

            return $result->output;
        } catch (ProcessFailedException) {
            throw new RuntimeException(sprintf('Failed to remove directory "%s"', $path));
        }
    }

    public function createFile(string $path, string $contents): void
    {
        $tmpFile = tempnam('/tmp', 'hst.');

        if (!$tmpFile) {
            throw new RuntimeException('Error creating temp file');
        }

        if (!file_put_contents($tmpFile, $contents)) {
            throw new RuntimeException('Error writing to temp file');
        }

        chmod($tmpFile, 0644);

        $this->runUser('v-copy-fs-file', [$tmpFile, $path]);

        unlink($tmpFile);
    }

    public function moveFile(string $fromPath, string $toPath): void
    {
        try {
            $this->runUser('v-move-fs-file', [$fromPath, $toPath]);
        } catch (ProcessFailedException) {
            throw new RuntimeException(
                sprintf('Failed to move file "%s" to "%s"', $fromPath, $toPath),
            );
        }
    }

    public function changeFilePermissions(string $filePath, string $permission): void
    {
        try {
            $this->runUser('v-change-fs-file-permission', [$filePath, $permission]);
        } catch (ProcessFailedException) {
            throw new RuntimeException(
                sprintf('Failed to change file "%s" permissions to "%s"', $filePath, $permission),
            );
        }
    }

    public function deleteFile(string $filePath): void
    {
        try {
            $this->runUser('v-delete-fs-file', [$filePath]);
        } catch (ProcessFailedException) {
            throw new RuntimeException(sprintf('Failed to delete file "%s"', $filePath));
        }
    }

    public function archiveExtract(string $filePath, string $extractDirectoryPath): void
    {
        if (!realpath($filePath)) {
            throw new RuntimeException('Error extracting archive: archive file not found');
        }

        if (empty($extractDirectoryPath)) {
            throw new RuntimeException('Error extracting archive: missing target folder');
        }

        try {
            $this->runUser('v-extract-fs-archive', [$filePath, $extractDirectoryPath, '', 1]);

            unlink($filePath);
        } catch (ProcessFailedException) {
            throw new RuntimeException(sprintf('Failed to extract "%s"', $filePath));
        }
    }

    public function downloadUrl(string $url, string $path): string
    {
        try {
            $result = $this->runUser('v-run-cli-cmd', [
                '/usr/bin/wget',
                '--tries',
                '3',
                '--timeout=30',
                '--no-dns-cache',
                '-nv',
                $url,
                '-P',
                $path,
            ]);

            $pattern = '/URL:\s*.+?\s*\[.+?\]\s*->\s*"(.+?)"/';
            if (preg_match($pattern, $result->output, $matches) && count($matches) > 1) {
                return $matches[1];
            }

            // Fallback on guessed result
            return $path . '/' . basename($url);
        } catch (ProcessFailedException) {
            throw new RuntimeException(
                sprintf('Failed to download "%s" to path "%s"', $url, $path),
            );
        }
    }

    public function sendPostRequest($url, array $formData, array $headers = []): void
    {
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

    // Effective user
    public function user(): string
    {
        $user = $_SESSION['user'];

        if ($_SESSION['userContext'] === 'admin' && !empty($_SESSION['look'])) {
            $user = $_SESSION['look'];
        }

        if (str_contains($user, DIRECTORY_SEPARATOR)) {
            throw new Exception('illegal characters in username');
        }
        return $user;
    }

    public function getUserHomeDir(): string
    {
        $info = posix_getpwnam($this->user());
        return $info['dir'];
    }

    public function userOwnsDomain(string $domain): bool
    {
        try {
            $this->runUser('v-list-web-domain', [$domain, 'json']);

            return true;
        } catch (ProcessFailedException) {
            return false;
        }
    }

    /**
     * @return string[]
     */
    public function getDatabaseHosts(string $type): array
    {
        try {
            $result = $this->run('v-list-database-hosts', ['json']);
        } catch (ProcessFailedException) {
            throw new RuntimeException('Failed to list database hosts');
        }

        $hostOfType = array_filter(
            $result->getOutputJson(),
            fn(array $host) => $host['TYPE'] === $type,
        );

        return array_column($hostOfType, 'HOST');
    }

    public function checkDatabaseLimit(): bool
    {
        try {
            $result = $this->runUser('v-list-user', ['json']);

            $userInfo = $result->getOutputJson()[$this->user()];

            return $userInfo['DATABASES'] === 'unlimited' ||
                $userInfo['DATABASES'] - $userInfo['U_DATABASES'] < 1;
        } catch (ProcessFailedException) {
            throw new RuntimeException('Unable to check database limit');
        }
    }

    public function databaseAdd(
        string $name,
        string $user,
        string $password,
        string $host,
        string $type = 'mysql',
        string $charset = 'utf8mb4',
    ): void {
        $passwordFile = tempnam('/tmp', 'hst');

        $fp = fopen($passwordFile, 'w');
        fwrite($fp, $password . "\n");
        fclose($fp);

        try {
            $this->runUser('v-add-database', [$name, $user, $passwordFile, $type, $host, $charset]);
        } catch (ProcessFailedException) {
            throw new RuntimeException(_('Unable to add database!'));
        } finally {
            unlink($passwordFile);
        }
    }

    public function changeWebTemplate(string $domain, string $template): void
    {
        try {
            $this->runUser('v-change-web-domain-tpl', [$domain, $template]);
        } catch (ProcessFailedException) {
            throw new RuntimeException(sprintf('Failed to change to template "%s"', $template));
        }
    }

    public function changeBackendTemplate(string $domain, string $template): void
    {
        try {
            $this->runUser('v-change-web-domain-backend-tpl', [$domain, $template]);
        } catch (ProcessFailedException) {
            throw new RuntimeException(
                sprintf('Failed to change backend template to "%s"', $template),
            );
        }
    }

    public function getSupportedPHPVersions(array $supportedPHP): array
    {
        try {
            // Load installed PHP Versions
            $result = $this->run('v-list-sys-php', ['json']);

            $installedPHPVersions = array_filter(
                $result->getOutputJson(),
                fn(string $installedPHP) => in_array($installedPHP, $supportedPHP, true),
            );

            sort($installedPHPVersions);

            return $installedPHPVersions;
        } catch (ProcessFailedException) {
            throw new RuntimeException('Failed to load installed PHP versions');
        }
    }

    public function getWebDomain(string $domainName): WebDomain
    {
        try {
            $result = $this->runUser('v-list-web-domain', [$domainName, 'json']);

            return new WebDomain(
                $domainName,
                Util::joinPaths($this->getUserHomeDir(), 'web', $domainName),
                filter_var($result->getOutputJson()[$domainName]['IP'], FILTER_VALIDATE_IP),
                $result->getOutputJson()[$domainName]['SSL'] === 'yes',
            );
        } catch (ProcessFailedException) {
            throw new Exception('Cannot find domain for user');
        }
    }

    public function runComposer(string $phpVersion, array $arguments): HestiaCommandResult
    {
        $this->runUser('v-add-user-composer', ['2', 'yes']);

        $composerBin = $this->getUserHomeDir() . '/.composer/composer';

        return $this->runPHP($phpVersion, $composerBin, $arguments);
    }

    public function runWp(string $phpVersion, array $arguments): HestiaCommandResult
    {
        $this->runUser('v-add-user-wp-cli', ['yes']);

        $wpCliBin = $this->getUserHomeDir() . '/.wp-cli/wp';

        return $this->runPHP($phpVersion, $wpCliBin, $arguments);
    }

    public function runPHP(
        string $phpVersion,
        string $command,
        array $arguments,
    ): HestiaCommandResult {
        $phpCommand = ['/usr/bin/php' . $phpVersion, $command, ...$arguments];

        try {
            return $this->runUser('v-run-cli-cmd', $phpCommand);
        } catch (ProcessFailedException $exception) {
            throw new RuntimeException(
                sprintf(
                    'Failed to run php command "%s"',
                    $exception->getProcess()->getCommandLine(),
                ),
            );
        }
    }

    private function runUser(string $cmd, array $arguments): HestiaCommandResult
    {
        return $this->run($cmd, [$this->user(), ...$arguments]);
    }

    private function run(string $cmd, array $arguments): HestiaCommandResult
    {
        $cli_script = realpath(HESTIA_DIR_BIN . $cmd);

        $command = ['/usr/bin/sudo', $cli_script, ...$arguments];

        // Escape spaces to disallow splitting commands and allow spaces in names like site names
        $command = array_map(fn(string $argument) => str_replace(' ', '\\ ', $argument), $command);

        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful()) {
            //log error message in nginx-error.log
            trigger_error($process->getCommandLine() . ' | ' . $process->getOutput());
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
