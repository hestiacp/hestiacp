<?php

declare(strict_types=1);

namespace App\Users\User\Infrastructure\Storage;

use App\System\Hestia\Infrastructure\Cli\HestiaCommandRunner;
use App\System\Hestia\Infrastructure\Cli\HestiaCommandFailed;
use App\Users\User\Application\Query\SecurityUser;
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

		$allowedIps = [];
		if ($user[$username]['LOGIN_USE_IPLIST'] === 'yes') {
			$allowedIps = array_map('trim', explode(',', $user[$username]['LOGIN_ALLOW_IPS']));
		}

		return new SecurityUser(
			$username,
			$credentials['password'],
			$credentials['salt'],
			[$user[$username]['ROLE'] === 'admin' ? 'ROLE_ADMIN' : 'ROLE_USER'],
			$allowedIps,
			$user[$username]['LOGIN_DISABLED'] === 'no',
		);
	}
}
