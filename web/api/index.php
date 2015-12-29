<?php
define('VESTA_CMD', '/usr/bin/sudo /usr/local/vesta/bin/');

if (isset($_POST['user']) || isset($_POST['hash'])) {

    // Authentication
    $auth_code = 1;
    if (empty($_POST['hash'])) {
        // Check user permission to use API
        if ($_POST['user'] != 'admin') {
            echo 'Error: only admin is allowed to use API';
            exit;
        }

        $v_user = escapeshellarg($_POST['user']);
        $v_password = tempnam("/tmp","vst");
        $fp = fopen($v_password, "w");
        fwrite($fp, $_POST['password']."\n");
        fclose($fp);
        $v_ip_addr = escapeshellarg($_SERVER["REMOTE_ADDR"]);
        exec(VESTA_CMD ."v-check-user-password ".$v_user." ".$v_password." '".$v_ip_addr."'",  $output, $auth_code);
        unlink($v_password);
    } else {
        $key = '/usr/local/vesta/data/keys/' . basename($_POST['hash']);
        if (file_exists($key) && is_file($key)) {
            $auth_code = '0';
        }
    }

    if ($auth_code != 0 ) {
        echo 'Error: authentication failed';
        exit;
    }
    
    // Define the command to use
    if (isset($_POST['cmd']))
    {
        $cmd = escapeshellarg($_POST['cmd']);
    } else
    {
        // If there's no command, just exit.
        echo 'No command specified.';
        exit;
    }
    
    // Prepare for iteration
    $args = [];
    $i = 0;
    
    // Loop through args until there isn't another.
    while (true)
    {
        $i++;
        if (!empty($_POST['arg' . $i]))
        {
            $args[] = escapeshellarg($_POST['arg' . $i]);
            continue;
        }
        break;
    }

    // Build query
    $cmdquery = VESTA_CMD . $cmd . " " . implode(" ", $args);

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
