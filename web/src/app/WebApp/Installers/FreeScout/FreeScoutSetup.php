<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\FreeScout;

use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;

class FreeScoutSetup extends BaseSetup {
    protected array $info = [
        "name" => "FreeScout",
        "group" => "helpdesk",
        "version" => "latest",
        "thumbnail" => "freescout-logo.svg",
    ];

    protected array $config = [
        "form" => [
            "first_name" => ["value" => ""],
            "last_name" => ["value" => ""],
            "email" => "text",
            "password" => "password",
        ],
        "database" => true,
        "resources" => [
            "archive" => [
                "src" =>
                    "https://github.com/freescout-help-desk/freescout/archive/refs/heads/dist.zip",
            ],
        ],
        "server" => [
            "nginx" => [
                "template" => "freescout",
            ],
            "php" => [
                // PHP 8.1 is omitted due to known critical issues with XML/HTML parsing: https://github.com/freescout-help-desk/freescout/wiki/Installation-Guide#2-installing-package-dependencies
                "supported" => ["7.4", "8.0", "8.2", "8.3", "8.4", "8.5"],
            ],
        ],
    ];

    protected function setupApplication(InstallationTarget $target, array $options): void {
        $this->appcontext->copyDirectory(
            $target->getDocRoot("/freescout-dist/."),
            $target->getDocRoot(),
        );

        $this->appcontext->moveFile(
            $target->getDocRoot(".env.example"),
            $target->getDocRoot(".env"),
        );

        // Update .env
        $envPath = $target->getDocRoot(".env");
        $env = $this->appcontext->readFile($envPath);

        $env = str_replace("APP_URL=https://example.com", "APP_URL=" . $target->getUrl(), $env);
        $env = str_replace("DB_HOST=localhost", "DB_HOST=" . $target->database->host, $env);
        $env = str_replace("DB_PORT=3306", "DB_PORT=3306", $env);
        $env = str_replace("DB_DATABASE=", "DB_DATABASE=" . $target->database->name, $env);
        $env = str_replace("DB_USERNAME=", "DB_USERNAME=" . $target->database->user, $env);
        $env = str_replace("DB_PASSWORD=", "DB_PASSWORD=" . $target->database->password, $env);

        $this->appcontext->createFile($envPath, $env);

        // Generate application key
        $this->appcontext->runPHP($options["php_version"], $target->getDocRoot("artisan"), [
            "key:generate",
            "--force",
        ]);

        // Clear application cache
        $this->appcontext->runPHP($options["php_version"], $target->getDocRoot("artisan"), [
            "freescout:clear-cache",
        ]);

        // Create a symbolic link
        $this->appcontext->runPHP($options["php_version"], $target->getDocRoot("artisan"), [
            "storage:link",
        ]);

        // Migrate database
        $this->appcontext->runPHP($options["php_version"], $target->getDocRoot("artisan"), [
            "migrate",
            "--force",
        ]);

        // Create admin user
        $this->appcontext->runPHP($options["php_version"], $target->getDocRoot("artisan"), [
            "freescout:create-user",
            "--role=admin",
            "--firstName=" . $options["first_name"],
            "--lastName=" . $options["last_name"],
            "--email=" . $options["email"],
            "--password=" . $options["password"],
            "--no-interaction",
        ]);

        $this->appcontext->deleteDirectory($target->getDocRoot("/freescout-dist"));
    }
}
