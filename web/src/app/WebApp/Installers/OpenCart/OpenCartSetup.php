<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\OpenCart;

use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;

class OpenCartSetup extends BaseSetup
{
    protected array $info = [
        'name' => 'OpenCart',
        'group' => 'ecommerce',
        'version' => '4.1.0.3',
        'thumbnail' => 'opencart-thumb.png',
    ];

    protected array $config = [
        'form' => [
            'username' => ['value' => 'ocadmin'],
            'email' => 'text',
            'password' => 'password',
        ],
        'database' => true,
        'resources' => [
            'archive' => [
                'src' =>
                    'https://github.com/opencart/opencart/releases/download/4.1.0.3/opencart-4.1.0.3.zip',
                'dst' => '/tmp-opencart',
            ],
        ],
        'server' => [
            'nginx' => [
                'template' => 'opencart',
            ],
            'php' => [
                'supported' => ['8.0', '8.1', '8.2', '8.3', '8.4', '8.5'],
            ],
        ],
    ];

    protected function setupApplication(InstallationTarget $target, array $options): void
    {
        $extractDirectory = $this->config['resources']['archive']['dst'];

        $this->appcontext->copyDirectory(
            $target->getDocRoot($extractDirectory . '/upload/.'),
            $target->getDocRoot(),
        );

        $this->appcontext->moveFile(
            $target->getDocRoot('config-dist.php'),
            $target->getDocRoot('config.php'),
        );

        $this->appcontext->moveFile(
            $target->getDocRoot('admin/config-dist.php'),
            $target->getDocRoot('admin/config.php'),
        );

        $this->appcontext->moveFile(
            $target->getDocRoot('.htaccess.txt'),
            $target->getDocRoot('.htaccess'),
        );

        $this->appcontext->runPHP(
            $options['php_version'],
            $target->getDocRoot('/install/cli_install.php'),
            [
                'install',
                '--db_hostname', $target->database->host,
                '--db_username', $target->database->user,
                '--db_password', $target->database->password,
                '--db_database', $target->database->name,
                '--username', $options['username'],
                '--password', $options['password'],
                '--email', $options['email'],
                '--http_server', $target->getUrl() . '/',
            ],
        );

        $this->appcontext->deleteDirectory($target->getDocRoot('/install'));
        $this->appcontext->deleteDirectory($target->getDocRoot($extractDirectory));
    }
}
