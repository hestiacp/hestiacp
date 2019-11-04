<?php
declare(strict_types=1);

namespace Hestia\WebApp;

use Hestia\System\HestiaApp;

class AppWizard {
    private $domain;
    private $appsetup;
    private $appcontext;
    private $formNamespace = 'webapp';
    private $errors;

    private $database_config = [
        'database_create' => ['type'=>'boolean', 'value'=>false],
        'database_name' => ['type'=>'text', 'placeholder' => 'auto'],
        'database_user' => ['type'=>'text', 'placeholder' => 'auto'],
        'database_password' => ['type'=>'password', 'placeholder' => 'auto'],
    ];

    public function __construct(InstallerInterface $app, string $domain, HestiaApp $context)
    {
        $this->domain = $domain;
        $this->appcontext = $context;

        if (!$this->appcontext->userOwnsDomain($domain)) {
            throw new \Exception("User does not have access to domain [$domain]");
        }

        $this->appsetup = $app;
    }

    public function getStatus()
    {
        return $this->errors;
    }

    public function isDomainRootClean()
    {
        $this->appcontext->runUser('v-run-cli-cmd', [ "ls", $this->appsetup->getDocRoot() ], $status);
        if($status->code !== 0) {
            throw new \Exception("Cannot list domain files");
        }

        $files = $status->raw;
        if( count($files) > 2) {
            return false;
        }

        foreach($files as $file) {
            if ( !in_array($file,['index.html','robots.txt']) ) {
                return false;
            }
        }
        return true;
    }

    public function formNs()
    {
        return $this->formNamespace;
    }

    public function getOptions()
    {
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

    public function execute(array $options)
    {

        $options = $this->filterOptions($options);

        $random_num = (string)random_int(10000, 99999);
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

