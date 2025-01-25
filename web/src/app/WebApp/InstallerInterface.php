<?php

declare(strict_types=1);

namespace Hestia\WebApp;

use Hestia\WebApp\InstallationTarget\InstallationTarget;

interface InstallerInterface
{
    public function getApplicationName(): string;

    public function getConfig(string $section = ''): mixed;

    public function install(InstallationTarget $target, array $options): void;
}
