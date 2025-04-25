<?php
declare(strict_types=1);

namespace App\Users\User;

readonly class AuthenticationSettings
{
    /**
     * @param string[] $loginIpAllowList
     */
    public function __construct(
        private bool $isTwoFAEnabled,
        private bool $isSuspended,
        private bool $isLoginEnabled,
        private array $loginIpAllowList,
    ) {
    }

    public function isTwoFAEnabled(): bool
    {
        return $this->isTwoFAEnabled;
    }

    public function isSuspended(): bool
    {
        return $this->isSuspended;
    }

    public function isLoginEnabled(): bool
    {
        return $this->isLoginEnabled;
    }

    public function getLoginIpAllowList(): array
    {
        return $this->loginIpAllowList;
    }
}
