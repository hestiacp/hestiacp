<?php
declare(strict_types=1);

namespace App\Users\User\Application\Query;

use App\Users\User\Application\Query\List\UserList;

interface UserQueryRepository
{
    public function findSecurityUserByUsername(string $username): ?SecurityUser;

    public function getUserList(): UserList;
}
