<?php

require_once '/usr/local/hestia/web/inc/2fa/loader.php';
Loader::register('./','RobThree\\Auth');

use \RobThree\Auth\TwoFactorAuth;

$tfa = new TwoFactorAuth('Hestia Control Panel');

$secret = $tfa->createSecret(160);  // Though the default is an 80 bits secret (for backwards compatibility reasons) we recommend creating 160+ bits secrets (see RFC 4226 - Algorithm Requirements)
$qrcode = $tfa->getQRCodeImageAsDataUri(gethostname(), $secret);

echo $secret . "-" . $qrcode;