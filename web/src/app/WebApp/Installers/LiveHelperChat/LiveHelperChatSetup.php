<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\LiveHelperChat;

use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;

class LiveHelperChatSetup extends BaseSetup {
    protected array $info = [
        "name" => "LiveHelperChat",
        "group" => "helpdesk",
        "version" => "latest",
        "thumbnail" => "livehelperchat-logo.svg",
    ];

    protected array $config = [
        "form" => [
            "admin_name" => "text",
            "surname" => "text",
            "username" => ["value" => "chatadmin"],
            "email" => "text",
            "default_department" => ["value" => "Helpdesk"],
            "password" => "password",
        ],
        "database" => true,
        "resources" => [
            "archive" => [
                "src" =>
                    "https://github.com/LiveHelperChat/livehelperchat/archive/refs/heads/master.zip",
            ],
        ],
        "server" => [
            "nginx" => [
                "template" => "default",
            ],
            "php" => [
                "supported" => ["8.2", "8.3", "8.4", "8.5"],
            ],
        ],
    ];

    protected function setupApplication(InstallationTarget $target, array $options): void {
        $this->appcontext->copyDirectory(
            $target->getDocRoot("livehelperchat-master/lhc_web/."),
            $target->getDocRoot(),
        );

        $this->appcontext->runComposer($options["php_version"], [
            "require",
            "-d",
            $target->getDocRoot(),
        ]);

        // Step 1 is not required, as it is for checking folder permissions and installed PHP extensions

        $this->appcontext->sendPostRequest($target->getUrl() . "/index.php/site_admin/install/install/2", [
            "DatabaseUsername" => $target->database->user,
            "DatabasePassword" => $target->database->password,
            "DatabaseHost" => $target->database->host,
            "DatabasePort" => "3306",
            "DatabaseDatabaseName" => $target->database->name,
        ]);

        $this->appcontext->sendPostRequest($target->getUrl() . "/index.php/site_admin/install/install/3", [
            "AdminUsername" => $options["username"],
            "AdminPassword" => $options["password"],
            "AdminPassword1" => $options["password"],
            "AdminEmail" => $options["email"],
            "AdminName" => $options["admin_name"],
            "AdminSurname" => $options["surname"] ?? "",
            "DefaultDepartament" => $options["default_department"],
        ]);

        $this->appcontext->deleteDirectory($target->getDocRoot("livehelperchat-master"));
    }
}
