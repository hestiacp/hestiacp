<?php

namespace Hestia\WebApp\Installers;

class SymfonySetup extends BaseSetup {

    protected $appname = 'symfony';

    protected $config = [
        'form' => [
            'protocol' => [
                'type' => 'select',
                'options' => ['http','https'],
            ],
        ],
        'database' => true,
        'resources' => [
            'composer' => [ 'src' => 'symfony/website-skeleton', 'dst' => '/' ],
        ],
    ];

}
