<?php
declare(strict_types=1);

namespace App\Users\User;

readonly class ServerSettings
{
    /**
     * @param string[] $defaultNameservers
     */
    public function __construct(
        private string $sshAccessShell,
        private string $phpCliVersion,
        private array $defaultNameservers,
    ) {
    }

    public function getSshAccessShell(): string
    {
        return $this->sshAccessShell;
    }

    public function getPhpCliVersion(): string
    {
        return $this->phpCliVersion;
    }

    /**
     * @return string[]
     */
    public function getDefaultNameservers(): array
    {
        return $this->defaultNameservers;
    }
}
