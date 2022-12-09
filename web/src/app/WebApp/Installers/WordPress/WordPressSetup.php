<?php

namespace Hestia\WebApp\Installers\Wordpress;

use Hestia\System\Util;
use Hestia\WebApp\Installers\BaseSetup as BaseSetup;

class WordpressSetup extends BaseSetup {
	protected $appInfo = [
		"name" => "WordPress",
		"group" => "cms",
		"enabled" => true,
		"version" => "latest",
		"thumbnail" => "wp-thumb.png",
	];

    protected $appname = 'wordpress';
    protected $config = [
        'form' => [
            //'protocol' => [
            //    'type' => 'select',
            //    'options' => ['http','https'],
            //],

            'site_name' => ['type'=>'text', 'value'=>'WordPress Blog'],
            'wordpress_account_username' => ['value'=>'wpadmin'],
            'wordpress_account_email' => 'text',
            'wordpress_account_password' => 'password',
            'install_directory' => ['type'=>'text', 'value'=>'', 'placeholder'=>'/'],
            'language' => [
                'type' => 'select',
                'value' => 'en_US',
                'options' => [
                        'cs_CZ' => 'Czech',
                        'de_DE' => 'German',
                        'es_ES' => 'Spanish',
                        'en_US' => 'English',
                        'fr_FR' => 'French',
                        'hu_HU' => 'Hungarian',
                        'it_IT' => 'Italian',
                        'nl_NL' => 'Dutch',
                        'pt_PT' => 'Portuguese',
                        'pt_BR' => 'Portuguese (Brazil)',
                        'sk_SK' => 'Slovak',
                        'sr_RS' => 'Serbian',
                        'tr_TR' => 'Turkish',
                        'ru_RU' => 'Russian',
                        'uk' => 'Ukrainian',
                        'zh-CN' => 'Simplified Chinese (China)',
                        'zh_TW' => 'Traditional Chinese',
                    ]
                ],
            ],
        'database' => true,
        'resources' => [
            'wp'  => [ 'src' => 'https://wordpress.org/latest.tar.gz' ],
        ],
        'server' => [
            'nginx' => [
                'template' => 'wordpress',
            ],
            'php' => [
                'supported' => [ '7.4','8.0','8.1' ],
            ]
        ],

    ];

    public function install(array $options = null)
    {
        parent::setAppDirInstall($options['install_directory']);
        parent::install($options);
        parent::setup($options);

        $this->appcontext->runUser('v-open-fs-file', [$this->getDocRoot("wp-config-sample.php")], $result);
        foreach($result -> raw as $line_num => $line){
            if ( '$table_prefix =' === substr( $line, 0, 15 ) ) {
                $result -> raw[ $line_num ] = '$table_prefix = \'' . addcslashes( Util::generate_string(5, false).'_', "\\'" ) . "';\r\n";
                continue;
            }
            if ( ! preg_match( '/^define\(\s*\'([A-Z_]+)\',([ ]+)/', $line, $match ) ) {
                continue;
            }
            $constant = $match[1];
            $padding  = $match[2];
            switch ( $constant ) {
            case 'DB_NAME':
                $result -> raw [ $line_num ] = "define( '" . $constant . "'," . $padding . "'" . addcslashes($this->appcontext->user() . '_' . $options['database_name'], "\\'" ) . "' );";
                break;
            case 'DB_USER':
                $result -> raw [ $line_num ] = "define( '" . $constant . "'," . $padding . "'" . addcslashes($this->appcontext->user() . '_' . $options['database_user'], "\\'" ) . "' );";
                break;
            case 'DB_PASSWORD':
                $result -> raw [ $line_num ] = "define( '" . $constant . "'," . $padding . "'" . addcslashes($options['database_password'], "\\'" ) . "' );";
                break;
            case 'DB_HOST':
                $result -> raw [ $line_num ] = "define( '" . $constant . "'," . $padding . "'" . addcslashes('localhost', "\\'" ) . "' );";
                break;
            case 'DB_CHARSET':
                $result -> raw [ $line_num ] = "define( '" . $constant . "'," . $padding . "'" . addcslashes('utf8mb4', "\\'" ) . "' );";
                break;
            case 'AUTH_KEY':
            case 'SECURE_AUTH_KEY':
            case 'LOGGED_IN_KEY':
            case 'NONCE_KEY':
            case 'AUTH_SALT':
            case 'SECURE_AUTH_SALT':
            case 'LOGGED_IN_SALT':
            case 'NONCE_SALT':
                $result -> raw [ $line_num ] = "define( '" . $constant . "'," . $padding . "'"  . Util::generate_string(64) . "' );";
                break;
            }
        }
        
        $tmp_configpath = $this->saveTempFile(implode("\r\n",$result -> raw ));

        if (!$this->appcontext->runUser('v-move-fs-file', [$tmp_configpath, $this->getDocRoot("wp-config.php")], $result)) {
            throw new \Exception("Error installing config file in: " . $tmp_configpath . " to:" . $this->getDocRoot("wp-config.php") . $result->text);
        }

        $this->appcontext->downloadUrl('https://raw.githubusercontent.com/roots/wp-password-bcrypt/master/wp-password-bcrypt.php', null, $plugin_output);
        $this->appcontext->runUser('v-add-fs-directory', [$this->getDocRoot("wp-content/mu-plugins/")], $result);
        if (!$this->appcontext->runUser('v-copy-fs-file', [$plugin_output->file, $this->getDocRoot("wp-content/mu-plugins/wp-password-bcrypt.php")], $result)) {
            throw new \Exception("Error installing wp-password-bcrypt file in: " . $plugin_output->file . " to:" . $this->getDocRoot("wp-content/mu-plugins/wp-password-bcrypt.php") . $result->text);
        }

        $this->appcontext->run('v-list-web-domain', [$this->appcontext->user(), $this->domain, 'json'], $status);
        $sslEnabled = ($status->json[$this->domain]['SSL'] == 'no' ? 0 : 1);
        $webDomain = ($sslEnabled ? "https://" : "http://") . $this->domain . "/";
        $webPort= ($sslEnabled ? "443" : "80");

        if (substr($options['install_directory'], 0, 1) == '/') {
            $options['install_directory'] = substr($options['install_directory'], 1);
        }
        if (substr($options['install_directory'], -1, 1) == '/') {
            $options['install_directory'] = substr($options['install_directory'], 0, strlen($options['install_directory']) - 1);
        }

        exec("/usr/bin/curl --location --post301 --insecure --resolve ".$this->domain.":$webPort:".$this->appcontext->getWebDomainIp($this->domain)." "
            . escapeshellarg($webDomain.$options['install_directory']."/wp-admin/install.php?step=2")
            . " -d " . escapeshellarg(
                "weblog_title=" . rawurlencode($options['site_name'])
            . "&user_name="      . rawurlencode($options['wordpress_account_username'])
            . "&admin_password=" . rawurlencode($options['wordpress_account_password'])
            . "&admin_password2=". rawurlencode($options['wordpress_account_password'])
            . "&admin_email="    . rawurlencode($options['wordpress_account_email'])
            ), $output, $return_var);
        
        if ( strpos(implode(PHP_EOL,$output),'Error establishing a database connection') !== false){
           throw new \Exception('Error establishing a database connection'); 
        }
        if ($return_var > 0) {
            throw new \Exception(implode(PHP_EOL, $output));
        }
        return ($return_var === 0);
    }
}
