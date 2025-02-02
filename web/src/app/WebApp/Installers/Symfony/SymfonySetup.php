<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\Symfony;

use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;

class SymfonySetup extends BaseSetup
{
    protected array $info = [
        'name' => 'Symfony',
        'group' => 'framework',
        'version' => 'latest',
        'thumbnail' => 'symfony-thumb.png',
    ];

    protected array $config = [
        'form' => [],
        'database' => false,
        'resources' => [
            'composer' => ['src' => 'symfony/website-skeleton', 'dst' => '/'],
        ],
        'server' => [
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
        $this->appcontext->createFile(
            $target->getDocRoot('.htaccess'),
            '<IfModule mod_rewrite.c>
                    RewriteEngine On
                    RewriteRule ^(.*)$ public/$1 [L]
            </IfModule>',
        );

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
