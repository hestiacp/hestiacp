<?php
declare(strict_types=1);

namespace App\Users\User\Infrastructure\Security;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class HestiaUserChecker implements UserCheckerInterface
{
	public function __construct(private RequestStack $requestStack)
	{
	}

	public function checkPreAuth(UserInterface $user): void
	{
		if (!$user instanceof HestiaUser) {
			return;
		}

		if (!$user->isLoginEnabled) {
			$ex = new DisabledException('User account is disabled.');
			$ex->setUser($user);

			throw $ex;
		}

		$request = $this->requestStack->getCurrentRequest();

		if (!$request || !$user->allowsLoginFromIp((string) $request->getClientIp())) {
			$ex = InvalidLoginIpException::forIp($request->getClientIp());
			$ex->setUser($user);

			throw $ex;
		}
	}

	public function checkPostAuth(UserInterface $user): void
	{
		// Not using the post auth check but need to conform to interface
	}
}

