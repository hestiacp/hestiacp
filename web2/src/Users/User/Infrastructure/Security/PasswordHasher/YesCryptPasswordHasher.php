<?php

declare(strict_types=1);

namespace App\Users\User\Infrastructure\Security\PasswordHasher;

use Symfony\Component\PasswordHasher\Exception\LogicException;
use Symfony\Component\PasswordHasher\Hasher\CheckPasswordLengthTrait;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;
use function implode;

class YesCryptPasswordHasher implements PasswordHasherInterface
{
	use CheckPasswordLengthTrait;

	public function hash(string $plainPassword): string
	{
		$stream = new InputStream();
		$process = new Process(['mkpasswd', '--method=yescrypt', '-s']);
		$process->setInput($stream);
		$process->start();

		$stream->write($plainPassword);
		$stream->close();

		$process->wait();

		if (!$process->isSuccessful()) {
			throw new LogicException(sprintf(
				'Unable to hash password, error message: %s',
				$process->getErrorOutput(),
			));
		}

		return trim($process->getOutput());
	}

	public function verify(string $hashedPassword, string $plainPassword): bool
	{
		$salt = $this->extractSaltFromPassword($hashedPassword);

		$stream = new InputStream();
		$process = new Process(['mkpasswd', '--method=yescrypt', '-s', '-S', $salt]);
		$process->setInput($stream);
		$process->start();

		$stream->write($plainPassword);
		$stream->close();

		$process->wait();

		if (!$process->isSuccessful()) {
			throw new LogicException(sprintf(
				'Unable to hash password, error message: %s',
				$process->getErrorOutput(),
			));
		}

		return $hashedPassword === trim($process->getOutput());
	}

	public function needsRehash(string $hashedPassword): bool
	{
		return false;
	}

	/**
	 * extract the salt from the yescrypt password. Sending in the full password to mkpasswd will
	 * also work. But I feel that sending in the full hashed password into mkpasswd reduces the
	 * trust in the generated hash.
	 */
	private function extractSaltFromPassword(string $hashedPassword): string
	{
		$hashedPasswordParts = explode('$', $hashedPassword);

		array_pop($hashedPasswordParts);

		return implode('$', $hashedPasswordParts);
	}
}
