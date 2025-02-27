<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\Grav;

use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;

class GravSetup extends BaseSetup
{
    protected array $info = [
        'name' => 'Grav',
        'group' => 'cms',
        'version' => 'latest',
        'thumbnail' => 'grav-symbol.svg',
    ];

    protected array $config = [
        'form' => [
            'admin' => ['type' => 'boolean', 'value' => false, 'label' => 'Create admin account'],
            'username' => ['text' => 'admin'],
            'password' => 'password',
            'email' => 'text',
        ],
        'database' => false,
        'resources' => [
            'composer' => ['src' => 'getgrav/grav', 'dst' => '/'],
        ],
        'server' => [
            'nginx' => [
                'template' => 'grav',
            ],
            'php' => [
                'supported' => ['7.4', '8.0', '8.1' . '8.2', '8.3'],
            ],
        ],
    ];

    protected function setupApplication(InstallationTarget $target, array $options): void
    {
        if ($options['admin'] == true) {
            chdir($target->getDocRoot());

            $this->appcontext->runPHP($options['php_version'], $target->getDocRoot('/bin/gpm'), [
                'install',
                'admin',
            ]);

            $this->appcontext->runPHP($options['php_version'], $target->getDocRoot('/bin/plugin'), [
                'login',
                'new-user',
                '-u',
                $options['username'],
                '-p',
                $options['password'],
                '-e',
                $options['email'],
                '-P',
                'a',
                '-N',
                $options['username'],
                '-l',
                'en',
            ]);
        }
    }
}
