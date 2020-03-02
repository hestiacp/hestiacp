<?php

namespace Hestia\WebApp\Installers;

class SymfonySetup extends BaseSetup {

    protected $appname = 'symfony';

    protected $config = [
        'form' => [
        ],
        'database' => true,
        'resources' => [
            'composer' => [ 'src' => 'symfony/website-skeleton', 'dst' => '/' ],
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

        $this->appcontext->runComposer(["config",  "-d " . $this->getDocRoot(), "extra.symfony.allow-contrib", "true"], $result);
        $this->appcontext->runComposer(["require", "-d " . $this->getDocRoot(), "symfony/apache-pack"], $result);

        $tmp_configpath = $this->saveTempFile($htaccess_rewrite);
        $this->appcontext->runUser('v-move-fs-file',[$tmp_configpath, $this->getDocRoot(".htaccess")], $result);

        return ($result->code === 0);
    }
}
