<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\Joomla;

use Hestia\System\Util;
use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;

class JoomlaSetup extends BaseSetup {
    protected array $info = [
        "name" => "Joomla",
        "group" => "cms",
        "version" => "6.1.1",
        "thumbnail" => "joomla-logo.svg",
    ];

    protected array $config = [
        "form" => [
            "site_name" => [
                "type" => "text",
                "value" => "Joomla",
            ],
            "admin_user" => [
                "type" => "text",
                "value" => "John",
            ],
            "admin_username" => [
                "type" => "text",
                "value" => "joomlaadmin",
            ],
            "password" => [
                "type" => "password",
                "value" => "",
            ],
            "email" => [
                "type" => "text",
                "value" => "",
            ],
        ],
        "database" => true,
        "resources" => [
            "archive" => [
                "src" =>
                    "https://downloads.joomla.org/cms/joomla6/6-1-1/Joomla_6-1-1-Stable-Full_Package.zip?format=zip",
            ],
        ],
        "server" => [
            "nginx" => [
                "template" => "joomla",
            ],
            "php" => [
                "supported" => ["8.3", "8.4"],
            ],
        ],
    ];

    protected function setupApplication(InstallationTarget $target, array $options): void {
        $this->appcontext->moveFile(
            $target->getDocRoot("htaccess.txt"),
            $target->getDocRoot(".htaccess"),
        );

        $this->appcontext->runPHP(
            $options["php_version"],
            $target->getDocRoot("installation/joomla.php"),
            [
                "install",
                "--site-name=" . $options["site_name"],
                "--admin-user=" . $options["admin_user"],
                "--admin-username=" . $options["admin_username"],
                "--admin-password=" . $options["password"],
                "--admin-email=" . $options["email"],
                "--db-user=" . $target->database->user,
                "--db-pass=" . $target->database->password,
                "--db-name=" . $target->database->name,
                "--db-host=" . $target->database->host,
                "--db-type=mysqli",
                "--db-encryption=0",
            ],
        );
    }
}
