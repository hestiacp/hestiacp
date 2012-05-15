<?php
session_start();

if (isset($_GET['logout'])) {
    session_destroy();
}

if (isset($_SESSION['user'])) {
    if ($_SESSION['user'] ==  'admin' && !empty($_GET['loginas'])) {
        if ($_GET['loginas'] == 'admin') {
            unset($_SESSION['look']);
        } else {
            $_SESSION['look'] = $_GET['loginas'];
        }
    }
    
    header("Location: /");
} else {
    if (isset($_POST['user']) && isset($_POST['password'])) {
        $cmd="/usr/bin/sudo /usr/local/vesta/bin/";
        $command="$cmd"."v_check_user_password '".$_POST['user']."' '".$_POST['password']."' '".$_SERVER["REMOTE_ADDR"]."'";
        exec ($command, $output, $return_var);
        if ( $return_var > 0 ) {
            $ERROR = "<a class=\"error\">ERROR: Invalid username or password</a>";
        } else {
            $_SESSION['user'] = $_POST['user'];
            header("Location: /");
        }
    }
    require_once '../templates/login.html';
}
?>
