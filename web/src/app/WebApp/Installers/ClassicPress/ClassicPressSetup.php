<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\ClassicPress;

use Hestia\System\Util;
use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;

class ClassicPressSetup extends BaseSetup
{
    protected array $info = [
        'name' => 'ClassicPress',
        'group' => 'cms',
        'version' => 'latest',
        'thumbnail' => 'classicpress-logo.svg',
    ];

    protected array $config = [
        'form' => [
            'site_name' => ['type' => 'text', 'value' => 'ClassicPress Blog'],
            'username' => ['value' => 'wpadmin'],
            'email' => 'text',
            'password' => 'password',
            'language' => [
                'type' => 'select',
                'value' => 'en_US',
                'options' => [
                    'ar_AR' => 'Arabic',
                    'zh-CN' => 'Chinese Simplified',
                    'cs_CZ' => 'Czech',
                    'nl_NL' => 'Dutch',
                    'en_GB' => 'English (British)',
                    'en_US' => 'English (United States)',
                    'fr_FR' => 'French',
                    'de_DE' => 'German',
                    'it_IT' => 'Italian',
                    'sv_SE' => 'Swedish',
                    'pt_BR' => 'Portuguese (Brazil)',
                ],
            ],
            'indexing' => [
                'type' => 'select',
                'value' => '1',
                'options' => [
                    '0' => 'Discourage search engine indexing',
                    '1' => 'Allow indexing'
                ]
            ],
        ],
        'database' => true,
        'resources' => [
            'wp' => ['src' => 'https://www.classicpress.net/latest.zip'],
        ],
        'server' => [
            'nginx' => [
                'template' => 'classicpress',
            ],
            'php' => [
                'supported' => ['7.4', '8.0', '8.1', '8.2', '8.3', '8.4', '8.5'],
            ],
        ],
    ];

    protected function setupApplication(InstallationTarget $target, array $options = null): void
    {
        $this->appcontext->runWp($options['php_version'], [
            'config',
            'create',
            '--dbname=' . $target->database->name,
            '--dbuser=' . $target->database->user,
            '--dbpass=' . $target->database->password,
            '--dbhost=' . $target->database->host,
            '--dbprefix=' . 'cp_' . Util::generateString(5, false) . '_',
            '--dbcharset=utf8mb4',
            '--locale=' . $options['language'],
            '--path=' . $target->getDocRoot(),
        ]);

        $this->appcontext->sendPostRequest(
            $target->getUrl() .
                '/' .
                $options['install_directory'] .
                '/wp-admin/install.php?step=2',
            [
                'weblog_title' => $options['site_name'],
                'user_name' => $options['username'],
                'admin_password' => $options['password'],
                'admin_password2' => $options['password'],
                'admin_email' => $options['email'],
                'blog_public' => $options['indexing'],
            ],
        );
    }
}
