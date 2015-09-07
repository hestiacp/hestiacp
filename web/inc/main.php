<?php

// Check system settings
if ((!isset($_SESSION['VERSION'])) && (!defined('NO_AUTH_REQUIRED'))) {
    session_destroy();
    $_SESSION['request_uri'] = $_SERVER['REQUEST_URI'];
    header("Location: /login/");
    exit;
}

// Check user session
if ((!isset($_SESSION['user'])) && (!defined('NO_AUTH_REQUIRED'))) {
    $_SESSION['request_uri'] = $_SERVER['REQUEST_URI'];
    header("Location: /login/");
    exit;

}

if (isset($_SESSION['user'])) {
    require_once($_SERVER['DOCUMENT_ROOT'].'/inc/i18n/'.$_SESSION['language'].'.php');
    if(!isset($_SESSION['token'])){
        $token = uniqid(mt_rand(), true);
        $_SESSION['token'] = $token;
    }
}


/**
 * Translates string by a given key in first parameter to current session language. Works like sprintf
 * @global array $LANG Associative array of language pharses
 * @return string Translated string
 * @see _translate()
 */
function __() {
   $args = func_get_args();
   array_unshift($args,$_SESSION['language']);
   return call_user_func_array("_translate",$args);
}

/**
 * Translates string to given language in first parameter, key given in second parameter (dynamically loads required language). Works like spritf from second parameter
 * @global array $LANG Associative array of language pharses
 * @return string Translated string
 */
function _translate() {
    global $LANG;

    $args = func_get_args();
    $l = $args[0];

    if (!$l) return 'NO LANGUAGE DEFINED';
    $key = $args[1];

    if (!isset($LANG[$l])) {
        require_once($_SERVER['DOCUMENT_ROOT'].'/inc/i18n/'.$l.'.php');
    }

    if (!isset($LANG[$l][$key])) {
        $text=$key;
    } else {
        $text=$LANG[$l][$key];
    }

    array_shift($args);
    if (count($args)>1) {
        $args[0] = $text;
        return call_user_func_array("sprintf",$args);
    } else {
        return $text;
    }
}

define('VESTA_CMD', '/usr/bin/sudo /usr/local/vesta/bin/');

$i = 0;

if (isset($_SESSION['language'])) {
    switch ($_SESSION['language']) {
        case 'ro':
            setlocale(LC_ALL, 'ro_RO.utf8');
            break;
        case 'ru':
            setlocale(LC_ALL, 'ru_RU.utf8');
            break;
        case 'ua':
            setlocale(LC_ALL, 'uk_UA.utf8');
            break;
        case 'es':
            setlocale(LC_ALL, 'es_ES.utf8');
            break;
        default:
            setlocale(LC_ALL, 'en_US.utf8');
    }
}

if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
}

if (isset($_SESSION['look']) && ( $_SESSION['look'] != 'admin' )) {
    $user = $_SESSION['look'];
}


function check_error($return_var) {
    if ( $return_var > 0 ) {
        header("Location: /error/");
        exit;
    }
}

function check_return_code($return_var,$output) {
   if ($return_var != 0) {
        $error = implode('<br>', $output);
        if (empty($error)) $error = __('Error code:',$return_var);
        $_SESSION['error_msg'] = $error;
    }
}

function top_panel($user, $TAB) {
    global $panel;
    $command = VESTA_CMD."v-list-user '".$user."' 'json'";
    exec ($command, $output, $return_var);
    if ( $return_var > 0 ) {
        header("Location: /error/");
        exit;
    }
    $panel = json_decode(implode('', $output), true);
    unset($output);
    if ( $user == 'admin' ) {
        include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/panel.html');
    } else {
        include($_SERVER['DOCUMENT_ROOT'].'/templates/user/panel.html');
    }
}

function translate_date($date){
  $date = strtotime($date);
  return strftime("%d &nbsp;", $date).__(strftime("%b", $date)).strftime(" &nbsp;%Y", $date);
}

function humanize_time($usage) {
    if ( $usage > 60 ) {
        $usage = $usage / 60;
        if ( $usage > 24 ) {
             $usage = $usage / 24;
            $usage = number_format($usage);
            if ( $usage == 1 ) {
                $usage = $usage." ".__('day');
            } else {
                $usage = $usage." ".__('days');
            }
        } else {
            $usage = number_format($usage);
            if ( $usage == 1 ) {
                $usage = $usage." ".__('hour');
            } else {
                $usage = $usage." ".__('hours');
            }
        }
    } else {
        if ( $usage == 1 ) {
            $usage = $usage." ".__('minute');
        } else {
            $usage = $usage." ".__('minutes');
        }
    }
    return $usage;
}

function humanize_usage_size($usage) {
    if ( $usage > 1024 ) {
        $usage = $usage / 1024;
        if ( $usage > 1024 ) {
                $usage = $usage / 1024 ;
                if ( $usage > 1024 ) {
                    $usage = $usage / 1024 ;
                    $usage = number_format($usage, 2);
                } else {
                    $usage = number_format($usage, 2);
                }
        } else {
            $usage = number_format($usage, 2);
        }
    }

    return $usage;
}

function humanize_usage_measure($usage) {
    $measure = 'kb';

    if ( $usage > 1024 ) {
        $usage = $usage / 1024;
        if ( $usage > 1024 ) {
                $usage = $usage / 1024 ;
                if ( $usage > 1024 ) {
                    $measure = 'pb';
                } else {
                    $measure = 'tb';
                }
        } else {
            $measure = 'gb';
        }
    } else {
        $measure = 'mb';
    }

    return __($measure);
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

function send_email($to,$subject,$mailtext,$from) {
    $charset = "utf-8";
    $to = '<'.$to.'>';
    $boundary = '--' . md5( uniqid("myboundary") );
    $priorities = array( '1 (Highest)', '2 (High)', '3 (Normal)', '4 (Low)', '5 (Lowest)' );
    $priority = $priorities[2];
    $ctencoding = "8bit";
    $sep = chr(13) . chr(10);
    $disposition = "inline";
    $subject = "=?$charset?B?".base64_encode($subject)."?=";
    $header = "From: $from \nX-Priority: $priority\nCC:\n";
    $header .= "Mime-Version: 1.0\nContent-Type: text/plain; charset=$charset \n";
    $header .= "Content-Transfer-Encoding: $ctencoding\nX-Mailer: Php/libMailv1.3\n";
    $message = $mailtext;
    mail($to, $subject, $message, $header);
}

function display_error_block() {
    if (!empty($_SESSION['error_msg'])) {
        echo '
            <div>
                <script type="text/javascript">
                    $(function() {
                        $( "#dialog:ui-dialog" ).dialog( "destroy" );
                        $( "#dialog-message" ).dialog({
                            modal: true,
                            buttons: {
                                Ok: function() {
                                    $( this ).dialog( "close" );
                                }
                            }
                        });
                    });
                </script>
                <div id="dialog-message" title="">
                    <p>'. htmlentities($_SESSION['error_msg']) .'</p>
                </div>
            </div>'."\n";
        unset($_SESSION['error_msg']);
    }
}

function list_timezones() {
    $tz = new DateTimeZone('HAST');
    $timezone_offsets['HAST'] = $tz->getOffset(new DateTime);
    $tz = new DateTimeZone('HADT');
    $timezone_offsets['HADT'] = $tz->getOffset(new DateTime);
    $tz = new DateTimeZone('AKST');
    $timezone_offsets['AKST'] = $tz->getOffset(new DateTime);
    $tz = new DateTimeZone('AKDT');
    $timezone_offsets['AKDT'] = $tz->getOffset(new DateTime);
    $tz = new DateTimeZone('PST');
    $timezone_offsets['PST'] = $tz->getOffset(new DateTime);
    $tz = new DateTimeZone('PDT');
    $timezone_offsets['PDT'] = $tz->getOffset(new DateTime);
    $tz = new DateTimeZone('MST');
    $timezone_offsets['MST'] = $tz->getOffset(new DateTime);
    $tz = new DateTimeZone('MDT');
    $timezone_offsets['MDT'] = $tz->getOffset(new DateTime);
    $tz = new DateTimeZone('CST');
    $timezone_offsets['CST'] = $tz->getOffset(new DateTime);
    $tz = new DateTimeZone('CDT');
    $timezone_offsets['CDT'] = $tz->getOffset(new DateTime);
    $tz = new DateTimeZone('EST');
    $timezone_offsets['EST'] = $tz->getOffset(new DateTime);
    $tz = new DateTimeZone('EDT');
    $timezone_offsets['EDT'] = $tz->getOffset(new DateTime);
    $tz = new DateTimeZone('AST');
    $timezone_offsets['AST'] = $tz->getOffset(new DateTime);
    $tz = new DateTimeZone('ADT');
    $timezone_offsets['ADT'] = $tz->getOffset(new DateTime);

    foreach(DateTimeZone::listIdentifiers() as $timezone){
        $tz = new DateTimeZone($timezone);
        $timezone_offsets[$timezone] = $tz->getOffset(new DateTime);
    }

    foreach($timezone_offsets as $timezone => $offset){
        $offset_prefix = $offset < 0 ? '-' : '+';
        $offset_formatted = gmdate( 'H:i', abs($offset) );
        $pretty_offset = "UTC${offset_prefix}${offset_formatted}";
        $t = new DateTimeZone($timezone);
        $c = new DateTime(null, $t);
        $current_time = $c->format('H:i:s');
        $timezone_list[$timezone] = "$timezone [ $current_time ] ${pretty_offset}";
    }
    return $timezone_list;
}
?>
