<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\PrestaShop;

use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;

class PrestaShopSetup extends BaseSetup
{
    protected array $info = [
        'name' => 'PrestaShop',
        'group' => 'ecommerce',
        'version' => '8.1.0',
        'thumbnail' => 'prestashop-thumb.png',
    ];

    protected array $config = [
        'form' => [
            'prestashop_account_first_name' => ['value' => ''],
            'prestashop_account_last_name' => ['value' => ''],
            'prestashop_account_email' => 'text',
            'prestashop_account_password' => 'password',
        ],
        'database' => true,
        'resources' => [
            'archive' => [
                'src' =>
                    'https://github.com/PrestaShop/PrestaShop/releases/download/8.2.0/prestashop_8.2.0.zip',
                'dst' => '/tmp-prestashop',
            ],
        ],
        'server' => [
            'nginx' => [
                'template' => 'prestashop',
            ],
            'php' => [
                'supported' => ['8.0', '8.1', '8.2', '8.3'],
            ],
        ],
    ];

    protected function setupApplication(InstallationTarget $target, array $options = null): void
    {
        $extractDirectory = $this->config['resources']['archive']['dst'];

        $this->appcontext->archiveExtract(
            $target->getDocRoot($extractDirectory . '/prestashop.zip'),
            $target->getDocRoot(),
        );

        $this->appcontext->runPHP(
            $options['php_version'],
            $target->getDocRoot('/install/index_cli.php'),
            [
                '--db_server=' . $target->database->host,
                '--db_user=' . $target->database->user,
                '--db_password=' . $target->database->password,
                '--db_name=' . $target->database->name,
                '--firstname=' . $options['prestashop_account_first_name'],
                '--lastname=' . $options['prestashop_account_last_name'],
                '--password=' . $options['prestashop_account_password'],
                '--email=' . $options['prestashop_account_email'],
                '--domain=' . $target->domain->domainName,
                '--ssl=' . $target->domain->isSslEnabled ? 1 : 0,
            ],
        );

        // remove install folder
        $this->appcontext->deleteDirectory($target->getDocRoot('/install'));
        $this->appcontext->deleteDirectory($target->getDocRoot($extractDirectory));
    }
}
