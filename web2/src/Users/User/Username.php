<?php
declare(strict_types=1);

namespace App\Users\User;

readonly class Username
{
    public function __construct(
        private string $username,
    ) {
    }

    public function getUsername(): string
    {
        return $this->username;
    }
}
