<?php

abstract class BaseSetup {
    public function getConfig($section=null) {
        return (!empty($section))? $this->config[$section] : $this->config;
    }

    public function getOptions() {
        return $this->getConfig('form');
    }

    public function withDatabase() {
        return ($this->getConfig('database') === true);
    }
}


class WordpressSetup extends BaseSetup {
    protected $config = [
        'form' => [
            'protocol' => [ 
                'type' => 'select',
                'options' => ['http','ion','https'],
                'value' => 'ion',
            ],
            'subdir' => ['type'=>'text', 'value'=>'/'],
            'site_name' => ['type'=>'text', 'value'=>'Wordpress Blog'],
            'site_description' => ['value'=>'Another wordpresss site'],
            'wordpress_account_username' => ['value'=>'wpadmin'],
            'wordpress_account_password' => 'password',
            ],
        'database' => true,
        'url' => 'https://wordpress.org/wordpress-5.2.2.tar.gz'
    ];
}


class HestiaApp {

    public function run($cmd, $args, &$return_code=null) {

        $cli_script = HESTIA_CMD . '/' . basename($cmd);
        $cli_arguments = '';

        if (!empty($args) && is_array($args)) {
            foreach ($args as $arg) {
                $cli_arguments .= escapeshellarg($arg) . ' ';
            }
        } else {
            $cli_arguments = escapeshellarg($args);
        }

        exec ($cli_script . ' ' . $cli_arguments, $output, $return_code);

        $result['code'] = $return_code;
        $result['raw']  = $output;
        $result['text'] = implode( PHP_EOL, $result['raw']);
        $result['json'] = json_decode($result['text'], true);

        return (object)$result;
    }

    public function realuser() {
        // Logged in user 
        return $_SESSION['user'];
    }

    public function user() {
        // Effective user
        if ($this->realuser() == 'admin' && !empty($_SESSION['look'])) {
            return $_SESSION['look'];
        }
        return $this->realuser();
    }

    public function userOwnsDomain($domain) {
        $status = null;
        $this->run('v-list-web-domain', [$this->user(), $domain, 'json'], $status);
        return ($status === 0);
    }

    public function databaseAdd($dbname,$dbuser,$dbpass) {
        $v_password = tempnam("/tmp","vst");
        $fp = fopen($v_password, "w");
        fwrite($fp, $dbpass."\n");
        fclose($fp);
        $this->run('v-add-database', [$this->user(), $dbname, $dbuser, $v_password], $status);
        unlink($v_password);
        return ($status === 0);
    }
}


class AppInstaller {

    private $domain;
    private $appsetup;
    private $appcontext;
    private $formNamespace = 'webapp';
    private $errors;

    private $database_config = [
        'database_name' => 'text',
        'database_user' => 'text',
        'database_password' => 'password',
    ];

    public function __construct($app, $domain, $context) {
        $this->domain = $domain;
        $this->appcontext = $context;

        if (!$this->appcontext->userOwnsDomain($domain)) {
            throw new Exception("User does not have access to domain [$domain]");
        }

        $appclass = ucfirst($app).'Setup';
        if (class_exists($appclass)) {
            $this->appsetup = new $appclass();
        }

        if (!$this->appsetup) {
            throw new Exception( "Application [".ucfirst($app)."] does not have a installer" );
        }
    }

    public function getStatus() {
        return $this->errors;
    }

    public function formNs() {
        return $this->formNamespace;
    }

    public function getOptions() {
        if(!$this->appsetup) return;

        $options = $this->appsetup->getOptions();
        if ($this->appsetup->withDatabase()) {
            $options = array_merge($options, $this->database_config);
        }
        return $options;
    }

    public function filterOptions($options)
    {
        $filteredoptions = [];
        array_walk($options, function($value, $key) use(&$filteredoptions) {
            if (strpos($key, $this->formNs().'_')===0) {
                $option = str_replace($this->formNs().'_','',$key);
                $filteredoptions[$option] = $value;
            }
        });
        return $filteredoptions;
    }

    public function execute($options) {
        if (!$this->appsetup) return;

        $options = $this->filterOptions($options);

        $random_num = random_int(10000, 99999);
        if ($this->appsetup->withDatabase()) {

            if(empty($options['database_name'])) {
                $options['database_name'] = $random_num;
            }

            if(empty($options['database_user'])) {
                $options['database_user'] = $random_num;
            }

            if(empty($options['database_password'])) {
                $options['database_password'] = bin2hex(random_bytes(10));
            }

            if(!$this->appcontext->databaseAdd($options['database_name'], $options['database_user'], $options['database_password'])){
                $this->errors[] = "Error adding database";
                return false;
            }
        }
    }
}
