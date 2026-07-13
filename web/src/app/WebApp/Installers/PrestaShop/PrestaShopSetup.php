<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\PrestaShop;

use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;

class PrestaShopSetup extends BaseSetup {
    protected array $info = [
        "name" => "PrestaShop",
        "group" => "ecommerce",
        "version" => "9.1.4",
        "thumbnail" => "prestashop-logo.svg",
    ];

    protected array $config = [
        "form" => [
            "shop_name" => ["value" => "PrestaShop"],
            "first_name" => ["value" => ""],
            "last_name" => ["value" => ""],
            "language" => [
                "type" => "select",
                "value" => "en",
                "options" => [
                    "en" => "English",
                    "ar" => "العربية (Arabic)",
                    "bs" => "Bosanski (Bosnian)",
                    "bg" => "български език (Bulgarian)",
                    "ca" => "Català (Catalan)",
                    "cs" => "Čeština (Czech)",
                    "da" => "Dansk (Danish)",
                    "de" => "Deutsch (German)",
                    "et" => "Eesti keel (Estonian)",
                    "es" => "Español (Spanish)",
                    "mx" => "Español MX (Spanish)",
                    "fr" => "Français (French)",
                    "qc" => "Français CA (French)",
                    "gl" => "Galego (Galician)",
                    "el" => "ελληνικά (Greek)",
                    "ko" => "한국어 (Korean)",
                    "hr" => "Hrvatski (Croatian)",
                    "id" => "Indonesia (Indonesian)",
                    "it" => "Italiano (Italian)",
                    "ja" => "日本語 (Japanese)",
                    "lv" => "Latvija (Latvian)",
                    "lt" => "lietuvių kalba (Lithuanian)",
                    "mk" => "македонски јазик (Macedonian)",
                    "hu" => "Magyar (Hungarian)",
                    "nl" => "Nederlands (Dutch)",
                    "no" => "Norsk (Norwegian)",
                    "fa" => "پارسی (Persian)",
                    "pl" => "Polski (Polish)",
                    "br" => "Português (Brasil)",
                    "pt" => "Português (Portuguese)",
                    "ro" => "Română (Romanian)",
                    "ru" => "Русский (Russian)",
                    "sr" => "Srpski (Serbian)",
                    "sq" => "Shqip (Albanian)",
                    "sk" => "Slovenčina (Slovak)",
                    "si" => "slovenski jezik (Slovene)",
                    "fi" => "Suomi (Finnish)",
                    "sv" => "svenska (Swedish)",
                    "tr" => "Türkçe (Turkish)",
                    "uk" => "Українська (Ukrainian)",
                    "vn" => "tiếng Việt (Vietnamese)",
                    "he" => "עברית (Hebrew)",
                    "hi" => "हिन्दी (Hindi)",
                    "bn" => "বাংলা (Bengali)",
                    "tw" => "繁體中文 (Traditional Chinese)",
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
                    "https://assets.prestashop3.com/dst/edition/corporate/9.1.4-5.0/prestashop_edition_classic_version_9.1.4-5.0.zip?source=hestiacp",
            ],
        ],
        "server" => [
            "nginx" => [
                "template" => "prestashop",
            ],
            "php" => [
                "supported" => ["8.1", "8.2", "8.3", "8.4", "8.5"],
            ],
        ],
    ];

    protected function setupApplication(InstallationTarget $target, array $options): void {
        // Remove the browser-based installation files
        $this->appcontext->deleteFile($target->getDocRoot("/index.php"));
        $this->appcontext->deleteFile($target->getDocRoot("/Install_PrestaShop.html"));

        $this->appcontext->archiveExtract(
            $target->getDocRoot("/prestashop.zip"),
            $target->getDocRoot("/"),
        );

        $this->appcontext->runPHP(
            $options["php_version"],
            $target->getDocRoot("/install/index_cli.php"),
            [
                "--db_server=" . $target->database->host,
                "--db_user=" . $target->database->user,
                "--db_password=" . $target->database->password,
                "--db_name=" . $target->database->name,
                "--name=" . $options["shop_name"],
                "--firstname=" . $options["first_name"],
                "--lastname=" . $options["last_name"],
                "--language=" . $options["language"],
                "--password=" . $options["password"],
                "--email=" . $options["email"],
                "--domain=" . $target->domain->domainName,
                "--ssl=" . $target->domain->isSslEnabled,
            ],
        );

        // Cleanup
        $this->appcontext->deleteDirectory($target->getDocRoot("/install"));
        $this->appcontext->deleteFile($target->getDocRoot("/prestashop.zip"));
    }
}
