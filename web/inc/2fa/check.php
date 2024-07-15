<?php
use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Providers\Qr\QRServerProvider;
require_once __DIR__ . "/../vendor/autoload.php";

if (isset($argv[1]) && isset($argv[2])) {
	$secret = $argv[1];
	$token = $argv[2];
} elseif (isset($_GET["secret"]) && isset($_GET["token"])) {
	$secret = htmlspecialchars($_GET["secret"]);
	$token = htmlspecialchars($_GET["token"]);
} else {
	echo "ERROR: Secret or Token is not set as argument!";
	exit();
}

$tfa = new TwoFactorAuth(new QRServerProvider(), "Hestia Control Panel");

// Verify code
$result = $tfa->verifyCode($secret, $token);

if ($result) {
	echo "ok";
}
