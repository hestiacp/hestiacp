<?php
declare(strict_types=1);

namespace App\Users\User\Application\Query;

interface UserQueryRepository
{
	public function findSecurityUserByUsername(string $username): ?SecurityUser;
}
