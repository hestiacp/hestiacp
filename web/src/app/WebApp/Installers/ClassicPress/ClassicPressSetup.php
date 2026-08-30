<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\ClassicPress;

use Hestia\System\Util;
use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;

class ClassicPressSetup extends BaseSetup {
    protected array $info = [
        "name" => "ClassicPress",
        "group" => "cms",
        "version" => "latest",
        "thumbnail" => "classicpress-logo.svg",
    ];

    protected array $config = [
        "form" => [
            "site_name" => ["type" => "text", "value" => "ClassicPress Blog"],
            "username" => ["value" => "cpadmin"],
            "email" => "text",
            "password" => "password",
            "language" => [
                "type" => "select",
                "value" => "en_US",
                "options" => [
                    "ar_AR" => "Arabic",
                    "zh_CN" => "Chinese Simplified",
                    "cs_CZ" => "Czech",
                    "nl_NL" => "Dutch",
                    "en_GB" => "English (British)",
                    "en_US" => "English (United States)",
                    "fr_FR" => "French",
                    "de_DE" => "German",
                    "it_IT" => "Italian",
                    "sv_SE" => "Swedish",
                    "pt_BR" => "Portuguese (Brazil)",
                ],
            ],
            "search_engine_indexing" => [
                "type" => "select",
                "options" => ["Allow", "Discourage"],
            ],
            "disable_XML-RPC?" => [
                "type" => "select",
                "options" => ["Yes", "No"],
            ],
        ],
        "database" => true,
        "resources" => [
            "archive" => ["src" => "https://www.classicpress.net/latest.zip"],
        ],
        "server" => [
            "nginx" => [
                "template" => "classicpress",
            ],
            "php" => [
                "supported" => ["7.4", "8.0", "8.1", "8.2", "8.3", "8.4", "8.5"],
            ],
        ],
    ];

    protected function setupApplication(InstallationTarget $target, array $options): void {
        // Move files from ClassicPress folder to root
        $dir = glob($target->getDocRoot("ClassicPress-*/"));
        $this->appcontext->copyDirectory($dir[0] . ".", $target->getDocRoot());

        $this->appcontext->runWp($options["php_version"], [
            "config",
            "create",
            "--dbname=" . $target->database->name,
            "--dbuser=" . $target->database->user,
            "--dbpass=" . $target->database->password,
            "--dbhost=" . $target->database->host,
            "--dbprefix=" . "cp_" . Util::generateString(5, false) . "_",
            "--dbcharset=utf8mb4",
            "--locale=" . $options["language"],
            "--path=" . $target->getDocRoot(),
        ]);

        // Ensure language pack exists
        if ($options["language"] !== "en_US") {
            $this->appcontext->sendPostRequest($target->getUrl() . "/wp-admin/install.php?step=1", [
                "language" => $options["language"],
            ]);
        }

        $this->appcontext->sendPostRequest($target->getUrl() . "/wp-admin/install.php?step=2", [
            "weblog_title" => $options["site_name"],
            "user_name" => $options["username"],
            "admin_password" => $options["password"],
            "admin_password2" => $options["password"],
            "admin_email" => $options["email"],
            "blog_public" => $options["search_engine_indexing"] === "Allow" ? 1 : 0,
            "language" => $options["language"],
        ]);

        // Disable XML-RPC
        if ($options["disable_XML-RPC?"] === "Yes") {
            $this->appcontext->runWp($options["php_version"], [
                "option",
                "update",
                "disable_xml_rpc",
                "1",
                "--path=" . $target->getDocRoot(),
            ]);
        }

        // Delete empty ClassicPress folder
        $this->appcontext->deleteDirectory($dir[0]);
    }
}
