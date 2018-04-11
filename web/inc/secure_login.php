<?php
if (!defined('NO_AUTH_REQUIRED2')) {
    if (file_exists('/usr/local/vesta/web/inc/login_url.php')) {
        require_once('/usr/local/vesta/web/inc/login_url.php');
        if (isset($_GET[$login_url])) {
            setcookie($login_url, '1', time() + 31536000, '/', $_SERVER['HTTP_HOST'], true);
            header ("Location: /login/");
            exit;
        }
        if (!isset($_COOKIE[$login_url])) exit;
    }
}
