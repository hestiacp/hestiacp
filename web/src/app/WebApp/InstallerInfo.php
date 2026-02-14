<?php

declare(strict_types=1);

namespace Hestia\WebApp;

use Hestia\WebApp\InstallationTarget\TargetDatabase;
use Hestia\WebApp\InstallationTarget\TargetDomain;

class InstallerInfo
{
    /**
     * @param string[] $supportedPHPVersions
     */
    private function __construct(
        public readonly string $name,
        public readonly string $group,
        public readonly string $version,
        public readonly string $thumbnail,
        public readonly array $supportedPHPVersions,
    ) {
    }

    public function isInstallable(): bool
    {
        return !empty($this->supportedPHPVersions);
    }

    /**
     * @param mixed[] $info
     */
    public static function fromArray(array $info): self
    {
        return new self(
            (string) $info['name'],
            (string) $info['group'],
            (string) $info['version'],
            (string) $info['thumbnail'],
            (array) $info['supportedPHPVersions'],
        );
    }
}
