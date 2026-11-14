<?php

use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Providers\Qr\BaconQrCodeProvider;

require_once __DIR__ . "/../vendor/autoload.php";
// Render the QR code locally (SVG needs no PHP extensions), so the secret is not sent to a third party
$tfa = new TwoFactorAuth(new BaconQrCodeProvider(format: "svg"), "Hestia Control Panel");

$secret = $tfa->createSecret(160); // Though the default is an 80 bits secret (for backwards compatibility reasons) we recommend creating 160+ bits secrets (see RFC 4226 - Algorithm Requirements)
$qrcode = $tfa->getQRCodeImageAsDataUri(gethostname(), $secret);

echo $secret . "-" . $qrcode;
