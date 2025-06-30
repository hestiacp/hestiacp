<?php
declare(strict_types=1);

namespace App\Users\User\Application\Handlers;

readonly class AddUser
{
    /**
     * @param string[] $defaultNameservers
     */
    public function __construct(
        public string $username,
        public string $password,
        public string $contactName,
        public string $email,
        public string $language,
        public bool $isLoginEnabled,
        public string $sshAccessShell,
        public string $phpCliVersion,
        public array $defaultNameservers,
    ){
    }
}
