<?php
declare(strict_types=1);

namespace App\Users\User\Application\Handlers;

readonly class DeleteUser
{
    public function __construct(
        public string $username
    )
    {

    }
}
