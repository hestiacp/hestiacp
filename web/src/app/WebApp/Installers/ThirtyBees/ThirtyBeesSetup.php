<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\ThirtyBees;

use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;

class ThirtyBeesSetup extends BaseSetup {
    protected array $info = [
        "name" => "ThirtyBees",
        "group" => "ecommerce",
        "version" => "1.7.0",
        "thumbnail" => "thirtybees-thumb.png",
    ];

    protected array $config = [
        "form" => [
            "store_name" => ["value" => "thirty bees"],
            "first_name" => ["value" => ""],
            "last_name" => ["value" => ""],
            "email" => "text",
            "password" => "password",
        ],
        "database" => true,
        "resources" => [
            "archive" => [
                "src" =>
                    "https://github.com/thirtybees/thirtybees/releases/download/1.7.0/thirtybees-v1.7.0-php7.4.zip",
            ],
        ],
        "server" => [
            "nginx" => [
                "template" => "prestashop",
            ],
            "php" => [
                "supported" => ["7.4", "8.0", "8.1", "8.2", "8.3"],
            ],
        ],
    ];

    protected function setupApplication(InstallationTarget $target, array $options): void {
        $this->appcontext->runPHP(
            $options["php_version"],
            $target->getDocRoot("/install/index_cli.php"),
            [
                "--db_server=" . $target->database->host,
                "--db_user=" . $target->database->user,
                "--db_password=" . $target->database->password,
                "--db_name=" . $target->database->name,
                "--name=" . $options["store_name"],
                "--firstname=" . $options["first_name"],
                "--lastname=" . $options["last_name"],
                "--password=" . $options["password"],
                "--email=" . $options["email"],
                "--domain=" . $target->domain->domainName,
            ],
        );

        $this->appcontext->deleteDirectory($target->getDocRoot("/install"));
    }
}
