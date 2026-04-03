<?php
declare(strict_types=1);

namespace App\Users\User;

readonly class Password
{
    public function __construct(
        private string $password,
    ) {
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
