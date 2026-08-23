<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\Castopod;

use Hestia\System\Util;
use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;
use function file_get_contents;
use function str_replace;

class CastopodSetup extends BaseSetup {
    protected array $info = [
        "name" => "Castopod",
        "group" => "podcasting",
        "version" => "1.15.5",
        "thumbnail" => "castopod-logo.svg",
    ];

    protected array $config = [
        "form" => [
            "admin_gateway" => ["value" => "cp-admin"],
            "auth_gateway" => ["value" => "cp-auth"],
        ],
        "database" => true,
        "resources" => [
            "archive" => [
                "src" =>
                    "https://code.castopod.org/-/project/2/uploads/419c795a5ab306a27082b7d86aa43df6/castopod-1.15.5.zip",
            ],
        ],
        "server" => [
            "nginx" => [
                "template" => "castopod",
            ],
            "php" => [
                "supported" => ["8.1", "8.2", "8.3", "8.4", "8.5"],
            ],
        ],
    ];

    protected function setupApplication(InstallationTarget $target, array $options): void {
        $this->appcontext->copyDirectory($target->getDocRoot("/castopod/."), $target->getDocRoot());

        $this->appcontext->createFile(
            $target->getDocRoot('.htaccess'),
            file_get_contents(__DIR__ . '/.htaccess'),
        );

        $this->appcontext->moveFile(
            $target->getDocRoot(".env.example"),
            $target->getDocRoot(".env"),
        );

        // Update .env
        $envPath = $target->getDocRoot(".env");
        $env = $this->appcontext->readFile($envPath);

        $env = str_replace(
            'app.baseURL="https://YOUR_DOMAIN_NAME/"',
            "app.baseURL=" . $target->getUrl(),
            $env,
        );
        $env = str_replace(
            'media.baseURL="https://YOUR_MEDIA_DOMAIN_NAME/"',
            "media.baseURL=" . $target->getUrl(),
            $env,
        );
        $env = str_replace(
            'admin.gateway="cp-admin"',
            "admin.gateway=" . $options["admin_gateway"],
            $env,
        );
        $env = str_replace(
            'auth.gateway="cp-auth"',
            "auth.gateway=" . $options["auth_gateway"],
            $env,
        );
        $env = str_replace(
            'analytics.salt="RANDOM_STRING_OF_64_CHARACTERS"',
            "analytics.salt=" . Util::generateString(64, false),
            $env,
        );
        $env = str_replace(
            'database.default.hostname="localhost"',
            "database.default.hostname=" . $target->database->host,
            $env,
        );
        $env = str_replace(
            'database.default.database="castopod"',
            "database.default.database=" . $target->database->name,
            $env,
        );
        $env = str_replace(
            'database.default.username="root"',
            "database.default.username=" . $target->database->user,
            $env,
        );
        $env = str_replace(
            'database.default.password="****"',
            "database.default.password=" . $target->database->password,
            $env,
        );

        $this->appcontext->createFile($envPath, $env);

        // Initialize the database
        $this->appcontext->runPHP($options["php_version"], $target->getDocRoot("spark"), [
            "install:init-database",
        ]);

        $this->appcontext->deleteDirectory($target->getDocRoot("/castopod"));
    }
}
