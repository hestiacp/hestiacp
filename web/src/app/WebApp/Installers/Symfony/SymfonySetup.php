<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\Symfony;

use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;

class SymfonySetup extends BaseSetup
{
    protected array $appInfo = [
        'name' => 'Symfony',
        'group' => 'framework',
        'enabled' => true,
        'version' => 'latest',
        'thumbnail' => 'symfony-thumb.png',
    ];

    protected array $config = [
        'form' => [],
        'database' => true,
        'resources' => [
            'composer' => ['src' => 'symfony/website-skeleton', 'dst' => '/'],
        ],
        'server' => [
            'apache2' => [
                'document_root' => 'public',
            ],
            'nginx' => [
                'template' => 'symfony4-5',
            ],
            'php' => [
                'supported' => ['8.2', '8.3', '8.4'],
            ],
        ],
    ];

    protected function setupApplication(InstallationTarget $target, array $options = null): void
    {
        $this->appcontext->runComposer($options['php_version'], [
            'config',
            '-d',
            $target->getDocRoot(),
            'extra.symfony.allow-contrib',
            'true',
        ]);
        $this->appcontext->runComposer($options['php_version'], [
            'require',
            '-d',
            $target->getDocRoot(),
            'symfony/apache-pack',
        ]);
    }
}
