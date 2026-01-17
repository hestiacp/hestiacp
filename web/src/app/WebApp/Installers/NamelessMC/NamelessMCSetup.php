<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\NamelessMC;

use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;

class NamelessMCSetup extends BaseSetup
{
    protected array $info = [
        'name' => 'NamelessMC',
        'group' => 'cms',
        'version' => '2.1.2',
        'thumbnail' => 'namelessmc.png',
    ];

    protected array $config = [
        'form' => [
            'protocol' => [
                'type' => 'select',
                'options' => ['http', 'https'],
                'value' => 'https',
            ],
        ],
        'database' => false,
        'resources' => [
            'archive' => [
                'src' =>
                    'https://github.com/NamelessMC/Nameless/releases/download/v2.1.2/nameless-deps-dist.zip',
            ],
        ],
        'server' => [
            'nginx' => [
                'template' => 'namelessmc',
            ],
            'apache2' => [
                'template' => 'namelessmc',
            ],
            'php' => [
                'supported' => ['7.4', '8.0', '8.1'],
            ],
        ],
    ];

    protected function setupApplication(InstallationTarget $target, array $options): void
    {
        // Nothing to do after installation of resources
    }
}
