<?php
require_once('/usr/local/vesta/web/login_url.php');
if (isset($_GET[$login_url])) {
    setcookie($login_url, '1', time() + 31536000, '/', $_SERVER['HTTP_HOST'], true);
    header ("Location: /");
    exit;
}
if (!isset($_COOKIE[$login_url])) exit;
