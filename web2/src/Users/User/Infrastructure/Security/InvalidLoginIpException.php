<?php
declare(strict_types=1);

namespace App\Users\User\Infrastructure\Security;

use Symfony\Component\Security\Core\Exception\AccountStatusException;

class InvalidLoginIpException extends AccountStatusException
{
	public static function forIp(string $ip): self
	{
		return new self(sprintf(
			'Invalid login IP "%s", ip address not in whitelist',
			$ip,
		));
	}
}
