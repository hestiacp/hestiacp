<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\Flarum;

use Hestia\System\Util;
use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;

class FlarumSetup extends BaseSetup
{
    protected array $appInfo = [
        'name' => 'Flarum',
        'group' => 'forum',
        'enabled' => true,
        'version' => 'latest',
        'thumbnail' => 'fl-thumb.png',
    ];

    protected array $config = [
        'form' => [
            'forum_title' => ['type' => 'text', 'value' => 'Flarum Forum'],
            'admin_username' => ['value' => 'fladmin'],
            'admin_email' => 'text',
            'admin_password' => 'password',
        ],
        'database' => true,
        'resources' => [
            'composer' => ['src' => 'flarum/flarum'],
        ],
        'server' => [
            'apache2' => [
                'document_root' => 'public',
            ],
            'nginx' => [
                'template' => 'flarum',
            ],
            'php' => [
                'supported' => ['8.2', '8.3'],
            ],
        ],
    ];

    protected function setupApplication(InstallationTarget $target, array $options): void
    {
        $this->appcontext->sendPostRequest($target->getUrl(), [
            'forumTitle' => $options['forum_title'],
            'mysqlHost' => $target->database->host,
            'mysqlDatabase' => $target->database->name,
            'mysqlUsername' => $target->database->user,
            'mysqlPassword' => $target->database->password,
            'tablePrefix' => 'fl' . Util::generateString(5, false),
            'adminUsername' => $options['admin_username'],
            'adminEmail' => $options['admin_email'],
            'adminPassword' => $options['admin_password'],
            'adminPasswordConfirmation' => $options['admin_password'],
        ]);
    }
}
