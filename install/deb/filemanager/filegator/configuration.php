<?php

$dist_config = require __DIR__.'/configuration_sample.php';

$dist_config['frontend_config']['app_name'] = 'Hestia FM';
$dist_config['frontend_config']['logo'] = 'https://raw.githubusercontent.com/filegator/filegator/master/dist/img/logo.png';
$dist_config['frontend_config']['editable'] = ['.txt', '.css', '.js', '.ts', '.html', '.php', '.py' ];
$dist_config['frontend_config']['guest_redirection'] = '/login/' ;

$dist_config['services']['Filegator\Services\Storage\Filesystem']['config']['adapter'] = function () {

        if (isset($_SESSION['user'])) {
            $v_user = $_SESSION['user'];
        }
        if (isset($_SESSION['look']) && $_SESSION['look'] != 'admin' && $v_user === 'admin') {
            $v_user = $_SESSION['look'];
        }

        return new \League\Flysystem\Sftp\SftpAdapter([
            'host' => '127.0.0.1',
            'port' => 22,
            'username' => basename($v_user),
            'privateKey' => '/home/'.basename($v_user).'/.ssh/hst-filemanager-key',
            'root' => '/',
            'timeout' => 10,
        ]);
    };

$dist_config['services']['Filegator\Services\Auth\AuthInterface'] = [
        'handler' => '\Filegator\Services\Auth\Adapters\HestiaAuth',
        'config' => [
            'permissions' => ['read', 'write', 'upload', 'download', 'batchdownload', 'zip'],
            'private_repos' => false,
        ],
    ];

$dist_config['services']['Filegator\Services\View\ViewInterface']['config'] = [
        'add_to_head' => '',
        'add_to_body' => '',
];


return $dist_config;
