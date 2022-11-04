<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

$dist_config = require __DIR__.'/configuration_sample.php';

$dist_config['public_path'] = '/fm/';
$dist_config['frontend_config']['app_name'] = 'File Manager - Hestia Control Panel';
$dist_config['frontend_config']['logo'] = '../images/logo.svg';
$dist_config['frontend_config']['editable'] = ['.txt', '.css', '.js', '.ts', '.html', '.php', '.py',
        '.yml', '.xml', '.md', '.log', '.csv', '.conf', '.config', '.ini', '.scss', '.sh', '.env', '.example', '.htaccess', '.twig'];
$dist_config['frontend_config']['guest_redirection'] = '/login/' ;
$dist_config['frontend_config']['upload_max_size'] = 1024 * 1024 * 1024;

$dist_config['services']['Filegator\Services\Storage\Filesystem']['config']['adapter'] = function () {
    if (!empty($_SESSION['INACTIVE_SESSION_TIMEOUT'])){
    if ($_SESSION['INACTIVE_SESSION_TIMEOUT'] * 60 + $_SESSION['LAST_ACTIVITY'] < time()) {
        $v_user = quoteshellarg($_SESSION['user']);
        $v_session_id = quoteshellarg($_SESSION['token']);
        exec('/usr/local/hestia/bin/v-log-user-logout ' . $v_user . ' ' . $v_session_id, $output, $return_var);
        unset($_SESSION);
        session_unset();
        session_destroy();
        session_start();
        echo '<meta http-equiv="refresh" content="0; url=/">';
        exit;
    } else {
        $_SESSION['LAST_ACTIVITY'] = time();
    }
    }else{
        echo '<meta http-equiv="refresh" content="0; url=/">';
    }
    if (isset($_SESSION['user'])) {
        $v_user = $_SESSION['user'];
    }
    if (!empty($_SESSION['look'])) {
        if (isset($_SESSION['look']) && ($_SESSION['userContext'] === 'admin')) {
            $v_user = $_SESSION['look'];
        }
        if ((isset($_SESSION['look']) && ($_SESSION['look'] == 'admin') && ($_SESSION['POLICY_SYSTEM_PROTECTED_ADMIN'] == 'yes'))) {
            header('Location: /');
        }
    }
    # Create filemanager sftp key if missing and trash it after 30 min
    if (! file_exists('/home/'.basename($v_user).'/.ssh/hst-filemanager-key')) {
        exec("sudo /usr/local/hestia/bin/v-add-user-sftp-key " . quoteshellarg(basename($v_user)) . " 30", $output, $return_var);
        // filemanager also requires .ssh chmod o+x ... hopefully we can improve it to g+x or u+x someday
        // current minimum for filemanager: chmod 0701 .ssh
        shell_exec("sudo chmod o+x " . quoteshellarg('/home/' . basename($v_user) . '/.ssh'));
    }

    if (!isset($_SESSION['SFTP_PORT'])) {
        exec("sudo /usr/local/hestia/bin/v-list-sys-sshd-port json", $output, $result);
        $port=json_decode(implode('', $output));
        if (is_numeric($port[0]) && $port[0] > 0) {
            $_SESSION['SFTP_PORT'] = $port[0];
        } elseif (preg_match('/^\s*Port\s+(\d+)$/im', file_get_contents('/etc/ssh/sshd_config'), $matches)) {
            $_SESSION['SFTP_PORT'] = $matches[1] ?? 22;
        } else {
            $_SESSION['SFTP_PORT'] = 22;
        }
    }

    preg_match('/(Hestia SFTP Chroot\nMatch User)(.*)/i', file_get_contents('/etc/ssh/sshd_config'), $matches);
    $user_list = explode(',', $matches[2]);
    if (in_array($v_user, $user_list)) {
        $root = '/';
    } else {
        $root = '/home/'.$v_user;
    }

    return new \League\Flysystem\Sftp\SftpAdapter([
            'host' => '127.0.0.1',
            'port' => intval($_SESSION['SFTP_PORT']),
            'username' => basename($v_user),
            'privateKey' => '/home/'.basename($v_user).'/.ssh/hst-filemanager-key',
            'root' => $root,
            'timeout' => 10,
            'directoryPerm' => 0755,
        ]);
};

$dist_config['services']['Filegator\Services\Archiver\ArchiverInterface'] = [
    'handler' => '\Filegator\Services\Archiver\Adapters\HestiaZipArchiver',
    'config' => [],
];

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
        if (document.getElementsByClassName("container").length) {
            clearInterval(checkVueLoaded);
            var navProfile = document.getElementsByClassName("navbar-item profile")[0]; navProfile.replaceWith(navProfile.cloneNode(true))
            document.getElementsByClassName("navbar-item logout")[0].text="Exit to Control Panel \u00BB";
            div = document.getElementsByClassName("container")[0];
            callback = function(){
                if (document.getElementsByClassName("navbar-item logout")[0]){
                    if ( document.getElementsByClassName("navbar-item logout")[0].text != "Exit to Control Panel \u00BB" ){
                        var navProfile = document.getElementsByClassName("navbar-item profile")[0]; navProfile.replaceWith(navProfile.cloneNode(true))
                        document.getElementsByClassName("navbar-item logout")[0].text="Exit to Control Panel \u00BB";
                    }
                }
            }
            config = {
                childList:true,
                subtree:true
            }
            observer = new MutationObserver(callback);
            observer.observe(div,config);
        }
    }, 200);
</script>',
];


return $dist_config;
