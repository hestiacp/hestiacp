<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\Dolibarr;

use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;
use function file_get_contents;

class DolibarrSetup extends BaseSetup {
    protected array $info = [
        "name" => "Dolibarr",
        "group" => "crm",
        "version" => "23.0.3",
        "thumbnail" => "dolibarr_logo.svg",
    ];

    protected array $config = [
        "form" => [
            "username" => [
                "value" => "doliadmin",
            ],
            "password" => "password",
            "language" => [
                "type" => "select",
                "value" => "en_EN",
                "options" => [
                    "en_EN" => "English",
                    "fr_FR" => "French",
                    "de_DE" => "German",
                    "es_ES" => "Spanish",
                    "it_IT" => "Italian",
                    "pt_PT" => "Portuguese",
                ],
            ],
        ],

        "database" => true,

        "resources" => [
            "archive" => [
                "src" => "https://github.com/Dolibarr/dolibarr/archive/refs/tags/23.0.3.zip",
            ],
        ],

        "server" => [
            "nginx" => [
                "template" => "dolibarr",
            ],
            "php" => [
                "supported" => ["8.1", "8.2", "8.3", "8.4"],
            ],
        ],
    ];

    protected function setupApplication(InstallationTarget $target, array $options): void {
        $this->appcontext->copyDirectory(
            $target->getDocRoot("/dolibarr-" . $this->info["version"] . "/."),
            $target->getDocRoot(),
        );

        $language = $options["language"];

        $this->appcontext->moveFile(
            $target->getDocRoot("htdocs/conf/conf.php.example"),
            $target->getDocRoot("htdocs/conf/conf.php"),
        );

        $this->appcontext->changeFilePermissions(
            $target->getDocRoot("htdocs/conf/conf.php"),
            "666",
        );

        $this->appcontext->addDirectory($target->getDocRoot("documents"));

        $this->appcontext->createFile(
            $target->getDocRoot(".htaccess"),
            file_get_contents(__DIR__ . "/.htaccess"),
        );

        // Adapted from YunoHost install script: https://github.com/YunoHost-Apps/dolibarr_ynh/blob/master/scripts/install
        $this->appcontext->sendPostRequest($target->getUrl() . "/install/step1.php", [
            "testpost" => "ok",
            "action" => "set",
            "main_dir" => $target->getDocRoot("htdocs"),
            "main_data_dir" => $target->getDocRoot("documents"),
            "main_url" => $target->getUrl(),
            "main_force_https" => $target->domain->isSslEnabled,
            "db_type" => "mysqli",
            "db_host" => $target->database->host,
            "db_port" => "3306",
            "db_prefix" => "llx_",
            "db_name" => $target->database->name,
            "db_user" => $target->database->user,
            "db_pass" => $target->database->password,
            "selectlang" => $language,
        ]);

        $this->appcontext->sendPostRequest($target->getUrl() . "/install/step2.php", [
            "testpost" => "ok",
            "action" => "set",
            "dolibarr_main_db_character_set" => "utf8",
            "dolibarr_main_db_collation" => "utf8_unicode_ci",
            "selectlang" => $language,
        ]);

        // Give time to complete the step 2
        sleep(10);

        // There's no step 3 and step 4 is an HTML form to ensure admin credentials
        $this->appcontext->sendPostRequest($target->getUrl() . "/install/step5.php", [
            "testpost" => "ok",
            "action" => "set",
            "login" => $options["username"],
            "pass" => $options["password"],
            "pass_verif" => $options["password"],
            "installlock" => "1",
            "selectlang" => $language,
        ]);

        $this->appcontext->changeFilePermissions(
            $target->getDocRoot("htdocs/conf/conf.php"),
            "400",
        );

        $this->appcontext->deleteDirectory(
            $target->getDocRoot("/dolibarr-" . $this->info["version"] . "/"),
        );
    }
}
