<?php

if (!file_exists('/usr/local/vesta/web/inc/login_url.php')) {
    session_start();
    if (isset($_SESSION['user'])) {
        header("Location: /list/user/");
    } else {
        header("Location: /login/");
    }
} else {
    require_once('/usr/local/vesta/web/inc/login_url.php');
    if (isset($_GET[$login_url])) {
      require_once('/usr/local/vesta/web/inc/secure_login.php');
    }
    header("Location: /webmail/");
}
