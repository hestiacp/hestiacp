<?php
declare(strict_types=1);

namespace App\Users\User\Application\Query;

readonly class SecurityUser
{
	/**
	 * @param string[] $allowedLoginIps
	 * @param string[] $roles
	 */
	public function __construct(
		public string $username,
		public string $password,
		public string $salt,
		public array $roles,
		public array $allowedLoginIps,
		public bool $isLoginEnabled,
	) {
	}
}
