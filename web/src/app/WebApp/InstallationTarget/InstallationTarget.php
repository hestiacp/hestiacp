<?php

declare(strict_types=1);

namespace Hestia\WebApp\InstallationTarget;

class InstallationTarget
{
    public function __construct(
        public readonly TargetDomain $domain,
        public TargetDatabase $database,
    ) {
    }

    public function addTargetDatabase(TargetDatabase $database): void
    {
        $this->database = $database;
    }

    public function getUrl(): string
    {
        return $this->domain->getUrl();
    }

    public function getDocRoot(string $appendedPath = ''): string
    {
        return $this->domain->getDocRoot($appendedPath);
    }
}
