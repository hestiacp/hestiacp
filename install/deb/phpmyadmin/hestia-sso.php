<?php

/* Hestia way to enable support for SSO to PHPmyAdmin */
/* To install please run v-add-sys-pma-sso */

/* Following keys will get replaced when calling v-add-sys-pma-sso */
define('PHPMYADMIN_KEY', '%PHPMYADMIN_KEY%');
define('API_HOST_NAME', '%API_HOST_NAME%');
define('API_HESTIA_PORT', '%API_HESTIA_PORT%');
define('API_KEY', '%API_KEY%');


class Hestia_API
{
    private $api_url;
    public function __construct()
    {
        $this -> hostname = 'https://' . API_HOST_NAME . ':' . API_HESTIA_PORT .'/api/';
        $this -> key = API_KEY;
        $this -> pma_key = PHPMYADMIN_KEY;
    }

    /* Creates curl request */
    public function request($postvars)
    {
        $postdata = http_build_query($postvars);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this -> hostname);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
        $answer = curl_exec($curl);
        return $answer;
    }

    /* Creates an new temp user in mysql */
    public function create_temp_user($database, $user, $host)
    {
        $post_request = array(
        'hash' => $this -> key,
        'returncode' => 'no',
        'cmd' => 'v-add-database-temp-user',
        'arg1' => $user,
        'arg2' => $database,
        'arg3' => 'mysql',
        'arg4' => $host
        );
        $request = $this -> request($post_request);
        $json = json_decode($request);
        if (json_last_error() == JSON_ERROR_NONE) {
            return $json;
        } else {
            trigger_error('Unable to connect over API please check api connection', E_USER_WARNING);
            return false;
        }
    }

    /* Delete an new temp user in mysql */
    public function delete_temp_user($database, $user, $dbuser, $host)
    {
        $post_request = array(
        'hash' => $this -> key,
        'returncode' => 'yes',
        'cmd' => 'v-delete-database-temp-user',
        'arg1' => $user,
        'arg2' => $database,
        'arg3' => $dbuser,
        'arg4' => 'mysql',
        'arg5' => $host
        );
        $request = $this -> request($post_request);
        if (is_numeric($request) && $request == 0) {
            return true;
        } else {
            return false;
        }
    }

    public function get_user_ip()
    {
        // Saving user IPs to the session for preventing session hijacking
        $user_combined_ip = array();
        if ($_SERVER['REMOTE_ADDR'] != $_SERVER['SERVER_ADDR']) {
            $user_combined_ip[] = $_SERVER['REMOTE_ADDR'];
        }
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $user_combined_ip .=  '|'. $_SERVER['HTTP_CLIENT_IP'];
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            if ($_SERVER['REMOTE_ADDR'] != $_SERVER['HTTP_X_FORWARDED_FOR']) {
                $user_combined_ip[] = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
        }
        if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            if ($_SERVER['REMOTE_ADDR'] != $_SERVER['HTTP_FORWARDED_FOR']) {
                $user_combined_ip[] = $_SERVER['HTTP_FORWARDED_FOR'];
            }
        }
        if (isset($_SERVER['HTTP_X_FORWARDED'])) {
            if ($_SERVER['REMOTE_ADDR'] != $_SERVER['HTTP_X_FORWARDED']) {
                $user_combined_ip[] = $_SERVER['HTTP_X_FORWARDED'];
            }
        }
        if (isset($_SERVER['HTTP_FORWARDED'])) {
            if ($_SERVER['REMOTE_ADDR'] != $_SERVER['HTTP_FORWARDED']) {
                $user_combined_ip[] =  '|'. $_SERVER['HTTP_FORWARDED'];
            }
        }
        if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
                $user_combined_ip[] = $_SERVER['HTTP_CF_CONNECTING_IP'];
            }
        }
        return implode('|',$user_combined_ip);
    }
}

/* Need to have cookie visible from parent directory */
session_set_cookie_params(0, '/', '', true, true);
/* Create signon session */
$session_name = 'SignonSession';
session_name($session_name);
@session_start();

function session_invalid()
{
    global $session_name;
    //delete all current sessions
    session_destroy();
    setcookie($session_name, null, -1, '/');
    header("Location: " . dirname($_SERVER['PHP_SELF']) . "/index.php");
    die();
}
    $api = new Hestia_API();
    if (!empty($_GET)) {
        if (isset($_GET['logout'])) {
            $api -> delete_temp_user($_SESSION['HESTIA_sso_database'], $_SESSION['HESTIA_sso_user'], $_SESSION['PMA_single_signon_user'], $_SESSION['HESTIA_sso_host']);
            //remove sessin
            session_invalid();
            header("Location: " . dirname($_SERVER['PHP_SELF']) . "/index.php");
            die();
        } else {
            if (isset($_GET['user']) && isset($_GET['hestia_token'])) {
                $database = $_GET['database'];
                $user = $_GET['user'];
                $host = 'localhost';
                $token = $_GET['hestia_token'];
                $time = $_GET['exp'];

                if ($time + 60 > time()) {
                    //note: Possible issues with cloudflare due to ip obfuscation
                    $ip = $api -> get_user_ip();
                    if (!password_verify($database.$user.$ip.$time.PHPMYADMIN_KEY, $token)) {
                        trigger_error('Access denied: There is a security token mismatch '. $time, E_USER_WARNING);
                        session_invalid();
                        die();
                        session_invalid();
                    } else {
                        $id = session_id();
                        //create a new temp user
                        $data = $api -> create_temp_user($database, $user, $host);
                        if ($data) {
                            $_SESSION['PMA_single_signon_user'] = $data -> login -> user;
                            $_SESSION['PMA_single_signon_password'] = $data -> login -> password ;
                            $_SESSION['PMA_single_signon_host'] = $host;
                            //save database / username to be used for sending logout notification.
                            $_SESSION['HESTIA_sso_user'] = $user;
                            $_SESSION['HESTIA_sso_database'] = $database;
                            $_SESSION['HESTIA_sso_host'] = $host;

                            @session_write_close();
                            setcookie($session_name, $id, 0, "/");
                            header("Location: " . dirname($_SERVER['PHP_SELF']) . "/index.php");
                        } else {
                            session_invalid();
                        }
                        die();
                    }
                } else {
                    trigger_error('Link has been expired: System time: '. time() .' / Time provided in link: '. $time, E_USER_WARNING);
                    session_invalid();
                    die();
                }
            }
        }
    } else {
        session_invalid();
        die();
    }
