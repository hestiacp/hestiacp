<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\Joomla;

use Hestia\System\Util;
use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;

class JoomlaSetup extends BaseSetup
{
    protected array $appInfo = [
        'name' => 'Joomla',
        'group' => 'cms',
        'enabled' => true,
        'version' => 'latest',
        'thumbnail' => 'joomla_thumb.png',
    ];

    protected array $config = [
        'form' => [
            'site_name' => [
                'type' => 'text',
                'value' => 'Joomla Site',
                'placeholder' => 'Joomla Site',
            ],
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
                'value' => 'admin@example.com',
                'placeholder' => 'Admin Email',
            ],
        ],
        'database' => true,
        'resources' => [
            'archive' => [
                'src' => 'https://www.joomla.org/latest',
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
        $this->appcontext->runPHP(
            $options['php_version'],
            $target->getDocRoot('/installation/joomla.php'),
            [
                'install',
                '--site-name=' . $options['site_name'],
                '--admin-user=' . $options['admin_username'],
                '--admin-username=' . $options['admin_username'],
                '--admin-password=' . $options['admin_password'],
                '--admin-email=' . $options['admin_email'],
                '--db-user=' . $target->database->user,
                '--db-pass=' . $target->database->password,
                '--db-name=' . $target->database->name,
                '--db-prefix=' . 'jl' . Util::generateString(5, false) . '_',
                '--db-host=' . $target->database->host,
                '--db-type=mysqli',
            ],
        );
    }
}
