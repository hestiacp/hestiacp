<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\Shopware;

use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;
use function file_get_contents;
use function str_replace;

class ShopwareSetup extends BaseSetup {
    protected array $info = [
        "name" => "Shopware",
        "group" => "ecommerce",
        "version" => "latest",
        "thumbnail" => "shopware-logo.svg",
    ];

    protected array $config = [
        "form" => [
            "shop_name" => ["value" => "Shopware"],
            "shop_email" => "text",
            "shop_locale" => [
                "type" => "select",
                "value" => "en-GB",
                "options" => [
                    "en-GB" => "English (UK)",
                    "en-US" => "English (US)",
                    "ach-UG" => "Lwo (Acholi)",
                    "af-ZA" => "Afrikaans - South Africa",
                    "ar-SA" => "العربية (Arabic - Saudi Arabia)",
                    "bg-BG" => "български език (Bulgarian)",
                    "bs-BA" => "Bosanski (Bosnian)",
                    "ca-ES" => "Català (Catalan)",
                    "cs-CZ" => "Čeština (Czech)",
                    "da-DK" => "Dansk (Danish)",
                    "de-AT" => "Deutsch (German - Austria)",
                    "de-CH" => "Deutsch (German - Switzerland)",
                    "de-DE" => "Deutsch (German)",
                    "el-GR" => "Ελληνικά (Greek)",
                    "es-AR" => "Español (Argentina)",
                    "es-ES" => "Español (Spanish)",
                    "et-EE" => "Eesti keel (Estonian)",
                    "fi-FI" => "Suomi (Finnish)",
                    "fr-FR" => "Français (French)",
                    "hi-IN" => "हिन्दी (Hindi)",
                    "hr-HR" => "Hrvatski (Croatian)",
                    "hu-HU" => "Magyar (Hungarian)",
                    "hy-AM" => "Հայերեն (Armenian)",
                    "id-ID" => "Indonesia (Indonesian)",
                    "it-IT" => "Italiano (Italian)",
                    "ja-JP" => "日本語 (Japanese)",
                    "ko-KR" => "한국어 (Korean)",
                    "lt-LT" => "lietuvių kalba (Lithuanian)",
                    "lv-LV" => "Latviešu (Latvian)",
                    "nl-NL" => "Nederlands (Dutch)",
                    "nn-NO" => "Norsk Nynorsk (Norwegian)",
                    "pl-PL" => "Polski (Polish)",
                    "pt-PT" => "Português (Portuguese)",
                    "ro-RO" => "Română (Romanian)",
                    "ru-RU" => "Русский (Russian)",
                    "sk-SK" => "Slovenčina (Slovak)",
                    "sl-SI" => "Slovenščina (Slovenian)",
                    "sq-AL" => "Shqip (Albanian)",
                    "sr-RS" => "Srpski (Serbian)",
                    "sv-SE" => "Svenska (Swedish)",
                    "th-TH" => "ภาษาไทย (Thai)",
                    "tr-TR" => "Türkçe (Turkish)",
                    "uk-UA" => "Українська (Ukrainian)",
                    "vi-VN" => "Tiếng Việt (Vietnamese)",
                ],
            ],
            "shop_currency" => ["value" => "USD"],
            "username" => ["value" => "shopadmin"],
            "first_name" => "text",
            "last_name" => "text",
            "email" => "text",
            "password" => "password",
        ],
        "database" => true,
        "resources" => [
            "composer" => ["src" => "shopware/production", "dst" => "/"],
        ],
        "server" => [
            "nginx" => [
                "template" => "shopware",
            ],
            "php" => [
                "supported" => ["8.2", "8.3", "8.4", "8.5"],
            ],
        ],
    ];

    protected function setupApplication(InstallationTarget $target, array $options): void {
        $this->appcontext->createFile(
            $target->getDocRoot(".htaccess"),
            file_get_contents(__DIR__ . "/.htaccess"),
        );

        // Update .env
        $envPath = $target->getDocRoot(".env");
        $env = $this->appcontext->readFile($envPath);

        $env = str_replace("APP_URL=http://127.0.0.1:8000", "APP_URL=" . $target->getUrl(), $env);
        $env = str_replace(
            "DATABASE_URL=mysql://root:root@localhost/shopware",
            "DATABASE_URL=mysql://" .
                $target->database->user .
                ":" .
                $target->database->password .
                "@" .
                $target->database->host .
                ":3306/" .
                $target->database->name,
            $env,
        );

        $this->appcontext->createFile($envPath, $env);

        // Install Shopware system
        $this->appcontext->runPHP($options["php_version"], $target->getDocRoot("bin/console"), [
            "system:install",
            "-n",
            "--shop-name=" . $options["shop_name"],
            "--shop-email=" . $options["shop_email"],
            "--shop-locale=" . $options["shop_locale"],
            "--shop-currency=" . $options["shop_currency"],
            "--basic-setup",
        ]);

        // Install language pack if needed
        if ($options["shop_locale"] !== "en-GB" && $options["shop_locale"] !== "de-DE") {
            $this->appcontext->runPHP($options["php_version"], $target->getDocRoot("bin/console"), [
                "translation:install",
                "-n",
                "--locales=" . $options["shop_locale"],
            ]);
        }

        // Create admin user
        $this->appcontext->runPHP($options["php_version"], $target->getDocRoot("bin/console"), [
            "user:create",
            $options["username"],
            "-a",
            "--firstName=" . $options["first_name"],
            "--lastName=" . $options["last_name"],
            "--password=" . $options["password"],
            "--email=" . $options["email"],
            "-n",
        ]);
    }
}
