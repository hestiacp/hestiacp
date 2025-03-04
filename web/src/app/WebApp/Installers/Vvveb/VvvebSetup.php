<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\Vvveb;

use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;

class VvvebSetup extends BaseSetup
{
    protected array $info = [
        'name' => 'Vvveb',
        'group' => 'cms',
        'version' => 'latest',
        'thumbnail' => 'vvveb-symbol.svg',
    ];

    protected array $config = [
        'form' => [
            'vvveb_account_username' => ['value' => 'admin'],
            'vvveb_account_email' => 'text',
            'vvveb_account_password' => 'password',
        ],
        'database' => true,
        'resources' => [
            'archive' => [
                'src' => 'https://www.vvveb.com/latest.zip',
            ],
        ],
        'server' => [
            'nginx' => [
                'template' => 'vvveb',
            ],
            'php' => [
                'supported' => ['7.4', '8.0', '8.1', '8.2', '8.3', '8.4'],
            ],
        ],
    ];

    protected function setupApplication(InstallationTarget $target, array $options = null): void
    {
        $this->appcontext->runPHP($options['php_version'], $target->getDocRoot('/cli.php'), [
            'install',
            'host=' . $target->database->host,
            'user=' . $target->database->user,
            'password=' . $target->database->password,
            'database=' . $target->database->name,
            'admin[user]=' . $options['vvveb_account_username'],
            'admin[password]=' . $options['vvveb_account_password'],
            'admin[email]=' . $options['vvveb_account_email'],
        ]);
    }
}
