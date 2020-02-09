<?php

namespace Hestia\WebApp\Installers;

use Hestia\System\Util;
use Hestia\System\HestiaApp;
use Hestia\WebApp\InstallerInterface;
use Hestia\Models\WebDomain;

use Hestia\WebApp\Installers\Resources\ComposerResource;


abstract class BaseSetup implements InstallerInterface {

    protected $domain;
    protected $extractsubdir;

    public function __construct($domain, HestiaApp $appcontext)
    {
        if(filter_var($domain, FILTER_VALIDATE_DOMAIN) === false) {
            throw new \Exception("Invalid domain name");
        }

        $this->domain = $domain;
        $this->appcontext = $appcontext;
    }

    public function getConfig($section=null)
    {
        return (!empty($section))? $this->config[$section] : $this->config;
    }

    public function getOptions()
    {
        return $this->getConfig('form');
    }

    public function withDatabase() : bool
    {
        return ($this->getConfig('database') === true);
    }

    public function getDocRoot($append_relative_path=null) : string
    {
        $domain_path = $this->appcontext->getWebDomainPath($this->domain);
        if(empty($domain_path) || ! is_dir($domain_path)) {
            throw new \Exception("Error finding domain folder ($domain_path)");
        }
        return Util::join_paths($domain_path, "public_html", $append_relative_path);
    }

    public function retrieveResources($options)
    {
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

    public function install(array $options=null)
    {
        $this->appcontext->runUser('v-delete-fs-file', [$this->getDocRoot('robots.txt')]);
        $this->appcontext->runUser('v-delete-fs-file', [$this->getDocRoot('index.html')]);
        return $this->retrieveResources($options);
    }

    public function cleanup()
    {
        // Remove temporary folder
        if(!empty($this->extractsubdir)) {
            $this->appcontext->runUser('v-delete-fs-directory',[$this->getDocRoot($this->extractsubdir)], $result);
        }
    }

    public function saveTempFile(string $data)
    {
        $tmp_file = tempnam("/tmp", "hst.");
        if(empty($tmp_file)) {
            throw new \Exception("Error creating temp file");
        }

        if (file_put_contents($tmp_file, $data) > 0) {
            chmod($tmp_file, 0644);
            $user_tmp_file = Util::join_paths($this->appcontext->getUserHomeDir(), $tmp_file );
            $this->appcontext->runUser('v-copy-fs-file',[$tmp_file, $user_tmp_file], $result);
            unlink($tmp_file);
            return $user_tmp_file;
        }

        if(file_exists($tmp_file)) {
            unlink($tmp_file);
        }
        return false;
    }


}