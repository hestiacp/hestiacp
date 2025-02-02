<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\DokuWiki;

use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;
use function var_dump;

class DokuWikiSetup extends BaseSetup
{
    protected array $info = [
        'name' => 'DokuWiki',
        'group' => 'wiki',
        'version' => 'latest',
        'thumbnail' => 'dokuwiki-logo.svg',
    ];

    protected array $config = [
        'form' => [
            'wiki_name' => 'text',
            'superuser' => 'text',
            'real_name' => 'text',
            'email' => 'text',
            'password' => 'password',
            'initial_ACL_policy' => [
                'type' => 'select',
                'options' => [
                    '0: Open Wiki (read, write, upload for everyone)',
                    '1: Public Wiki (read for everyone, write and upload for registered users)',
                    '2: Closed Wiki (read, write, upload for registered users only)',
                ],
            ],
            'content_license' => [
                'type' => 'select',
                'options' => [
                    'cc-zero: CC0 1.0 Universal',
                    'publicdomain: Public Domain',
                    'cc-by: CC Attribution 4.0 International',
                    'cc-by-sa: CC Attribution-Share Alike 4.0 International',
                    'gnufdl: GNU Free Documentation License 1.3',
                    'cc-by-nc: CC Attribution-Noncommercial 4.0 International',
                    'cc-by-nc-sa: CC Attribution-Noncommercial-Share Alike 4.0 International',
                    '0: Do not show any license information',
                ],
            ],
        ],
        'resources' => [
            'archive' => [
                'src' => 'https://download.dokuwiki.org/src/dokuwiki/dokuwiki-stable.tgz',
            ],
        ],
        'server' => [
            'nginx' => [
                'template' => 'default',
            ],
            'php' => [
                'supported' => ['8.0', '8.1', '8.2', '8.3', '8.4'],
            ],
        ],
    ];

    protected function setupApplication(InstallationTarget $target, array $options): void
    {
        // Enable htaccess
        $this->appcontext->moveFile(
            $target->getDocRoot('.htaccess.dist'),
            $target->getDocRoot('.htaccess'),
        );

        $this->appcontext->sendPostRequest(
            $target->getUrl() . '/install.php',
            [
                'l' => 'en',
                'd[title]' => $options['wiki_name'],
                'd[acl]' => 'on',
                'd[superuser]' => $options['superuser'],
                'd[fullname]' => $options['real_name'],
                'd[email]' => $options['email'],
                'd[password]' => $options['password'],
                'd[confirm]' => $options['password'],
                'd[policy]' => substr($options['initial_ACL_policy'], 0, 1),
                'd[license]' => explode(':', $options['content_license'])[0],
                'submit' => '',
            ],
            ['Content-Type: application/x-www-form-urlencoded'],
        );

        $this->appcontext->deleteFile($target->getDocRoot('install.php'));
    }
}
