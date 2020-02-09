<?php
declare(strict_types=1);

namespace Hestia\WebApp;

interface InstallerInterface
{
    public function install(array $options = null);
    public function getDocRoot(string $append_relative_path = null) : string;
    public function withDatabase() : bool;
}
