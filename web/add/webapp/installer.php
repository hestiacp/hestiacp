<?php
// declare(strict_types=1);
require_once("Hestia.php");
class AppInstaller {
    private $domain;
    private $appsetup;
    private $appcontext;
    private $formNamespace = 'webapp';
    private $errors;

    private $database_config = [
        'database_create' => ['type'=>'boolean', 'value'=>false],
        'database_name' => 'text',
        'database_user' => 'text',
        'database_password' => 'password',
    ];

    public function __construct(string $app, string $domain, HestiaApp $context) {
        $this->domain = $domain;
        $this->appcontext = $context;

        if (!$this->appcontext->userOwnsDomain($domain)) {
            throw new Exception("User does not have access to domain [$domain]");
        }

        $appclass = ucfirst($app).'Setup';
        if(file_exists('installer/' . $appclass.".php"))
            require_once('installer/' . $appclass.".php");

        if (class_exists($appclass)) {
            $this->appsetup = new $appclass($domain, $this->appcontext);
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

    public function filterOptions(array $options)
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

    public function execute(array $options) {
        if (!$this->appsetup) return;

        $options = $this->filterOptions($options);

        $random_num = random_int(10000, 99999);
        if ($this->appsetup->withDatabase() && !empty($options['database_create'])) {
            if(empty($options['database_name'])) {
                $options['database_name'] = $random_num;
            }

            if(empty($options['database_user'])) {
                $options['database_user'] = $random_num;
            }

            if(empty($options['database_password'])) {
                $options['database_password'] = bin2hex(random_bytes(10));
            }

            if(!$this->appcontext->databaseAdd($options['database_name'], $options['database_user'], $options['database_password'])) {
                $this->errors[] = "Error adding database";
                return false;
            }
        }

        if(empty($this->errors)) {
            return $this->appsetup->install($options);
        }
    }
}

// TO DO : create a WebDomain model class, hidrate from v-list-web-domain(json)
