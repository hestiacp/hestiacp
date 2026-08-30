<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\ConcreteCMS;

use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;

class ConcreteCMSSetup extends BaseSetup {
    protected array $info = [
        "name" => "ConcreteCMS",
        "group" => "cms",
        "version" => "latest",
        "thumbnail" => "concrete-cms-logo.svg",
    ];

    protected array $config = [
        "form" => [
            "email" => "text",
            "password" => "password",
            "language" => [
                "type" => "select",
                "value" => "en_US",
                "options" => [
                    "en_US" => "English (United States)",
                    "en_GB" => "English (United Kingdom)",
                    "cs_CZ" => "čeština (Česko)",
                    "da_DK" => "dansk (Danmark)",
                    "de_DE" => "Deutsch (Deutschland)",
                    "de_CH" => "Deutsch (Schweiz)",
                    "fr_FR" => "français (France)",
                    "it_IT" => "italiano (Italia)",
                    "nl_BE" => "Nederlands (België)",
                    "nl_NL" => "Nederlands (Nederland)",
                    "pl_PL" => "polski (Polska)",
                    "fi_FI" => "suomi (Suomi)",
                    "tr_TR" => "Türkçe (Türkiye)",
                    "el_GR" => "Ελληνικά (Ελλάδα)",
                    "ru_RU" => "русский (Россия)",
                    "uk_UA" => "українська (Україна)",
                    "zh_CN" => "中文 (中国)",
                    "ja_JP" => "日本語 (日本)",
                ],
            ],
        ],
        "database" => true,
        "resources" => [
            "archive" => ["src" => "https://www.concretecms.org/download/latest.zip"],
        ],
        "server" => [
            "nginx" => [
                "template" => "default",
            ],
            "php" => [
                "supported" => ["7.4", "8.0", "8.1", "8.2", "8.3", "8.4", "8.5"],
            ],
        ],
    ];

    protected function setupApplication(InstallationTarget $target, array $options): void {
        // Move files from Concrete CMS folder to root
        $dir = glob($target->getDocRoot("concrete-cms-*/"));
        $this->appcontext->copyDirectory($dir[0] . ".", $target->getDocRoot());

        $this->appcontext->runPHP(
            $options["php_version"],
            $target->getDocRoot("concrete/bin/concrete"),
            [
                "c5:install",
                "--no-interaction",
                "--db-server=" . $target->database->host,
                "--db-database=" . $target->database->name,
                "--db-username=" . $target->database->user,
                "--db-password=" . $target->database->password,
                "--admin-email=" . $options["email"],
                "--admin-password=" . $options["password"],
                "--language=" . $options["language"],
                "--site-locale=" . $options["language"],
            ],
        );

        // Cleanup
        $this->appcontext->deleteDirectory($dir[0]);
    }
}
