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
        'version' => '4.0.2.2',
        'thumbnail' => 'opencart-thumb.png',
    ];

    protected array $config = [
        'form' => [
            'opencart_account_username' => ['value' => 'ocadmin'],
            'opencart_account_email' => 'text',
            'opencart_account_password' => 'password',
        ],
        'database' => true,
        'resources' => [
            'archive' => [
                'src' =>
                    'https://github.com/opencart/opencart/releases/download/4.0.2.2/opencart-4.0.2.2.zip',
                'dst' => '/tmp-prestashop',
            ],
        ],
        'server' => [
            'nginx' => [
                'template' => 'opencart',
            ],
            'php' => [
                'supported' => ['7.4', '8.0', '8.1', '8.2', '8.3'],
            ],
        ],
    ];

    protected function setupApplication(InstallationTarget $target, array $options = null): void
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
                '--db_hostname',
                $target->database->host,
                '--db_username',
                $target->database->user,
                '--db_password',
                $target->database->password,
                '--db_database',
                $target->database->name,
                '--username',
                $options['opencart_account_username'],
                '--password',
                $options['opencart_account_password'],
                '--email',
                $options['opencart_account_email'],
                '--http_server',
                $target->getUrl() . '/',
            ],
        );

        $this->appcontext->deleteDirectory($target->getDocRoot('/install'));
        $this->appcontext->deleteDirectory($target->getDocRoot($extractDirectory));
    }
}
