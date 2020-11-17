<?php

$dist_config = require __DIR__.'/configuration_sample.php';

$dist_config['public_path'] = '/fm/';
$dist_config['frontend_config']['app_name'] = 'File Manager - Hestia Control Panel';
$dist_config['frontend_config']['logo'] = '../images/logo.svg';
$dist_config['frontend_config']['editable'] = ['.txt', '.css', '.js', '.ts', '.html', '.php', '.py',
        '.yml', '.xml', '.md', '.log', '.csv', '.conf', '.config', '.ini', '.scss', '.sh', '.env', '.example', '.htaccess'];
$dist_config['frontend_config']['guest_redirection'] = '/login/' ;
$dist_config['frontend_config']['upload_max_size'] = 1024 * 1024 * 1024;

$dist_config['services']['Filegator\Services\Storage\Filesystem']['config']['adapter'] = function () {

        if (isset($_SESSION['user'])) {
            $v_user = $_SESSION['user'];
        }
        if (isset($_SESSION['look']) && $_SESSION['look'] != 'admin' && $v_user === 'admin') {
            $v_user = $_SESSION['look'];
        }
        # Create filemanager sftp key if missing and trash it after 30 min
        if (! file_exists('/home/'.basename($v_user).'/.ssh/hst-filemanager-key')) {
            exec ("sudo /usr/local/hestia/bin/v-add-user-sftp-key " . escapeshellarg(basename($v_user)) . " 30", $output, $return_var);
        }

        if ( !isset($_SESSION['SFTP_PORT']) ) {
            if( preg_match('/^\s*Port\s+(\d+)$/im', file_get_contents('/etc/ssh/sshd_config'), $matches) ) {
                $_SESSION['SFTP_PORT'] = $matches[1] ?? 22;
            } else {
                $_SESSION['SFTP_PORT'] = 22;
            }
        }

        return new \League\Flysystem\Sftp\SftpAdapter([
            'host' => '127.0.0.1',
            'port' => intval($_SESSION['SFTP_PORT']),
            'username' => basename($v_user),
            'privateKey' => '/home/'.basename($v_user).'/.ssh/hst-filemanager-key',
            'root' => '/',
            'timeout' => 10,
            'directoryPerm' => 0755,
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
    'add_to_head' => '
    <style>
        .logo {
            width: 46px;
        }
    </style>
    ',
    'add_to_body' => '
<script>
    var checkVueLoaded = setInterval(function() {
        if (document.getElementsByClassName("navbar-item").length) {
            clearInterval(checkVueLoaded);
            var navProfile = document.getElementsByClassName("navbar-item profile")[0]; navProfile.replaceWith(navProfile.cloneNode(true))
            document.getElementsByClassName("navbar-item logout")[0].text="Exit to Control Panel \u00BB";
        }
    }, 200);
</script>',
];


return $dist_config;
