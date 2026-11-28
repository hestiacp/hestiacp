<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\GLPI;

use Hestia\System\HestiaApp;
use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;

class GLPISetup extends BaseSetup
{
    protected array $info = [
        'name' => 'GLPI',
        'group' => 'ITSM',
        'version' => '12.0.2',
        'thumbnail' => 'glpi-thumb.png',
    ];

    public function __construct(HestiaApp $appcontext)
    {
        parent::__construct($appcontext);

        $this->config = [
            'database' => true,
            'form' => [],
            'server' => [
                'nginx' => [
                    'template' => 'glpi',
                ],
                'php' => [
                    'supported' => ['8.3', '8.4', '8.5'],
                    'extensions' => ['bcmath', 'curl', 'gd', 'intl', 'mbstring', 'mysqli', 'zlib', 'openssl'],
                ],
            ],
            'resources' => [
                'archive' => [
                    'src' => sprintf('https://github.com/glpi-project/glpi/releases/download/%1$s/glpi-%1$s.tgz', $this->info['version']),
                ],
            ],
        ];
    }

    protected function setupApplication(InstallationTarget $target, array $options): void
    {
        // Pass 100M limits so GLPI's database installer saves them to the DB.
        $this->appcontext->runPHP($options['php_version'], '-d', [
            'upload_max_filesize=100M',
            '-d',
            'post_max_size=100M',
            $target->getDocRoot('bin/console'),
            'db:install',
            '--db-host=' . $target->database->host,
            '--db-name=' . $target->database->name,
            '--db-user=' . $target->database->user,
            '--db-password=' . $target->database->password,
            '--no-interaction'
        ]);

        // Set url_base dynamically based on SSL status
        $this->appcontext->runPHP($options['php_version'], $target->getDocRoot('bin/console'), [
            'config:set',
            'url_base',
            $target->getUrl(),
            '--no-interaction'
        ]);

        // Set up the cron job to run every minute
        $cronCommand = sprintf(
            '%s %s > /dev/null 2>&1',
            escapeshellarg('/usr/bin/php' . $options['php_version']),
            escapeshellarg($target->getDocRoot('front/cron.php')),
        );
        $this->appcontext->addCronJob('*', '*', '*', '*', '*', $cronCommand);

        // Remove the web installer
        $this->appcontext->deleteFile($target->getDocRoot('install/install.php'));
    }
}
