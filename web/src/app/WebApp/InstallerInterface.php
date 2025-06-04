<?php

declare(strict_types=1);

namespace DevIT\WebApp;

use DevIT\WebApp\InstallationTarget\InstallationTarget;

interface InstallerInterface
{
    public function getInfo(): InstallerInfo;

    public function getConfig(string $section = ''): mixed;

    public function install(InstallationTarget $target, array $options): void;
}
