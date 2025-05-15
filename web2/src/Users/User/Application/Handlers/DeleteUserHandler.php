<?php
declare(strict_types=1);

namespace App\Users\User\Application\Handlers;

use App\Users\User\Username;
use App\Users\User\UserRepository;

readonly class DeleteUserHandler
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    public function __invoke(DeleteUser $command): void
    {
        $user = $this->userRepository->getByUsername(new Username($command->username));
        $user->delete();

        $this->userRepository->delete($user);
    }
}
