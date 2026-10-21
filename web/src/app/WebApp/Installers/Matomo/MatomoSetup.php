<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\Matomo;

use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;
use RuntimeException;

use function parse_str;
use function parse_url;
use function sprintf;

class MatomoSetup extends BaseSetup
{
    protected array $info = [
        'name' => 'Matomo',
        'group' => 'analytics',
        'version' => 'latest',
        'thumbnail' => 'matomo-logo.svg',
    ];

    protected array $config = [
        'form' => [
            'username' => ['value' => 'admin'],
            'password' => 'password',
            'email' => 'text',
            'website_name' => ['type' => 'text', 'value' => 'My Website'],
            'website_url' => ['type' => 'text', 'placeholder' => 'https://example.com'],
            'timezone' => ['type' => 'text', 'value' => 'UTC'],
        ],
        'database' => true,
        'resources' => [
            'archive' => [
                'src' => 'https://builds.matomo.org/matomo-latest.zip',
                'dst' => '/tmp-matomo',
            ],
        ],
        'server' => [
            'nginx' => [
                'template' => 'matomo',
            ],
            'php' => [
                'supported' => ['8.1', '8.2', '8.3', '8.4', '8.5'],
            ],
        ],
    ];

    protected function setupApplication(InstallationTarget $target, array $options): void
    {
        // The archive also contains "How to install Matomo.html" next to the matomo/ folder,
        // so it is extracted into its own directory which is removed afterwards
        $extractDirectory = $this->config['resources']['archive']['dst'];

        $this->appcontext->copyDirectory(
            $target->getDocRoot($extractDirectory . '/matomo/.'),
            $target->getDocRoot(),
        );
        $this->appcontext->deleteDirectory($target->getDocRoot($extractDirectory));

        // Matomo has no CLI installer, so submit the steps of its web installer. Matomo then
        // writes config.ini.php itself (incl. salt, trusted host and the server's collation).
        $this->runInstallerStep($target, 'databaseSetup', [
            'host' => $target->database->host,
            'username' => $target->database->user,
            'password' => $target->database->password,
            'dbname' => $target->database->name,
            'tables_prefix' => 'matomo_',
            'adapter' => 'PDO\\MYSQL',
            'schema' => 'Mysql',
        ]);
        $this->runInstallerStep($target, 'tablesCreation');
        $this->runInstallerStep($target, 'setupSuperUser', [
            'login' => $options['username'],
            'password' => $options['password'],
            'password_bis' => $options['password'],
            'email' => $options['email'],
        ]);
        $this->runInstallerStep($target, 'firstWebsiteSetup', [
            'siteName' => $options['website_name'],
            'url' => $options['website_url'],
            'timezone' => $options['timezone'],
            'ecommerce' => '0',
        ]);
        $this->runInstallerStep($target, 'finished', ['submit' => '1']);
    }

    private function runInstallerStep(
        InstallationTarget $target,
        string $action,
        array $formData = [],
    ): void {
        $effectiveUrl = $this->appcontext->sendPostRequest(
            $target->getUrl() . '/index.php?module=Installation&action=' . $action,
            $formData,
            ['Content-Type: application/x-www-form-urlencoded'],
            $target->getResolveUrl(),
        );

        // Matomo does not enforce the order of its steps. An accepted form redirects to the
        // next step, a rejected one (e.g. an invalid email address) is shown again.
        parse_str((string) parse_url($effectiveUrl, PHP_URL_QUERY), $query);
        if ($formData !== [] && ($query['action'] ?? '') === $action) {
            throw new RuntimeException(
                sprintf(
                    'Matomo installation step "%s" failed, finish it at %s',
                    $action,
                    $target->getUrl(),
                ),
            );
        }
    }
}
