#!/usr/local/vesta/php/bin/php
<?php
error_reporting(NULL);
if (empty($argv[1])) {
    echo "ERROR: not enough arguments\n";
    echo "USAGE: mail-wrapper.php -s SUBJECT EMAIL [NOTIFY]\n";
    exit(3);
}

$options = getopt("s:f:");

if ((!empty($argv[4])) && ($argv[4] == 'no')) {
     exit;
}

define('NO_AUTH_REQUIRED',true);

include("/usr/local/vesta/web/inc/main.php");

// Set system language
exec (VESTA_CMD . "v-list-sys-config json", $output, $return_var);
$data = json_decode(implode('', $output), true);
if (!empty( $data['config']['LANGUAGE'])) {
    $_SESSION['language'] = $data['config']['LANGUAGE'];
} else {
    $_SESSION['language'] = 'en';
}
require_once('/usr/local/vesta/web/inc/i18n/'.$_SESSION['language'].'.php');

// Define vars
$from = 'Vesta Control Panel <vesta@'.gethostname().'>';
$to = $argv[3]."\n";
$subject = $argv[2]."\n";
$mailtext = file_get_contents("php://stdin");

// Send email
if ((!empty($to)) && (!empty($subject))) {
    send_email($to,$subject,$mailtext,$from);
}
