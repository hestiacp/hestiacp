<?php
require_once("BaseSetup.php");

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
