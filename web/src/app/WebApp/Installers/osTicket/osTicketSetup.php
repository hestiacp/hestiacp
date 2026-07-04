<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\osTicket;

use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;

class osTicketSetup extends BaseSetup {
    protected array $info = [
        "name" => "osTicket",
        "group" => "helpdesk",
        "version" => "1.18.4",
        "thumbnail" => "ost-logo.png",
    ];

    protected array $config = [
        "form" => [
            "helpdesk_name" => ["value" => "osTicket"],
            "email" => "text",
            "first_name" => "text",
            "last_name" => "text",
            "username" => ["value" => "osAdmin"],
            "admin_email" => "text",
            "password" => "password",
        ],
        "database" => true,
        "resources" => [
            "archive" => [
                "src" =>
                    "https://github.com/osTicket/osTicket/releases/download/v1.18.4/osTicket-v1.18.4.zip",
            ],
        ],
        "server" => [
            "nginx" => [
                "template" => "osticket",
            ],
            "php" => [
                "supported" => ["8.2", "8.3", "8.4"],
            ],
        ],
    ];

    protected function setupApplication(InstallationTarget $target, array $options): void {
        $this->appcontext->copyDirectory($target->getDocRoot("upload/."), $target->getDocRoot());

        // Rename config file
        $this->appcontext->moveFile(
            $target->getDocRoot("include/ost-sampleconfig.php"),
            $target->getDocRoot("include/ost-config.php"),
        );

        $this->appcontext->sendPostRequest($target->getUrl() . "/setup/install.php", [
            "s" => "install",
            "name" => $options["helpdesk_name"],
            "email" => $options["email"],
            "lang_id" => "en_US",
            "fname" => $options["first_name"],
            "lname" => $options["last_name"],
            "admin_email" => $options["admin_email"],
            "username" => $options["username"],
            "passwd" => $options["password"],
            "passwd2" => $options["password"],
            "prefix" => "ost_",
            "dbhost" => $target->database->host,
            "dbname" => $target->database->name,
            "dbuser" => $target->database->user,
            "dbpass" => $target->database->password,
        ]);

        // Cleanup
        $this->appcontext->deleteDirectory($target->getDocRoot("upload"));
        $this->appcontext->deleteDirectory($target->getDocRoot("setup"));
    }
}
