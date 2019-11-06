<?php
declare(strict_types=1);

namespace Hestia\WebApp;

interface InstallerInterface
{
    public function install(array $options = null);
}
