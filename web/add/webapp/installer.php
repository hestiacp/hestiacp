<?php



function join_paths() {
    $paths = array();

    foreach (func_get_args() as $arg) {
        if ($arg !== '') { $paths[] = $arg; }
    }

    return preg_replace('#/+#','/',join('/', $paths));
}

function generate_string($length = 16) {
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ~`!@|#[]$%^&*() _-=+{}:;<>?,./';
    $random_string = '';
    for($i = 0; $i < $length; $i++) {
        $random_string .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $random_string;
}



abstract class BaseSetup {

    protected $domain;
    public function __construct($domain, $appcontext) {

        // validate domain name
        if(filter_var($domain, FILTER_VALIDATE_DOMAIN) === false) {
            throw new Exception("Invalid domain name");
        }

        $this->domain = $domain;
        $this->appcontext = $appcontext;
    }
    public function getConfig($section=null) {
        return (!empty($section))? $this->config[$section] : $this->config;
    }

    public function getOptions() {
        return $this->getConfig('form');
    }

    public function withDatabase() {
        return ($this->getConfig('database') === true);
    }

    public function getDocRoot($docrelative=null) {
        $domain_path = $this->appcontext->getWebDomainPath($this->domain);
        if(empty($domain_path)){
            return false;
        }

        return join_paths($domain_path, "public_html", $docrelative);
    }

    public function retrieveResources() {
        return $this->appcontext->archiveExtract(
            $this->getConfig('url'),
            $this->getDocRoot(), 1);
    }

    public function install($options) {
        return $this->retrieveResources();
    }
}


class WordpressSetup extends BaseSetup {

    protected $appname = 'wordpress';
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
            'wordpress_account_email' => 'text',
            'wordpress_account_password' => 'password',
            ],
        'database' => true,
        'url' => 'https://wordpress.org/wordpress-5.2.2.tar.gz'
    ];

    public function install($options) {
        parent::install($options);

        $this->appcontext->runUser('v-open-fs-file',[$this->getDocRoot("wp-config-sample.php")], $result);

        $distconfig = preg_replace( [
                '/database_name_here/', '/username_here/', '/password_here/'
            ], [
                $this->appcontext->user() . '_' . $options['database_name'],
                $this->appcontext->user() . '_' . $options['database_user'],
                $options['database_password']
            ],
            $result->text);

        while (strpos($distconfig, 'put your unique phrase here') !== false) {
            $distconfig = preg_replace( '/put your unique phrase here/', generate_string(64), $distconfig, 1);
        }

        $tmp_configpath = $this->appcontext->saveTempFile($distconfig);

        if(!$this->appcontext->runUser('v-copy-fs-file',[$tmp_configpath, $this->getDocRoot("wp-config.php")], $result)) {
            return false;
        }

        exec("/usr/bin/curl --post301 --insecure --resolve ".$this->domain.":80:".$this->appcontext->getWebDomainIp($this->domain)." " 
            . escapeshellarg("http://".$this->domain."/wp-admin/install.php?step=2")
            . " -d " . escapeshellarg(
                 "weblog_title=" . rawurlencode($options['site_name'])
            . "&user_name="      . rawurlencode($options['wordpress_account_username'])
            . "&admin_password=" . rawurlencode($options['wordpress_account_password'])
            . "&admin_password2=". rawurlencode($options['wordpress_account_password'])
            . "&admin_email="    . rawurlencode($options['wordpress_account_email'])), $output, $return_var);

        return ($return_var === 0);
    }
}

class OpencartSetup extends BaseSetup {

    protected $appname = 'opencart';
    protected $config = [
        'form' => [
            'protocol' => [
                'type' => 'select',
                'options' => ['http','ion','https'],
                'value' => 'ion',
            ],
            'subdir' => ['type'=>'text', 'value'=>'/'],
            'opencart_account_username' => ['value'=>'ocadmin'],
            'opencart_account_email' => 'text',
            'opencart_account_password' => 'password',
            ],
        'database' => true,
        'url' => 'https://github.com/opencart/opencart/releases/download/3.0.3.2/opencart-3.0.3.2.zip'
        //'url' => 'https://github.com/opencart/opencart/archive/3.0.3.2.tar.gz'
    ];

    public function retrieveResources() {

        #cleanup temp folder
        $this->appcontext->runUser('v-delete-fs-directory', [$this->getDocRoot("/tmp-opencart")], $result);

        $this->appcontext->archiveExtract($this->getConfig('url'), $this->getDocRoot("/tmp-opencart"), 1);

        $this->appcontext->runUser('v-copy-fs-directory',[
            $this->getDocRoot("/tmp-opencart/upload/."),
            $this->getDocRoot()], $result);

        $this->appcontext->runUser('v-delete-fs-directory',[$this->getDocRoot("/tmp-opencart")], $result);
        return true;
    }

    public function install($options) {
        parent::install($options);

        $this->appcontext->runUser('v-copy-fs-file',[$this->getDocRoot("config-dist.php"), $this->getDocRoot("config.php")]);
        $this->appcontext->runUser('v-copy-fs-file',[$this->getDocRoot("admin/config-dist.php"), $this->getDocRoot("admin/config.php")]);

        $this->appcontext->runUser('v-change-fs-file-permission',[$this->getDocRoot("config.php"), '666']);
        $this->appcontext->runUser('v-change-fs-file-permission',[$this->getDocRoot("admin/config.php"), '666']);

        exec("/usr/bin/php " . escapeshellarg($this->getDocRoot("/install/cli_install.php")) . " install"
            . " --db_username " . escapeshellarg($this->appcontext->user() . '_' .$options['database_user'])
            . " --db_password " . escapeshellarg($options['database_password'])
            . " --db_database " . escapeshellarg($this->appcontext->user() . '_' .$options['database_name'])
            . " --username "    . escapeshellarg($options['opencart_account_username'])
            . " --password "    . escapeshellarg($options['opencart_account_password'])
            . " --email "       . escapeshellarg($options['opencart_account_email'])
            . " --http_server " . escapeshellarg("http://" . $this->domain . "/")
            , $output, $return_var);

        $this->appcontext->runUser('v-change-fs-file-permission',[$this->getDocRoot("config.php"), '640']);
        $this->appcontext->runUser('v-change-fs-file-permission',[$this->getDocRoot("admin/config.php"), '640']);

        return ($return_var === 0);
    }
}

class HestiaApp {

    protected const TMPDIR_DOWNLOADS="/tmp/hestia-webapp";

    public function __construct() {
        mkdir(self::TMPDIR_DOWNLOADS);
    }

    public function run($cmd, $args, &$cmd_result=null) {
        $cli_script = HESTIA_CMD . '/' . basename($cmd);
        $cli_arguments = '';

        if (!empty($args) && is_array($args)) {
            foreach ($args as $arg) {
                $cli_arguments .= escapeshellarg($arg) . ' ';
            }
        } else {
            $cli_arguments = escapeshellarg($args);
        }

        exec ($cli_script . ' ' . $cli_arguments, $output, $exit_code);

        $result['code'] = $exit_code;
        $result['args'] = $cli_arguments;
        $result['raw']  = $output;
        $result['text'] = implode( PHP_EOL, $output);
        $result['json'] = json_decode($result['text'], true);
        $cmd_result = (object)$result;

        return ($exit_code === 0);
    }

    public function runUser($cmd, $args, &$cmd_result=null) {
        if (!empty($args) && is_array($args)) {
            array_unshift($args, $this->user());
        }
        else {
            $args = [$this->user(), $args];
        }
        return $this->run($cmd, $args, $cmd_result);
    }

    // Logged in user
    public function realuser() {
        return $_SESSION['user'];
    }

    // Effective user
    public function user() {
        $user = $this->realuser();
        if ($user == 'admin' && !empty($_SESSION['look'])) {
            $user = $_SESSION['look'];
        }

        if(strpos($user, DIRECTORY_SEPARATOR) !== false) {
            throw new Exception("illegal characthers in username");
        }
        return $user;
    }

    public function userOwnsDomain($domain) {
        return $this->runUser('v-list-web-domain', [$domain, 'json']);
    }

    public function databaseAdd($dbname, $dbuser, $dbpass) {
        $v_password = tempnam("/tmp","hst");
        $fp = fopen($v_password, "w");
        fwrite($fp, $dbpass."\n");
        fclose($fp);
        $status = $this->runUser('v-add-database', [$dbname, $dbuser, $v_password]);
        unlink($v_password);
        return $status;
    }

    public function getWebDomainIp($domain) {
        $this->runUser('v-list-web-domain', [$domain, 'json'], $result);
        $ip = $result->json[$domain]['IP'];
        return filter_var($ip, FILTER_VALIDATE_IP);
    }

    public function getWebDomainPath($domain) {
        return join_paths("/home", $this->user() , "/web", $domain);
    }

    public function downloadUrl($src, $path=null, &$result=null) {
        if (strpos($src,'http://') !== 0 &&
            strpos($src,'https://')!== 0 ) {
            return false;
        }

        exec("/usr/bin/wget --tries 3 -nv " . escapeshellarg($src). " -P " . escapeshellarg(self::TMPDIR_DOWNLOADS) . ' 2>&1', $output, $return_var);
        if ($return_var !== 0) {
            return false;
        }

        if(!preg_match('/URL:\s*(.+?)\s*\[(.+?)\]\s*->\s*"(.+?)"/', implode(PHP_EOL, $output), $matches)) {
            return false;
        }

        if(empty($matches) || count($matches) != 4) {
            return false;
        }

        $status['url'] = $matches[1];
        $status['file'] = $matches[3];
        $result = (object)$status;
        return true;
    }

    public function archiveExtract($src, $path, $skip_components=null) {
        if (file_exists($src)) {
            $archive_file = $src;
        } else  {
            if( !$this->downloadUrl($src, null, $download_result) ) {
                return false;
            }
            $archive_file = $download_result->file;
        }
        $status = $this->runUser('v-extract-fs-archive', [ $archive_file, $path, null, $skip_components]);
        unlink($download_result->file);
        return status;
    }

    public function saveTempFile($data) {
        $tmp_file = tempnam("/tmp","hst");
        chmod($tmp_file, 0644);

        if (file_put_contents($tmp_file, $data) > 0) {
            return $tmp_file;
        }
        return false;
    }
}


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

    public function __construct($app, $domain, $context) {
        $this->domain = $domain;
        $this->appcontext = $context;

        if (!$this->appcontext->userOwnsDomain($domain)) {
            throw new Exception("User does not have access to domain [$domain]");
        }

        $appclass = ucfirst($app).'Setup';
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
