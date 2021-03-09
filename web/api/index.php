<?php
define('HESTIA_CMD', '/usr/bin/sudo /usr/local/hestia/bin/');

function get_real_user_ip(){
    $ip = $_SERVER['REMOTE_ADDR'];
    if(isset($_SERVER['HTTP_CLIENT_IP'])){
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
        $ip =  $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    if(isset($_SERVER['HTTP_FORWARDED_FOR'])){
        $ip = $_SERVER['HTTP_FORWARDED_FOR'];
    }
    if(isset($_SERVER['HTTP_X_FORWARDED'])){
        $ip = $_SERVER['HTTP_X_FORWARDED'];
    }
    if(isset($_SERVER['HTTP_FORWARDED'])){
        $ip = $_SERVER['HTTP_FORWARDED'];
    }
    if(isset($_SERVER['HTTP_CF_CONNECTING_IP'])){
        if(!empty($_SERVER['HTTP_CF_CONNECTING_IP'])){
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
    }
    return $ip;
}

function api($hst_hash, $hst_user, $hst_password, $hst_returncode, $hst_cmd, $hst_arg1, $hst_arg2, $hst_arg3, $hst_arg4, $hst_arg5, $hst_arg6, $hst_arg7, $hst_arg8, $hst_arg9){
    //This exists, so native JSON can be used without the repeating the code twice, so future code changes are easier and don't need to be replicated twice
    // Authentication
    if (empty($hst_hash)) {
        if ($hst_user != 'admin') {
            echo 'Error: authentication failed';
            exit;
        }

        $password = $hst_password;
        if (!isset($password)){
            echo 'Error: missing authentication';
            exit;
        }
        $v_ip = escapeshellarg(get_real_user_ip());
        $output = '';
        exec (HESTIA_CMD."v-get-user-salt admin ".$v_ip." json" , $output, $return_var);
        $pam = json_decode(implode('', $output), true);
        $salt = $pam['admin']['SALT'];
        $method = $pam['admin']['METHOD'];

        if ($method == 'md5' ) {
            $hash = crypt($password, '$1$'.$salt.'$');
        }
        if ($method == 'sha-512' ) {
            $hash = crypt($password, '$6$rounds=5000$'.$salt.'$');
            $hash = str_replace('$rounds=5000','',$hash);
        }
        if ($method == 'des' ) {
            $hash = crypt($password, $salt);
        }

        // Send hash via tmp file
        $v_hash = exec('mktemp -p /tmp');
        $fp = fopen($v_hash, "w");
        fwrite($fp, $hash."\n");
        fclose($fp);

        // Check user hash
        exec(HESTIA_CMD ."v-check-user-hash admin ".$v_hash." ".$v_ip,  $output, $return_var);
        unset($output);

        // Remove tmp file
        unlink($v_hash);

        // Check API answer
        if ( $return_var > 0 ) {
            echo 'Error: authentication failed';
            exit;
        }
    } else {
        $key = '/usr/local/hestia/data/keys/' . basename($hst_hash);
        $v_ip = escapeshellarg(get_real_user_ip());
        exec(HESTIA_CMD ."v-check-api-key ".escapeshellarg($key)." ".$v_ip,  $output, $return_var);
        unset($output);
        // Check API answer
        if ( $return_var > 0 ) {
            echo 'Error: authentication failed';
            exit;
        }
    }

    // Prepare arguments
    if (isset($hst_cmd)) $cmd = escapeshellarg($hst_cmd);
    if (isset($hst_arg1)) $arg1 = escapeshellarg($hst_arg1);
    if (isset($hst_arg2)) $arg2 = escapeshellarg($hst_arg2);
    if (isset($hst_arg3)) $arg3 = escapeshellarg($hst_arg3);
    if (isset($hst_arg4)) $arg4 = escapeshellarg($hst_arg4);
    if (isset($hst_arg5)) $arg5 = escapeshellarg($hst_arg5);
    if (isset($hst_arg6)) $arg6 = escapeshellarg($hst_arg6);
    if (isset($hst_arg7)) $arg7 = escapeshellarg($hst_arg7);
    if (isset($hst_arg8)) $arg8 = escapeshellarg($hst_arg8);
    if (isset($hst_arg9)) $arg9 = escapeshellarg($hst_arg9);
    // Build query
    $cmdquery = HESTIA_CMD.$cmd." ";
    if(!empty($arg1)){
         $cmdquery = $cmdquery.$arg1." "; }
    if(!empty($arg2)){
         $cmdquery = $cmdquery.$arg2." "; }
    if(!empty($arg3)){
         $cmdquery = $cmdquery.$arg3." "; }
    if(!empty($arg4)){
         $cmdquery = $cmdquery.$arg4." "; }
    if(!empty($arg5)){
         $cmdquery = $cmdquery.$arg5." "; }
    if(!empty($arg6)){
         $cmdquery = $cmdquery.$arg6." "; }
    if(!empty($arg7)){
         $cmdquery = $cmdquery.$arg7." "; }
    if(!empty($arg8)){
         $cmdquery = $cmdquery.$arg8." "; }
    if(!empty($arg9)){
         $cmdquery = $cmdquery.$arg9; }

    // Check command
    if ($cmd == "'v-make-tmp-file'") {
        // Used in DNS Cluster
        $fp = fopen('/tmp/'.basename($hst_arg2), 'w');
        fwrite($fp, $hst_arg1."\n");
        fclose($fp);
        $return_var = 0;
    } else {
        // Run normal cmd query
        exec ($cmdquery, $output, $return_var);
    }

    if ((!empty($hst_returncode)) && ($hst_returncode == 'yes')) {
        echo $return_var;
    } else {
        if (($return_var == 0) && (empty($output))) {
            echo "OK";
        } else {
            echo implode("\n",$output)."\n";
        }
    }
}

if (isset($_POST['user']) || isset($_POST['hash'])) {

    api($_POST['hash'], $_POST['user'], $_POST['password'], $_POST['returncode'], $_POST['cmd'], $_POST['arg1'], $_POST['arg2'], $_POST['arg3'], $_POST['arg4'], $_POST['arg5'], $_POST['arg6'], $_POST['arg7'], $_POST['arg8'], $_POST['arg9']);

} else if (json_decode(file_get_contents("php://input"), true) != NULL){ //JSON POST support
    $json_data = json_decode(file_get_contents("php://input"), true);
    api($json_data['hash'], $json_data['user'], $json_data['password'], $json_data['returncode'], $json_data['cmd'], $json_data['arg1'], $json_data['arg2'], $json_data['arg3'], $json_data['arg4'], $json_data['arg5'], $json_data['arg6'], $json_data['arg7'], $json_data['arg8'], $json_data['arg9']);

} else {
    echo "Error: data received is null or invalid, check https://docs.hestiacp.com/admin_docs/rest_api.html";
    exit;
}

?>
