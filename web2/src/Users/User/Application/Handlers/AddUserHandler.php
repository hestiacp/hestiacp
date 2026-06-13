<?php

declare(strict_types=1);

namespace App\Users\User\Application\Handlers;

use App\Users\User\AuthenticationSettings;
use App\Users\User\ContactInfo;
use App\Users\User\PanelSettings;
use App\Users\User\Password;
use App\Users\User\ServerSettings;
use App\Users\User\User;
use App\Users\User\Username;
use App\Users\User\UserRepository;

readonly class AddUserHandler
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    public function __invoke(AddUser $command): void
    {
        $user = User::add(
            new Username($command->username),
            new Password($command->password),
            new ContactInfo(
                $command->contactName,
                $command->email,
            ),
            PanelSettings::initial($command->language),
            AuthenticationSettings::initial($command->isLoginEnabled),
            new ServerSettings(
                $command->sshAccessShell,
                $command->phpCliVersion,
                $command->defaultNameservers,
            )
        );

        $this->userRepository->persist($user);
    }
}
