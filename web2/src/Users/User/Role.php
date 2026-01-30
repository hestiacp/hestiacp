<?php
declare(strict_types=1);

namespace App\Users\User;

readonly class Role
{
    public function __construct(
        private string $role,
    ) {
    }

    public function getRole(): string
    {
        return $this->role;
    }
}
