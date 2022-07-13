<?php

namespace Hestia\WebApp\Installers\Wordpress;

use Hestia\System\Util;
use Hestia\WebApp\Installers\BaseSetup as BaseSetup;
use function Divinity76\quoteshellarg\quoteshellarg;

class WordpressSetup extends BaseSetup
{
    protected $appInfo = [
        'name' => 'Wordpress',
        'group' => 'cms',
        'enabled' => true,
        'version' => 'latest',
        'thumbnail' => 'wp-thumb.png'
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
        $distconfig = preg_replace(
            [
            '/database_name_here/', '/username_here/', '/password_here/', '/utf8/', '/wp_/'
        ],
            [
            $this->appcontext->user() . '_' . $options['database_name'],
            $this->appcontext->user() . '_' . $options['database_user'],
            $options['database_password'],
            'utf8mb4',
            Util::generate_string(3, false).'_'
            ],
            $result->text
        );

        while (strpos($distconfig, 'put your unique phrase here') !== false) {
            $distconfig = preg_replace('/put your unique phrase here/', Util::generate_string(64), $distconfig, 1);
        }

        $tmp_configpath = $this->saveTempFile($distconfig);

        if (!$this->appcontext->runUser('v-move-fs-file', [$tmp_configpath, $this->getDocRoot("wp-config.php")], $result)) {
            throw new \Exception("Error installing config file in: " . $tmp_configpath . " to:" . $this->getDocRoot("wp-config.php") . $result->text);
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
            . quoteshellarg($webDomain.$options['install_directory']."/wp-admin/install.php?step=2")
            . " -d " . quoteshellarg(
                "weblog_title=" . rawurlencode($options['site_name'])
            . "&user_name="      . rawurlencode($options['wordpress_account_username'])
            . "&admin_password=" . rawurlencode($options['wordpress_account_password'])
            . "&admin_password2=". rawurlencode($options['wordpress_account_password'])
            . "&admin_email="    . rawurlencode($options['wordpress_account_email'])
            ), $output, $return_var);
        
        if ( strpos(implode(PHP_EOL,$output),'Error establishing a database connection' !== false)){
           throw new \Exception('Error establishing a database connection'); 
        }

        if ($return_var > 0) {
            throw new \Exception(implode(PHP_EOL, $output));
        }
        return ($return_var === 0);
    }
}
