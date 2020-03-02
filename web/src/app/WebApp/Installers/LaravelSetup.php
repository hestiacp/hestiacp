<?php

namespace Hestia\WebApp\Installers;

class LaravelSetup extends BaseSetup {

    protected $appname = 'laravel';

    protected $config = [
        'form' => [
        ],
        'database' => true,
        'resources' => [
            'composer' => [ 'src' => 'laravel/laravel', 'dst' => '/' ],
        ],
    ];

    public function install(array $options=null) : bool
    {
        parent::install($options);
        $result = null;

        $htaccess_rewrite = '
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>';

        $tmp_configpath = $this->saveTempFile($htaccess_rewrite);
        $this->appcontext->runUser('v-move-fs-file',[$tmp_configpath, $this->getDocRoot(".htaccess")], $result);

        return ($result->code === 0);
    }
}
