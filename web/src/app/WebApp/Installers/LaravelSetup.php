<?php

namespace Hestia\WebApp\Installers;

class LaravelSetup extends BaseSetup {

    protected $appname = 'laravel';

    protected $config = [
        'form' => [
            'protocol' => [
                'type' => 'select',
                'options' => ['http','https'],
            ],
        ],
        'database' => true,
        'resources' => [
            'composer' => [ 'src' => 'laravel/laravel', 'dst' => '/' ],
        ],
    ];

}
