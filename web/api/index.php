<?php
define('VESTA_CMD', '/usr/bin/sudo /usr/local/vesta/bin/');
exit;
if (isset($_POST['user']) || isset($_POST['hash'])) {

    // Authentication
    if (empty($_POST['hash'])) {
        if ($_POST['user'] != 'admin') {
            echo 'Error: authentication failed';
            exit;
        }

        $password = $_POST['password'];
        $v_ip = escapeshellarg($_SERVER['REMOTE_ADDR']);
        $output = '';
        exec (VESTA_CMD."v-get-user-salt admin ".$v_ip." json" , $output, $return_var);
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
        exec(VESTA_CMD ."v-check-user-hash admin ".$v_hash." ".$v_ip,  $output, $return_var);
        unset($output);

        // Remove tmp file
        unlink($v_hash);

        // Check API answer
        if ( $return_var > 0 ) {
            echo 'Error: authentication failed';
            exit;
        }
    } else {
        $key = '/usr/local/vesta/data/keys/' . basename($_POST['hash']);
        if (file_exists($key) && is_file($key)) {
            exec(VESTA_CMD ."v-check-api-key ".escapeshellarg($key)." ".$v_ip,  $output, $return_var);
            unset($output);

            // Check API answer
            if ( $return_var > 0 ) {
                echo 'Error: authentication failed';
                exit;
            }
        } else {
            $return_var = 1;
        }
    }

    if ( $return_var > 0 ) {
        echo 'Error: authentication failed';
        exit;
    }

    // Prepare arguments
    if (isset($_POST['cmd'])) $cmd = escapeshellarg($_POST['cmd']);
    if (isset($_POST['arg1'])) $arg1 = escapeshellarg($_POST['arg1']);
    if (isset($_POST['arg2'])) $arg2 = escapeshellarg($_POST['arg2']);
    if (isset($_POST['arg3'])) $arg3 = escapeshellarg($_POST['arg3']);
    if (isset($_POST['arg4'])) $arg4 = escapeshellarg($_POST['arg4']);
    if (isset($_POST['arg5'])) $arg5 = escapeshellarg($_POST['arg5']);
    if (isset($_POST['arg6'])) $arg6 = escapeshellarg($_POST['arg6']);
    if (isset($_POST['arg7'])) $arg7 = escapeshellarg($_POST['arg7']);
    if (isset($_POST['arg8'])) $arg8 = escapeshellarg($_POST['arg8']);
    if (isset($_POST['arg9'])) $arg9 = escapeshellarg($_POST['arg9']);

    // Build query
    $cmdquery = VESTA_CMD.$cmd." ";
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
        $fp = fopen($_POST['arg2'], 'w');
        fwrite($fp, $_POST['arg1']."\n");
        fclose($fp);
        $return_var = 0;
    } else {
        // Run normal cmd query
        exec ($cmdquery, $output, $return_var);
    }

    if ((!empty($_POST['returncode'])) && ($_POST['returncode'] == 'yes')) {
        echo $return_var;
    } else {
        if (($return_var == 0) && (empty($output))) {
            echo "OK";
        } else {
            echo implode("\n",$output)."\n";
        }
    }
}
