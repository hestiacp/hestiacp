<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\Laravel;

use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;

class LaravelSetup extends BaseSetup
{
    protected array $appInfo = [
        'name' => 'Laravel',
        'group' => 'framework',
        'enabled' => true,
        'version' => 'latest',
        'thumbnail' => 'laravel-thumb.png',
    ];

    protected array $config = [
        'form' => [],
        'database' => false,
        'resources' => [
            'composer' => ['src' => 'laravel/laravel', 'dst' => '/'],
        ],
        'server' => [
            'apache2' => [
                'document_root' => 'public',
            ],
            'nginx' => [
                'template' => 'laravel',
            ],
            'php' => [
                'supported' => ['8.1', '8.2', '8.3', '8.4'],
            ],
        ],
    ];

    protected function setupApplication(InstallationTarget $target, array $options): void
    {
        // Nothing to do after installation of resources
    }
}
