<?php

namespace Hestia\WebApp\Installers\Dolibarr;

use Hestia\WebApp\Installers\BaseSetup as BaseSetup;

class DolibarrSetup extends BaseSetup {
      protected $appInfo = [
              "name" => "Dolibarr", 
              "group" => "CRM",
              "enabled" => true,
              "version" => "20.0.2",
              "thumbnail" => "dolibarr-thumb.png",
      ];

      protected $appname = "dolibarr";

      protected $config = [
              "form" => [
                      "dolibarr_account_username" => ["value" => "admin"],
                      "dolibarr_account_password" => "password",
                      "language" => [
                              "type" => "select",
                              "options" => [
                                      "en_EN" => "English",
                                      "es_ES" => "Spanish", 
                                      "fr_FR" => "French",
                                      "de_DE" => "German",
                                      "pt_PT" => "Portuguese",
                                      "it_IT" => "Italian",
                              ],
                              "default" => "en_EN",
                      ],
              ],
              "database" => true,
              "resources" => [
                      "archive" => [
                              "src" => "https://github.com/Dolibarr/dolibarr/archive/refs/tags/20.0.2.zip",
                      ],
              ],
              "server" => [
                      "nginx" => [
                              "template" => "dolibarr",
                      ],
                      "php" => [
                              "supported" => ["7.4", "8.0", "8.1", "8.2", "8.3"],
                      ],
              ],
      ];

      public function install(array $options = null): bool {
              parent::install($options);
              parent::setup($options);

              $this->appcontext->runUser(
                      "v-copy-fs-directory",
                      [
                              $this->getDocRoot($this->extractsubdir . "/dolibarr-20.0.2/."),
                              $this->getDocRoot(),
                      ],
                      $status
              );

              $this->appcontext->run(
                      "v-list-web-domain",
                      [$this->appcontext->user(), $this->domain, "json"],
                      $status
              );
              
              $sslEnabled = $status->json[$this->domain]["SSL"] == "no" ? false : true;
              $webDomain = ($sslEnabled ? "https://" : "http://") . $this->domain;

              $language = $options['language'] ?? 'en_EN';
              $username = rawurlencode($options['dolibarr_account_username']);
              $password = rawurlencode($options['dolibarr_account_password']);
              $databaseUser = rawurlencode($this->appcontext->user() . "_" . $options['database_user']);
              $databasePassword = rawurlencode($options['database_password']);
              $databaseName = rawurlencode($this->appcontext->user() . "_" . $options['database_name']);

              $this->appcontext->runUser(
                      "v-copy-fs-file",
                      [
                              $this->getDocRoot("htdocs/conf/conf.php.example"),
                              $this->getDocRoot("htdocs/conf/conf.php")
                      ],
                      $status
              );

              $this->appcontext->runUser(
                      "v-change-fs-file-permission",
                      [
                              $this->getDocRoot("htdocs/conf/conf.php"),
                              "666"
                      ],
                      $status
              );

              $cmd = "curl --request POST " .
                      ($sslEnabled ? "" : "--insecure ") .
                      "--url $webDomain/install/step1.php " .
                      "--data 'testpost=ok&action=set" .
                      "&main_dir=" . rawurlencode($this->getDocRoot("htdocs")) .
                      "&main_data_dir=" . rawurlencode($this->getDocRoot("documents")) .
                      "&main_url=" . rawurlencode($webDomain) .
                      "&db_name=$databaseName" .
                      "&db_type=mysqli" .
                      "&db_host=localhost" .
                      "&db_port=3306" .
                      "&db_prefix=llx_" .
                      "&db_user=$databaseUser" .
                      "&db_pass=$databasePassword" .
                      "&selectlang=$language' && " .
                      
                      "curl --request POST " .
                      ($sslEnabled ? "" : "--insecure ") .
                      "--url $webDomain/install/step2.php " .
                      "--data 'testpost=ok&action=set" .
                      "&dolibarr_main_db_character_set=utf8" .
                      "&dolibarr_main_db_collation=utf8_unicode_ci" .
                      "&selectlang=$language' && " .
                      
                      "curl --request POST " .
                      ($sslEnabled ? "" : "--insecure ") .
                      "--url $webDomain/install/step4.php " .
                      "--data 'testpost=ok&action=set" .
                      "&dolibarrpingno=checked" .
                      "&selectlang=$language' && " .
                      
                      "curl --request POST " .
                      ($sslEnabled ? "" : "--insecure ") .
                      "--url $webDomain/install/step5.php " .
                      "--data 'testpost=ok&action=set" .
                      "&login=$username" .
                      "&pass=$password" .
                      "&pass_verif=$password" .
                      "&selectlang=$language'";

              exec($cmd, $output, $return_var);
              if ($return_var > 0) {
                      throw new \Exception(implode(PHP_EOL, $output));
              }

              $this->cleanup();

              return $status->code === 0;
      }
}
