<?php
declare(strict_types=1);

namespace App\Users\User\Application\Query\List;

readonly class UserList
{
    /**
     * @param User[] $users
     */
    private function __construct(
        public array $users,
    ) {
    }

    public static function fromState(array $state): self
    {
        $users = [];
        foreach ($state as $username => $userState) {
            $users[] = User::fromState($username, $userState);
        }

        return new self($users);
    }
}
