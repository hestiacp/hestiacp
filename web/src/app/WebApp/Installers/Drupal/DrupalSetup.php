<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\Drupal;

use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;

use function sprintf;

class DrupalSetup extends BaseSetup
{
    protected array $info = [
        'name' => 'Drupal',
        'group' => 'cms',
        'version' => 'latest',
        'thumbnail' => 'drupal-thumb.png',
    ];

    protected array $config = [
        'form' => [
            'username' => ['type' => 'text', 'value' => 'admin'],
            'password' => 'password',
            'email' => 'text',
        ],
        'database' => true,
        'resources' => [
            'composer' => ['src' => 'drupal/recommended-project', 'dst' => '/'],
        ],
        'server' => [
            'nginx' => [
                'template' => 'drupal-composer',
            ],
            'php' => [
                'supported' => ['8.1', '8.2', '8.3'],
            ],
        ],
    ];

    protected function setupApplication(InstallationTarget $target, array $options): void
    {
        $this->appcontext->createFile(
            $target->getDocRoot('.htaccess'),
            '<IfModule mod_rewrite.c>
                    RewriteEngine On
                    RewriteRule ^(.*)$ web/$1 [L]
            </IfModule>',
        );

        $this->appcontext->runComposer($options['php_version'], [
            'require',
            '-d',
            $target->getDocRoot(),
            'drush/drush',
        ]);

        $databaseUrl = sprintf(
            'mysql://%s:%s@%s:3306/%s',
            $target->database->user,
            $target->database->password,
            $target->database->host,
            $target->database->name,
        );

        $this->appcontext->runPHP(
            $options['php_version'],
            $target->getDocRoot('/vendor/drush/drush/drush.php'),
            [
                'site-install',
                'standard',
                '--db-url=' . $databaseUrl,
                '--account-name=' . $options['username'],
                '--account-pass=' . $options['password'],
                '--site-name=Drupal', // Sadly even when escaped spaces are splitted up
                '--site-mail=' . $options['email'],
            ],
        );
    }
}
