<?php

require_once("Resources.php");

function join_paths() {
    $paths = array();

    foreach (func_get_args() as $arg) {
        if ($arg !== '') { $paths[] = $arg; }
    }

    return preg_replace('#/+#','/',join('/', $paths));
}

function generate_string(int $length = 16) {
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ~`!@|#[]$%^&*() _-=+{}:;<>?,./';
    $random_string = '';
    for($i = 0; $i < $length; $i++) {
        $random_string .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $random_string;
}

abstract class BaseSetup {

    protected $domain;
    protected $extractsubdir;

    public function __construct($domain, HestiaApp $appcontext) {

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

    public function withDatabase() : bool {
        return ($this->getConfig('database') === true);
    }

    public function getDocRoot($docrelative=null) : string {
        $domain_path = $this->appcontext->getWebDomainPath($this->domain);
        if(empty($domain_path) || ! is_dir($domain_path)) {
            throw new Exception("Error finding domain folder ($domain_path)");
        }

        return join_paths($domain_path, "public_html", $docrelative);
    }

    public function retrieveResources() {

        foreach ($this->getConfig('resources') as $res_type => $res_data) {

            if (!empty($res_data['dst']) && is_string($res_data['dst'])) {
                $resource_destination = $this->getDocRoot($res_data['dst']);
            } else {
                $resource_destination = $this->getDocRoot($this->extractsubdir);
            }

            if ($res_type === 'composer') {
                new ComposerResource($this->appcontext, $res_data, $resource_destination);
            } else {
                $this->appcontext->archiveExtract($res_data['src'], $resource_destination, 1); 
            }
        }
        return true;
    }

    public function install($options) {
        return $this->retrieveResources();
    }

    public function cleanup() {

        // Remove temporary folder
        if(!empty($this->extractsubdir)) {
            $this->appcontext->runUser('v-delete-fs-directory',[$this->getDocRoot($this->extractsubdir)], $result);
        }
        
    }
}