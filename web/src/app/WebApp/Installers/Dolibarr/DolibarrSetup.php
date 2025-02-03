<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\Dolibarr;

use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;
use function file_get_contents;

class DolibarrSetup extends BaseSetup
{
    protected array $info = [
        'name' => 'Dolibarr',
        'group' => 'CRM',
        'version' => '20.0.2',
        'thumbnail' => 'dolibarr-thumb.png',
    ];

    protected array $config = [
        'form' => [
            'dolibarr_account_username' => ['value' => 'admin'],
            'dolibarr_account_password' => 'password',
            'language' => [
                'type' => 'select',
                'options' => [
                    'en_EN' => 'English',
                    'es_ES' => 'Spanish',
                    'fr_FR' => 'French',
                    'de_DE' => 'German',
                    'pt_PT' => 'Portuguese',
                    'it_IT' => 'Italian',
                ],
                'default' => 'en_EN',
            ],
        ],
        'database' => true,
        'resources' => [
            'archive' => [
                'src' => 'https://github.com/Dolibarr/dolibarr/archive/refs/tags/20.0.2.zip',
            ],
        ],
        'server' => [
            'nginx' => [
                'template' => 'dolibarr',
            ],
            'php' => [
                'supported' => ['7.4', '8.0', '8.1', '8.2', '8.3'],
            ],
        ],
    ];

    protected function setupApplication(InstallationTarget $target, array $options): void
    {
        $this->appcontext->copyDirectory(
            $target->getDocRoot('/dolibarr-' . $this->info['version'] . '/.'),
            $target->getDocRoot(),
        );

        $language = $options['language'] ?? 'en_EN';

        $this->appcontext->moveFile(
            $target->getDocRoot('htdocs/conf/conf.php.example'),
            $target->getDocRoot('htdocs/conf/conf.php'),
        );

        $this->appcontext->changeFilePermissions(
            $target->getDocRoot('htdocs/conf/conf.php'),
            '666',
        );

        $this->appcontext->addDirectory($target->getDocRoot('documents'));

        $this->appcontext->createFile(
            $target->getDocRoot('.htaccess'),
            file_get_contents(__DIR__ . '/.htaccess'),
        );

        $this->appcontext->sendPostRequest($target->getUrl() . '/install/step1.php', [
            'testpost' => 'ok',
            'action' => 'set',
            'main_dir' => $target->getDocRoot('htdocs'),
            'main_data_dir' => $target->getDocRoot('documents'),
            'main_url' => $target->getUrl(),
            'db_type' => 'mysqli',
            'db_host' => $target->database->host,
            'db_port' => '3306',
            'db_prefix' => 'llx_',
            'db_name' => $target->database->name,
            'db_user' => $target->database->user,
            'db_pass' => $target->database->password,
            'selectlang' => $language,
        ]);

        $this->appcontext->sendPostRequest($target->getUrl() . '/install/step2.php', [
            'testpost' => 'ok',
            'action' => 'set',
            'dolibarr_main_db_character_set' => 'utf8',
            'dolibarr_main_db_collation' => 'utf8_unicode_ci',
            'selectlang' => $language,
        ]);

        $this->appcontext->sendPostRequest($target->getUrl() . '/install/step4.php', [
            'testpost' => 'ok',
            'action' => 'set',
            'dolibarrpingno' => 'checked',
            'selectlang' => $language,
        ]);

        $this->appcontext->sendPostRequest($target->getUrl() . '/install/step5.php', [
            'testpost' => 'ok',
            'login' => $options['dolibarr_account_username'],
            'pass' => $options['dolibarr_account_password'],
            'selectlang' => $language,
        ]);
    }
}
