<?php
// Set timezone
date_default_timezone_set('UTC');

// Check user session
if (!isset($_SESSION['user'])) {
    $_SESSION['request_uri'] = $_SERVER['REQUEST_URI'];
    header("Location: /login/");
    exit;
}

if (isset($_SESSION['look']) && ( $_SESSION['look'] != 'admin' )) {
    $user = $_SESSION['look'];
} else {
    $user = $_SESSION['user'];
}

define('VESTA_CMD', '/usr/bin/sudo /usr/local/vesta/bin/');

$i = 0;

// Define functions
function check_error($return_var){
    if ( $return_var > 0 ) {
        header("Location: /error/");
        exit;
    }
}

function top_panel($user, $TAB) {
    global $panel;
    $command = VESTA_CMD."v_list_user '".$user."' 'json'";
    exec ($command, $output, $return_var);
    if ( $return_var > 0 ) {
        header("Location: /error/");
    }
    $panel = json_decode(implode('', $output), true);
    unset($output);
    if ( $user == 'admin' ) {
        include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/panel.html');
    } else {
        include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/panel.html');
    }
}

function humanize_time($usage) {
    if ( $usage > 60 ) {
        $usage = $usage / 60;
        $usage = number_format($usage, 2);
        $usage = $usage." Hour.";
    } else {
        $usage = $usage." Min.";
    }
    return $usage;
}

function humanize_usage($usage) {
    if ( $usage > 1000 ) {
        $usage = $usage / 1000;
        if ( $usage > 1000 ) {
                $usage = $usage / 1000 ;
                if ( $usage > 1000 ) {
                    $usage = $usage / 1000 ;
                    $usage = number_format($usage, 2);
                    $usage = $usage." PB";
                } else {
                    $usage = number_format($usage, 2);
                    $usage = $usage." TB";
                }
        } else {
            $usage = number_format($usage, 2);
            $usage = $usage." GB";
        }
    } else {
        $usage = $usage." MB";
    }
    return $usage;
}

function get_percentage($used,$total) {
    if (!isset($total)) $total =  0;
    if (!isset($used)) $used =  0;
    if ( $total == 0 ) {
        $percent = 0;
    } else {
        $percent = $used / $total;
        $percent = $percent * 100;
        $percent = number_format($percent, 0, '', '');
        if ( $percent > 100 ) {
            $percent = 100;
        }
        if ( $percent < 0 ) {
            $percent = 0;
        }

    }
    return $percent;
}

?>
