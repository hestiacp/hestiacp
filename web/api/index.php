<?php
define('VESTA_CMD', '/usr/bin/sudo /usr/local/vesta/bin/');

if (isset($_POST['user']) || isset($_POST['hash'])) {

    // Authentication
    $auth_code = 1;
    if (empty($_POST['hash'])) {
        $v_user = escapeshellarg($_POST['user']);
        $v_password = escapeshellarg($_POST['password']);
        exec(VESTA_CMD ."v-check-user-password ".$v_user." ".$v_password." '".$_SERVER["REMOTE_ADDR"]."'",  $output, $auth_code);
    } else {
        $key = '/usr/local/vesta/data/keys/' . basename($_POST['hash']);
        if (file_exists($key)) {
            $auth_code = '0';
        }
    }

    if ($auth_code != 0 ) {
        echo 'Error: authentication failed';
        exit;
    }

    // Check user permission to use API
    if ($_POST['user'] != 'admin') {
        echo 'Error: only admin is allowed to use API';
        exit;
    }

    // Prepare arguments
    $cmd = escapeshellarg($_POST['cmd']);
    $arg1 = escapeshellarg($_POST['arg1']);
    $arg2 = escapeshellarg($_POST['arg2']);
    $arg3 = escapeshellarg($_POST['arg3']);
    $arg4 = escapeshellarg($_POST['arg4']);
    $arg5 = escapeshellarg($_POST['arg5']);
    $arg6 = escapeshellarg($_POST['arg6']);
    $arg7 = escapeshellarg($_POST['arg7']);
    $arg8 = escapeshellarg($_POST['arg8']);
    $arg9 = escapeshellarg($_POST['arg9']);

    // Run query
    exec (VESTA_CMD.$cmd." ".$arg1." ".$arg2." ".$arg3." ".$arg4." ".$arg5." ".$arg6." ".$arg7." ".$arg8." ".$arg9, $output, $return_var);
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

?>
