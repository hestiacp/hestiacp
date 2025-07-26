<?php

declare(strict_types=1);

namespace App\Users\User\Infrastructure\Security;

use App\Users\User\Application\Query\SecurityUser;
use App\Users\User\Application\Query\UserQueryRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class HestiaUserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    public function __construct(
        private readonly UserQueryRepository $userQueryRepository,
    ) {
    }

    public function refreshUser(UserInterface $user): HestiaUser
    {
        if (!$user instanceof HestiaUser) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', $user::class));
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return $class === HestiaUser::class || is_subclass_of($class, HestiaUser::class);
    }

    public function loadUserByIdentifier(string $identifier): HestiaUser
    {
        $securityUser = $this->userQueryRepository->findSecurityUserByUsername($identifier);

        if (!$securityUser instanceof SecurityUser) {
            throw new UserNotFoundException(
                sprintf('User with identifier "%s" does not exist.', $identifier),
            );
        }

        return new HestiaUser(
            $securityUser->username,
            $securityUser->contactName,
            $securityUser->password,
            $securityUser->salt,
            $securityUser->roles,
            $securityUser->allowedLoginIps,
            $securityUser->isLoginEnabled,
            $securityUser->theme,
            $securityUser->package,
            $securityUser->usage,
        );
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof HestiaUser) {
            return;
        }

        // Rehash and upgrade the password here

        $user->upgradeHashedPassword($newHashedPassword);
    }
}
