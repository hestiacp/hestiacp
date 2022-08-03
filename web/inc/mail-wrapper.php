#!/usr/local/hestia/php/bin/php
<?php
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

include("/usr/local/hestia/web/inc/main.php");

// Set system language
exec (HESTIA_CMD . "v-list-sys-config json", $output, $return_var);
$data = json_decode(implode('', $output), true);
if (!empty( $data['config']['LANGUAGE'])) {
    $_SESSION['language'] = $data['config']['LANGUAGE'];
} else {
    $_SESSION['language'] = 'en';
}

//define vars 
//make hostname detection a bit more feature proof
$hostname = (function():string{
    $badValues = array(false, null, 0, '', "localhost", "127.0.0.1", "::1", "0000:0000:0000:0000:0000:0000:0000:0001");
    $ret = gethostname();
    if(in_array($ret, $badValues, true)){
        throw new Exception('gethostname() failed');
    }
    $ret2 = gethostbyname($ret);
    if(in_array($ret2, $badValues, true)){
        return $ret;
    }
    $ret3 = gethostbyaddr($ret2);
    if(in_array($ret3, $badValues, true)){
        return $ret2;
    }
    return $ret3;
})();

$from = 'noreply@'.$hostname;
$from_name = _('Hestia Control Panel');
$to = $argv[3]."\n";
$subject = $argv[2]."\n";
$mailtext = file_get_contents("php://stdin");

// Send email
if ((!empty($to)) && (!empty($subject))) {
    send_email($to,$subject,$mailtext,$from, $from_name);
}
