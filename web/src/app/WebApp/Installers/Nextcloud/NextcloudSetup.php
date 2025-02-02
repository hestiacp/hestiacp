<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\Nextcloud;

use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;

class NextcloudSetup extends BaseSetup
{
    protected array $info = [
        'name' => 'Nextcloud',
        'group' => 'cloud',
        'version' => 'latest',
        'thumbnail' => 'nextcloud-thumb.png',
    ];

    protected array $config = [
        'form' => [
            'username' => ['value' => 'admin'],
            'password' => 'password',
        ],
        'database' => true,
        'resources' => [
            'archive' => ['src' => 'https://download.nextcloud.com/server/releases/latest.tar.bz2'],
        ],
        'server' => [
            'nginx' => [
                'template' => 'owncloud',
            ],
            'php' => [
                'supported' => ['8.0', '8.1', '8.2', '8.3'],
            ],
        ],
    ];

    protected function setupApplication(InstallationTarget $target, array $options): void
    {
        $this->appcontext->runPHP($options['php_version'], $target->getDocRoot('occ'), [
            'maintenance:install',
            '--database',
            'mysql',
            '--database-name',
            $target->database->name,
            '--database-host',
            $target->database->host,
            '--database-user',
            $target->database->user,
            '--database-pass',
            $target->database->password,
            '--admin-user',
            $options['username'],
            '--admin-pass',
            $options['password'],
        ]);

        $this->appcontext->runPHP($options['php_version'], $target->getDocRoot('occ'), [
            'config:system:set',
            'trusted_domains',
            '2',
            '--value=' . $target->domain->domainName,
        ]);

        // Bump minimum memory limit to 512M
        $phpIni = $target->getDocRoot('.user.ini');

        $contents = $this->appcontext->readFile($phpIni);
        $contents .= 'memory_limit=512M\r\n';

        $this->appcontext->createFile($phpIni, $contents);
    }
}
