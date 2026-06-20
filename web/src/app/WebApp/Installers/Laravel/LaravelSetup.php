<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\Laravel;

use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;
use function file_get_contents;

class LaravelSetup extends BaseSetup {
    protected array $info = [
        "name" => "Laravel",
        "group" => "framework",
        "version" => "latest",
        "thumbnail" => "laravel-logo.svg",
    ];

    protected array $config = [
        "form" => [],
        "database" => true,
        "resources" => [
            "composer" => ["src" => "laravel/laravel", "dst" => "/"],
        ],
        "server" => [
            "nginx" => [
                "template" => "laravel",
            ],
            "php" => [
                "supported" => ["8.3", "8.4", "8.5"],
            ],
        ],
    ];

    protected function setupApplication(InstallationTarget $target, array $options): void {
        $this->appcontext->createFile(
            $target->getDocRoot(".htaccess"),
            file_get_contents(__DIR__ . "/.htaccess"),
        );

        // Update .env to use MySQL
        $envPath = $target->getDocRoot(".env");
        $env = $this->appcontext->readFile($envPath);

        $env = str_replace("DB_CONNECTION=sqlite", "DB_CONNECTION=mysql", $env);
        $env = str_replace("# DB_HOST=127.0.0.1", "DB_HOST=" . $target->database->host, $env);
        $env = str_replace("# DB_PORT=3306", "DB_PORT=3306", $env);
        $env = str_replace("# DB_DATABASE=laravel", "DB_DATABASE=" . $target->database->name, $env);
        $env = str_replace("# DB_USERNAME=root", "DB_USERNAME=" . $target->database->user, $env);
        $env = str_replace("# DB_PASSWORD=", "DB_PASSWORD=" . $target->database->password, $env);

        $this->appcontext->createFile($envPath, $env);

        // Generate application key
        $this->appcontext->runPHP($options["php_version"], $target->getDocRoot("artisan"), [
            "key:generate",
            "--force",
        ]);

        // Migrate database
        $this->appcontext->runPHP($options["php_version"], $target->getDocRoot("artisan"), [
            "migrate",
            "--force",
        ]);

        // Delete the default SQLite database
        $this->appcontext->deleteFile($target->getDocRoot("database/database.sqlite"));
    }
}
