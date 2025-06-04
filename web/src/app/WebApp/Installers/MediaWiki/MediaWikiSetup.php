<?php

declare(strict_types=1);

namespace DevIT\WebApp\Installers\MediaWiki;

use DevIT\WebApp\BaseSetup;
use DevIT\WebApp\InstallationTarget\InstallationTarget;

class MediaWikiSetup extends BaseSetup
{
    protected array $info = [
        'name' => 'MediaWiki',
        'group' => 'cms',
        'version' => '1.43.0',
        'thumbnail' => 'MediaWiki-2020-logo.svg', //Max size is 300px by 300px
    ];

    protected array $config = [
        'form' => [
            'admin_username' => ['type' => 'text', 'value' => 'admin'],
            'admin_password' => 'password',
            'language' => ['type' => 'text', 'value' => 'en'],
        ],
        'database' => true,
        'resources' => [
            'archive' => [
                'src' => 'https://releases.wikimedia.org/mediawiki/1.43/mediawiki-1.43.0.zip',
            ],
        ],
        'server' => [
            'nginx' => [
                'template' => 'default',
            ],
            'php' => [
                'supported' => ['8.0', '8.1', '8.2', '8.3'],
            ],
        ],
    ];

    protected function setupApplication(InstallationTarget $target, array $options): void
    {
        $this->appcontext->copyDirectory(
            $target->getDocRoot('/mediawiki-1.43.0/.'),
            $target->getDocRoot(),
        );

        $this->appcontext->runPHP(
            $options['php_version'],
            $target->getDocRoot('maintenance/install.php'),
            [
                '--dbserver=' . $target->database->host,
                '--dbname=' . $target->database->name,
                '--installdbuser=' . $target->database->user,
                '--installdbpass=' . $target->database->password,
                '--dbuser=' . $target->database->name,
                '--dbpass=' . $target->database->password,
                '--server=' . $target->getUrl(),
                '--scriptpath=', // must NOT be /
                '--lang=' . $options['language'],
                '--pass=' . $options['admin_password'],
                'Media Wiki',
                $options['admin_username'],
            ],
        );

        $this->appcontext->deleteDirectory($target->getDocRoot('/mediawiki-1.43.0/'));
    }
}
