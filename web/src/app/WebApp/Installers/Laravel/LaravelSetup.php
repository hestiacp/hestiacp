<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\Laravel;

use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;
use function file_get_contents;

class LaravelSetup extends BaseSetup
{
    protected array $info = [
        'name' => 'Laravel',
        'group' => 'framework',
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
        $this->appcontext->createFile(
            $target->getDocRoot('.htaccess'),
            file_get_contents(__DIR__ . '/.htaccess'),
        );
    }
}
