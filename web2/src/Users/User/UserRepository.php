<?php

declare(strict_types=1);

namespace App\Users\User;


interface UserRepository
{
    public function getByUsername(Username $username): User;

    public function persist(User $user): void;

    public function delete(User $user): void;
}
