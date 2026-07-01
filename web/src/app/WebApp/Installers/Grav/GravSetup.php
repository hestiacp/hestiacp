<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\Grav;

use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;

class GravSetup extends BaseSetup {
    protected array $info = [
        "name" => "Grav",
        "group" => "cms",
        "version" => "2.0.3",
        "thumbnail" => "grav-symbol.svg",
    ];

    protected array $config = [
        "form" => [
            "full_name" => "text",
            "username" => ["text" => "gravadmin"],
            "password" => "password",
            "email" => "text",
        ],
        "database" => false,
        "resources" => [
            "archive" => [
                "src" =>
                    "https://github.com/getgrav/grav/releases/download/2.0.3/grav-admin-v2.0.3.zip",
            ],
        ],
        "server" => [
            "nginx" => [
                "template" => "grav",
            ],
            "php" => [
                "supported" => ["8.3", "8.4", "8.5"],
            ],
        ],
    ];

    protected function setupApplication(InstallationTarget $target, array $options): void {
        $this->appcontext->copyDirectory(
            $target->getDocRoot("grav-admin/."),
            $target->getDocRoot(),
        );

        // Create admin user configuration
        $yaml =
            "state: enabled\n" .
            "email: " .
            $options["email"] .
            "\n" .
            "fullname: \"" .
            $options["full_name"] .
            "\"\n" .
            "title: Administrator\n" .
            "access:\n" .
            "  site:\n" .
            "    login: true\n" .
            "  api:\n" .
            "    super: true\n" .
            "hashed_password: '" .
            password_hash($options["password"], PASSWORD_DEFAULT) .
            "'\n";

        $this->appcontext->createFile(
            $target->getDocRoot("user/accounts/" . $options["username"] . ".yaml"),
            $yaml,
        );

        // Cleanup
        $this->appcontext->deleteDirectory($target->getDocRoot("grav-admin"));
    }
}
