<?php

declare(strict_types=1);

namespace App\Users\User\Infrastructure\Storage;

use App\System\Hestia\Infrastructure\Cli\HestiaCommandRunner;
use App\System\Hestia\Infrastructure\Cli\HestiaCommandFailed;
use App\Users\User\Application\Query\List\UserList;
use App\Users\User\Application\Query\Package;
use App\Users\User\Application\Query\SecurityUser;
use App\Users\User\Application\Query\Usage;
use App\Users\User\Application\Query\UserQueryRepository;

class HestiaUserQueryRepository implements UserQueryRepository
{
    public function __construct(private readonly HestiaCommandRunner $hestiaCommandRunner)
    {

    }

    public function findSecurityUserByUsername(string $username): ?SecurityUser
    {
        try {
            $result = $this->hestiaCommandRunner->run('v-get-user-credentials', [$username]);

            $credentials = $result->getOutputJson();
        } catch (HestiaCommandFailed) {
            return null;
        }

        try {
            $result = $this->hestiaCommandRunner->run('v-list-user', [$username, 'json']);

            $user = $result->getOutputJson();
        } catch (HestiaCommandFailed) {
            return null;
        }

        return SecurityUser::fromState($username, $credentials, $user[$username]);
    }

    public function getUserList(): UserList
    {
        $result = $this->hestiaCommandRunner->run('v-list-users', ['json']);

        return UserList::fromState($result->getOutputJson());
    }
}
