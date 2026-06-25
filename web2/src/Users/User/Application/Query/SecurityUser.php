<?php
declare(strict_types=1);

namespace App\Users\User\Application\Query;

readonly class SecurityUser
{
    /**
     * @param string[] $allowedLoginIps
     * @param string[] $roles
     */
    private function __construct(
        public string $username,
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

    public static function fromState(string $username, array $credentials, array $state): SecurityUser
    {
        $allowedIps = [];
        if ($state['LOGIN_USE_IPLIST'] === 'yes') {
            $allowedIps = array_map('trim', explode(',', $state['LOGIN_ALLOW_IPS']));
        }

        return new self(
            $username,
            $state['NAME'],
            $credentials['password'],
            $credentials['salt'],
            [$state['ROLE'] === 'admin' ? 'ROLE_ADMIN' : 'ROLE_USER'],
            $allowedIps,
            $state['LOGIN_DISABLED'] === 'no',
            $state['THEME'],
            new Package(
                $state['PACKAGE'],
                $state['DISK_QUOTA'],
                $state['BANDWIDTH'],
            ),
            new Usage(
                $state['U_DISK'],
                $state['U_BANDWIDTH'],
                $state['U_WEB_DOMAINS'],
                $state['U_DNS_DOMAINS'],
                $state['U_MAIL_DOMAINS'],
                $state['U_DATABASES'],
                $state['U_BACKUPS'],
                $state['IP_OWNED'],
            ),
        );
    }
}
