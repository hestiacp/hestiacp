<?php

declare(strict_types=1);

namespace App\Users\User\Infrastructure\Security;

use App\Users\User\Application\Query\Package;
use App\Users\User\Application\Query\Usage;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use function in_array;

class HestiaUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @param string[] $roles
     */
    public function __construct(
        public readonly string $username,
        public string $contactName,
        public string $password,
        public string $salt,
        public array $roles,
        public array $allowedLoginIps,
        public bool $isLoginEnabled,
        public string $theme,
        public Package $package,
        public Usage $usage,
    ) {
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function eraseCredentials(): void
    {
        // No credentials to erase because for example erasing the password will trip the user session validation
        // and logout the user.
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function upgradeHashedPassword(string $hashedPassword): void
    {
        $this->password = $hashedPassword;
    }

    public function allowsLoginFromIp(string $ip): bool
    {
        if ($this->allowedLoginIps === []) {
            return true;
        }

        return in_array($ip, $this->allowedLoginIps, true);
    }
}
