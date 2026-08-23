<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\NamelessMC;

use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;

class NamelessMCSetup extends BaseSetup {
    protected array $info = [
        "name" => "NamelessMC",
        "group" => "cms",
        "version" => "2.2.5",
        "thumbnail" => "namelessmc.png",
    ];

    protected array $config = [
        "form" => [
            "protocol" => [
                "type" => "select",
                "options" => ["http", "https"],
                "value" => "https",
            ],
        ],
        "database" => false,
        "resources" => [
            "archive" => [
                "src" =>
                    "https://github.com/NamelessMC/Nameless/releases/download/v2.2.5/nameless-deps-dist.zip",
            ],
        ],
        "server" => [
            "nginx" => [
                "template" => "default",
            ],
            "php" => [
                "supported" => ["7.4", "8.0", "8.1", "8.2"],
            ],
        ],
    ];

    protected function setupApplication(InstallationTarget $target, array $options): void {
        // Nothing to do after installation of resources
    }
}
