<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\QloApps;

use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;

class QloAppsSetup extends BaseSetup {
    protected array $info = [
        "name" => "QloApps",
        "group" => "ecommerce",
        "version" => "1.7.0",
        "thumbnail" => "qloapps-logo.svg",
    ];

    protected array $config = [
        "form" => [
            "first_name" => ["value" => ""],
            "last_name" => ["value" => ""],
            "language" => [
                "type" => "select",
                "value" => "en",
                "options" => [
                    "en" => "English",
                    "ca" => "Català (Catalan)",
                    "de" => "Deutsch (German)",
                    "es" => "Español (Spanish)",
                    "fr" => "Français (French)",
                    "qc" => "Français CA (French)",
                    "hr" => "Hrvatski (Croatian)",
                    "id" => "Indonesia (Indonesian)",
                    "it" => "Italiano (Italian)",
                    "hu" => "Magyar (Hungarian)",
                    "nl" => "Nederlands (Dutch)",
                    "no" => "Norsk (Norwegian)",
                    "pl" => "Polski (Polish)",
                    "br" => "Português (Brasil)",
                    "pt" => "Português (Portuguese)",
                    "ro" => "Română (Romanian)",
                    "sr" => "Srpski (Serbian)",
                    "tr" => "Türkçe (Turkish)",
                    "lt" => "lietuvių kalba (Lithuanian)",
                    "si" => "slovenski jezik (Slovene)",
                    "sv" => "svenska (Swedish)",
                    "vn" => "tiếng Việt (Vietnamese)",
                    "cs" => "Čeština (Czech)",
                    "ru" => "Русский (Russian)",
                    "bg" => "български език (Bulgarian)",
                    "mk" => "македонски јазик (Macedonian)",
                    "he" => "עברית (Hebrew)",
                    "fa" => "پارسی (Persian)",
                    "bn" => "বাংলা (Bengali)",
                    "tw" => "正體中文 (Traditional Chinese)",
                    "zh" => "简体字 (Simplified Chinese)",
                ],
            ],
            "email" => "text",
            "password" => "password",
        ],
        "database" => true,
        "resources" => [
            "archive" => [
                "src" =>
                    "https://github.com/Qloapps/QloApps/releases/download/v1.7.0/qloapps-1.7.0.zip",
            ],
        ],
        "server" => [
            "nginx" => [
                "template" => "default",
            ],
            "php" => [
                "supported" => ["8.1", "8.2", "8.3", "8.4"],
            ],
        ],
    ];

    protected function setupApplication(InstallationTarget $target, array $options): void {
        $this->appcontext->copyDirectory(
            $target->getDocRoot("/qloapps-1.7.0/."),
            $target->getDocRoot(),
        );

        $this->appcontext->runPHP(
            $options["php_version"],
            $target->getDocRoot("/install/index_cli.php"),
            [
                "--domain=" . $target->domain->domainName,
                "--db_server=" . $target->database->host,
                "--db_name=" . $target->database->name,
                "--db_user=" . $target->database->user,
                "--db_password=" . $target->database->password,
                "--firstname=" . $options["first_name"],
                "--lastname=" . $options["last_name"],
                "--language=" . $options["language"],
                "--password=" . $options["password"],
                "--email=" . $options["email"],
            ],
        );

        $this->appcontext->deleteDirectory($target->getDocRoot("/install"));
        $this->appcontext->deleteDirectory($target->getDocRoot("/qloapps-1.7.0"));
    }
}
