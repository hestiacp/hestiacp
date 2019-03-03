<?php

if (isset($argv[1]) && isset($argv[2])) {
    $secret = $argv[1];
    $token = $argv[2];
} elseif (isSet($_GET['secret']) && isSet($_GET['token'])) {
    $secret = htmlspecialchars($_GET['secret']);
    $token = htmlspecialchars($_GET['token']);
} else {
    echo 'ERROR: Secret or Token is not set as argument!';
    exit;
}


require_once '/usr/local/hestia/web/inc/2fa/loader.php';
Loader::register('./','RobThree\\Auth');

use \RobThree\Auth\TwoFactorAuth;

$tfa = new TwoFactorAuth('Hestia Control Panel');

// Verify code
$result = $tfa->verifyCode($secret, $token);

if ($result){
    echo "ok";
}