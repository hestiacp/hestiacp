<?php
declare(strict_types=1);

namespace App\Users\User\Application\Query\List;

use App\Users\User\Application\Query\Package;
use App\Users\User\Application\Query\Usage;

readonly class User
{
    public function __construct(
        public string $username,
        public string $name,
        public string $email,
        public Package $package,
        public Usage $usage,
        public string $createdOn,
        public bool $isSuspended,
    ) {
    }

    public static function fromState(string $username, array $state): self
    {
        return new self(
            $username,
            $state['NAME'],
            $state['CONTACT'],
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
            $state['DATE'] . ' ' . $state['TIME'],
            $state['SUSPENDED'] === 'yes',
        );
    }
}
