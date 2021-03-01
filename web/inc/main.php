<?php

session_start();

define('HESTIA_CMD', '/usr/bin/sudo /usr/local/hestia/bin/');
define('JS_LATEST_UPDATE', time());
define('DEFAULT_PHP_VERSION', "php-" . exec('php -r "echo (float)phpversion();"'));

$i = 0;

// Saving user IPs to the session for preventing session hijacking
$user_combined_ip = $_SERVER['REMOTE_ADDR'];

if(isset($_SERVER['HTTP_CLIENT_IP'])){
    $user_combined_ip .=  '|'. $_SERVER['HTTP_CLIENT_IP'];
}
if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
    $user_combined_ip .=  '|'. $_SERVER['HTTP_X_FORWARDED_FOR'];
}
if(isset($_SERVER['HTTP_FORWARDED_FOR'])){
    $user_combined_ip .=  '|'. $_SERVER['HTTP_FORWARDED_FOR'];
}
if(isset($_SERVER['HTTP_X_FORWARDED'])){
    $user_combined_ip .=  '|'. $_SERVER['HTTP_X_FORWARDED'];
}
if(isset($_SERVER['HTTP_FORWARDED'])){
    $user_combined_ip .=  '|'. $_SERVER['HTTP_FORWARDED'];
}
if(isset($_SERVER['HTTP_CF_CONNECTING_IP'])){
    if(!empty($_SERVER['HTTP_CF_CONNECTING_IP'])){
      $user_combined_ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    }
}

if(!isset($_SESSION['user_combined_ip'])){
    $_SESSION['user_combined_ip'] = $user_combined_ip;
}

// Checking user to use session from the same IP he has been logged in
if($_SESSION['user_combined_ip'] != $user_combined_ip && $_SERVER['REMOTE_ADDR'] != '127.0.0.1'){
    session_destroy();
    session_start();
    $_SESSION['request_uri'] = $_SERVER['REQUEST_URI'];
    header("Location: /login/");
    exit;
}
// Load Hestia Config directly
    load_hestia_config();

// Check system settings
if ((!isset($_SESSION['VERSION'])) && (!defined('NO_AUTH_REQUIRED'))) {
    session_destroy();
    session_start();
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

// Generate CSRF Token
if (isset($_SESSION['user'])) {
    if(!isset($_SESSION['token'])){
        $token = bin2hex(file_get_contents('/dev/urandom', false, null, 0, 16));
        $_SESSION['token'] = $token;
    }
}

if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
}

if (isset($_SESSION['look']) && ( $_SESSION['look'] != 'admin' )) {
    $user = $_SESSION['look'];
}

require_once(dirname(__FILE__).'/i18n.php');

function check_error($return_var) {
    if ( $return_var > 0 ) {
        header("Location: /error/");
        exit;
    }
}

function check_return_code($return_var,$output) {
    if ($return_var != 0) {
        $error = implode('<br>', $output);
        if (empty($error)) $error = sprintf(_('Error code:'),$return_var);
        $_SESSION['error_msg'] = $error;
    }
}

function render_page($user, $TAB, $page) {
    $__template_dir = dirname(__DIR__) . '/templates/';
    $__pages_js_dir = dirname(__DIR__) . '/js/pages/';

    // Header
    include($__template_dir . 'header.html');

    // Panel
    top_panel(empty($_SESSION['look']) ? $_SESSION['user'] : $_SESSION['look'], $TAB);

    // Extarct global variables
    // I think those variables should be passed via arguments
    extract($GLOBALS, EXTR_SKIP);

    // Body
    if (($_SESSION['user'] !== 'admin') && (@include($__template_dir . "user/$page.html"))) {
        // User page loaded
    } else {
        // Not admin or user page doesn't exist
        // Load admin page
        @include($__template_dir . "admin/$page.html");
    }

    // Including common js files
    @include_once(dirname(__DIR__) . '/templates/scripts.html');
    // Including page specific js file
    if(file_exists($__pages_js_dir.$page.'.js'))
       echo '<script type="text/javascript" src="/js/pages/'.$page.'.js?'.JS_LATEST_UPDATE.'"></script>';

    // Footer
    include($__template_dir . 'footer.html');
}

function top_panel($user, $TAB) {
    global $panel;
    $command = HESTIA_CMD."v-list-user ".escapeshellarg($user)." 'json'";
    exec ($command, $output, $return_var);
    if ( $return_var > 0 ) {
        header("Location: /error/");
        exit;
    }
    $panel = json_decode(implode('', $output), true);
    unset($output);

    if ( $user == 'admin' ) {
        include(dirname(__FILE__).'/../templates/admin/panel.html');
    } else {
        include(dirname(__FILE__).'/../templates/user/panel.html');
    }
}

function translate_date($date){
  $date = strtotime($date);
  return strftime("%d &nbsp;", $date)._(strftime("%b", $date)).strftime(" &nbsp;%Y", $date);
}

function humanize_time($usage) {
    if ( $usage > 60 ) {
        $usage = $usage / 60;
        if ( $usage > 24 ) {
             $usage = $usage / 24;

            $usage = number_format($usage);
            if ( $usage == 1 ) {
                $usage = $usage." "._('day');
            } else {
                $usage = $usage." "._('days');
            }
        } else {
            $usage = number_format($usage);
            if ( $usage == 1 ) {
                $usage = $usage." "._('hour');
            } else {
                $usage = $usage." "._('hours');
            }
        }
    } else {
        if ( $usage == 1 ) {
            $usage = $usage." "._('minute');
        } else {
            $usage = $usage." "._('minutes');
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

    return _($measure);
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

function list_timezones() {
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

/**
 * A function that tells is it MySQL installed on the system, or it is MariaDB.
 *
 * Explaination:
 * $_SESSION['DB_SYSTEM'] has 'mysql' value even if MariaDB is installed, so you can't figure out is it really MySQL or it's MariaDB.
 * So, this function will make it clear.
 *
 * If MySQL is installed, function will return 'mysql' as a string.
 * If MariaDB is installed, function will return 'mariadb' as a string.
 *
 * Hint: if you want to check if PostgreSQL is installed - check value of $_SESSION['DB_SYSTEM']
 *
 * @return string
 */
function is_it_mysql_or_mariadb() {
    exec (HESTIA_CMD."v-list-sys-services json", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    unset($output);
    $mysqltype='mysql';
    if (isset($data['mariadb'])) $mysqltype='mariadb';
    return $mysqltype;
}

function load_hestia_config() {
    // Check system configuration
    exec (HESTIA_CMD . "v-list-sys-config json", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    $sys_arr = $data['config'];
    foreach ($sys_arr as $key => $value) {
        $_SESSION[$key] = $value;
    }
}

/**
 * Returns the list of all web domains from all users grouped by Backend Template used and owner
 *
 * @return array
 */
function backendtpl_with_webdomains() {
    exec (HESTIA_CMD . "v-list-users json", $output, $return_var);
    $users = json_decode(implode('', $output), true);
    unset($output);

    $backend_list=[];
    foreach ($users as $user => $user_details) {
        exec (HESTIA_CMD . "v-list-web-domains ". escapeshellarg($user) . " json", $output, $return_var);
        $domains = json_decode(implode('', $output), true);
        unset($output);

        foreach ($domains as $domain => $domain_details) {
            if (!empty($domain_details['BACKEND'])) {
                $backend = $domain_details['BACKEND'];
                $backend_list[$backend][$user][] = $domain;
            }
        }
    }
    return $backend_list;
}
/**
 * Check if password is valid
 *
 * @return int; 1 / 0
 */
function validate_password($password){
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(.){8,}$/', $password);
}
