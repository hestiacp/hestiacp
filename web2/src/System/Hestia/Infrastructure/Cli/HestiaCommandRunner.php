<?php
declare(strict_types=1);

namespace App\System\Hestia\Infrastructure\Cli;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class HestiaCommandRunner
{
	public function __construct(private readonly string $hestiaBinDir)
	{
	}

	public function run(string $cmd, array $arguments): HestiaCommandResult
	{
		$hestiaBin = realpath($this->hestiaBinDir . $cmd);

		$command = ['/usr/bin/sudo', $hestiaBin, ...$arguments];

		try {
			$process = new Process($command);
			$process->mustRun();

			return new HestiaCommandResult(
				$process->getCommandLine(),
				$process->getExitCode(),
				$process->getOutput(),
			);
		} catch (ProcessFailedException $exception) {
			throw new HestiaCommandFailed($exception->getMessage(), $exception->getCode(), $exception);
		}
	}
}
