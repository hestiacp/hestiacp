<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\GLPI;

use Hestia\System\HestiaApp;
use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;
use Hestia\WebApp\InstallerInfo;

class GLPISetup extends BaseSetup
{
    protected array $info = [
        'name' => 'GLPI',
        'group' => 'ITSM',
        'version' => '11.0.7',
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
                    'supported' => ['8.1', '8.2', '8.3', '8.4'],
                    'extensions' => ['bcmath', 'curl', 'gd', 'intl', 'mbstring', 'mysqli', 'zlib', 'openssl'],
                ],
            ],
            'resources' => [
                'archive' => [
                    'src' => 'https://github.com/glpi-project/glpi/releases/download/11.0.7/glpi-11.0.7.tgz',
                ],
            ],
        ];
    }

    protected function setupApplication(InstallationTarget $target, array $options): void
    {
        // 1. Verify PHP extensions for the selected PHP version
        $requiredExtensions = $this->config['server']['php']['extensions'] ?? [];
        if (!empty($requiredExtensions)) {
            $result = $this->appcontext->runPHP($options['php_version'], '-m', []);
            $extensions = array_map('strtolower', array_map('trim', explode("\n", $result->getOutput())));
            $missing = array_diff($requiredExtensions, $extensions);

            if (!empty($missing)) {
                throw new \RuntimeException(
                    "Unable to install GLPI, required PHP extensions are missing: " . implode(', ', $missing)
                );
            }
        }

        // 2. DB install via CLI. Pass 100M limits so GLPI's installer saves them to the DB.
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

        // 4. Set url_base dynamically based on SSL status
        $this->appcontext->runPHP($options['php_version'], $target->getDocRoot('bin/console'), [
            'config:set',
            'url_base',
            $target->getUrl(),
            '--no-interaction'
        ]);

        // 5. Setup cron job (runs every minute)
        $this->appcontext->runUser('v-add-cron-job', [
            '*', '*', '*', '*', '*',
            '/usr/bin/php' . $options['php_version'] . ' ' . $target->getDocRoot('front/cron.php') . ' > /dev/null 2>&1'
        ]);

        // 6. Cleanup
        $this->appcontext->deleteFile($target->getDocRoot('install/install.php'));
    }
}
