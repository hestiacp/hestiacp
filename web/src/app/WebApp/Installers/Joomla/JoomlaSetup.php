<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\Joomla;

use Hestia\System\Util;
use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;

class JoomlaSetup extends BaseSetup
{
    protected array $info = [
        'name' => 'Joomla',
        'group' => 'cms',
        'version' => '5.2.3',
        'thumbnail' => 'joomla_thumb.png',
    ];

    protected array $config = [
        'form' => [
            'admin_username' => [
                'type' => 'text',
                'value' => 'admin',
                'placeholder' => 'Admin Username',
            ],
            'admin_password' => [
                'type' => 'password',
                'value' => '',
                'placeholder' => 'Admin Password',
            ],
            'admin_email' => [
                'type' => 'text',
                'value' => '',
                'placeholder' => 'Admin Email',
            ],
        ],
        'database' => true,
        'resources' => [
            'archive' => [
                'src' => 'https://downloads.joomla.org/cms/'
                    . 'joomla5/5-2-3/Joomla_5-2-3-Stable-Full_Package.zip?format=zip',
            ],
        ],
        'server' => [
            'nginx' => [
                'template' => 'joomla',
            ],
            'php' => [
                'supported' => ['7.4', '8.0', '8.1', '8.2', '8.3'],
            ],
        ],
    ];

    protected function setupApplication(InstallationTarget $target, array $options): void
    {
        $this->appcontext->moveFile(
            $target->getDocRoot('htaccess.txt'),
            $target->getDocRoot('.htaccess'),
        );

        $this->appcontext->runPHP(
            $options['php_version'],
            $target->getDocRoot('installation/joomla.php'),
            [
                'install',
                '--site-name=Joomla',
                '--admin-user=' . $options['admin_username'],
                '--admin-username=' . $options['admin_username'],
                '--admin-password=' . $options['admin_password'],
                '--admin-email=' . $options['admin_email'],
                '--db-user=' . $target->database->user,
                '--db-pass=' . $target->database->password,
                '--db-name=' . $target->database->name,
                '--db-host=' . $target->database->host,
                '--db-type=mysqli',
                '--no-interaction',
            ],
        );
    }
}
