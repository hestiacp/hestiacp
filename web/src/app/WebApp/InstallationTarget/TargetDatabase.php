<?php

declare(strict_types=1);

namespace Hestia\WebApp\InstallationTarget;

class TargetDatabase
{
    public function __construct(
        public readonly string $host,
        public readonly string $name,
        public readonly string $user,
        public readonly string $password,
        public readonly bool $createDatabase,
    ) {
    }

    public static function noDatabase(): self
    {
        return new self('', '', '', '', false);
    }
}
