<?php

declare(strict_types=1);

namespace App\Users\User\Application\Handlers;

use App\Users\User\AuthenticationSettings;
use App\Users\User\ContactInfo;
use App\Users\User\PanelSettings;
use App\Users\User\Password;
use App\Users\User\Role;
use App\Users\User\ServerSettings;
use App\Users\User\User;
use App\Users\User\Username;
use App\Users\User\UserRepository;

readonly class ChangeUserHandler
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    public function __invoke(ChangeUser $command): void
    {
        $user = $this->userRepository->getByUsername(new Username($command->username));

        $user->change(
            new Password($command->password),
            new Role($command->role),
            new ContactInfo(
                $command->contactName,
                $command->email,
            ),
            new PanelSettings(
                $command->language,
                $command->theme,
                $command->sortOrder,
            ),
            new AuthenticationSettings(
                $command->isLoginEnabled,
                $command->isTwoFAEnabled,
                $command->isSuspended,
                $command->loginAllowList,
            ),
            new ServerSettings(
                $command->sshAccessShell,
                $command->phpCliVersion,
                $command->defaultNameservers,
            )
        );

        $this->userRepository->persist($user);
    }
}
