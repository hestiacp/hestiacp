<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\ThirtyBees;

use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;

class ThirtyBeesSetup extends BaseSetup
{
    protected array $info = [
        'name' => 'ThirtyBees',
        'group' => 'ecommerce',
        'version' => '1.5.1',
        'thumbnail' => 'thirtybees-thumb.png',
    ];

    protected array $config = [
        'form' => [
            'thirtybees_account_first_name' => ['value' => ''],
            'thirtybees_account_last_name' => ['value' => ''],
            'thirtybees_account_email' => 'text',
            'thirtybees_account_password' => 'password',
        ],
        'database' => true,
        'resources' => [
            'archive' => [
                'src' =>
                    'https://github.com/thirtybees/thirtybees/' .
                    'releases/download/1.6.0/thirtybees-v1.6.0-php7.4.zip',
            ],
        ],
        'server' => [
            'nginx' => [
                'template' => 'prestashop',
            ],
            'php' => [
                'supported' => ['7.4', '8.0', '8.1', '8.2', '8.3'],
            ],
        ],
    ];

    protected function setupApplication(InstallationTarget $target, array $options = null): void
    {
        $this->appcontext->runPHP(
            $options['php_version'],
            $target->getDocRoot('/install/index_cli.php'),
            [
                '--db_server=' . $target->database->host,
                '--db_user=' . $target->database->user,
                '--db_password=' . $target->database->password,
                '--db_name=' . $target->database->name,
                '--firstname=' . $options['thirtybees_account_first_name'],
                '--lastname=' . $options['thirtybees_account_last_name'],
                '--password=' . $options['thirtybees_account_password'],
                '--email=' . $options['thirtybees_account_email'],
                '--domain=' . $target->domain->domainName,
                '--ssl=' . $target->domain->isSslEnabled,
            ],
        );

        $this->appcontext->deleteDirectory($target->getDocRoot('/install'));
    }
}
