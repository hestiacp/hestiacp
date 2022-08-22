<?php

    $check_csrf = true;

    if ($_SERVER['SCRIPT_FILENAME'] == '/usr/local/hestia/web/inc/mail-wrapper.php' || $_SERVER['SCRIPT_FILENAME'] == '/usr/local/hestia//web/inc/mail-wrapper.php') {
        $check_csrf=false;
    } // execute only from CLI
    if ($_SERVER['SCRIPT_FILENAME'] == '/usr/local/hestia/web/reset/mail/index.php' || $_SERVER['SCRIPT_FILENAME'] == '/usr/local/hestia/web//reset/mail/index.php') {
        $check_csrf=false;
    } // Localhost only
    if ($_SERVER['SCRIPT_FILENAME'] == '/usr/local/hestia/web/api/index.php' || $_SERVER['SCRIPT_FILENAME'] == '/usr/local/hestia/web//api/index.php') {
        $check_csrf=false;
    } // Own check
    if (substr($_SERVER['SCRIPT_FILENAME'], 0, 22)=='/usr/local/hestia/bin/') {
        $check_csrf=false;
    }

    function checkStrictness($level)
    {
        if ($level >= $_SESSION['POLICY_CSRF_STRICTNESS']) {
            return true;
        } else {
            http_response_code(400);
            echo "<h1>Potential use CSRF detected</h1>\n".
            "<p>Please disable any plugins/add-ons inside your browser or contact your system administrator. If you are the system administrator you can run v-change-sys-config-value 'POLICY_CSRF_STRICTNESS' '0' as root to disable this check.<p>".
            "<p>If you followed a bookmark or an static link <a href='/'>please click here</a>";
            die();
        }
    }

    function prevent_post_csrf()
    {
        if (!empty($_SERVER['REQUEST_METHOD'])) {
            if ($_SERVER['REQUEST_METHOD']==='POST') {
                list($hostname, $port) = explode(':', $_SERVER["HTTP_HOST"].":");
                if(empty($port)){
                    $port = 443;
                }
                if (isset($_SERVER['HTTP_ORIGIN'])) {
                    $origin_host = parse_url($_SERVER['HTTP_ORIGIN'], PHP_URL_HOST);
                    if (strcmp($origin_host, gethostname()) === 0 && in_array($port, array('443',$_SERVER['SERVER_PORT']))) {
                        return checkStrictness(2);
                    } else {
                        if (strcmp($origin_host, $hostname) === 0 && in_array($port, array('443',$_SERVER['SERVER_PORT']))) {
                            return checkStrictness(1);
                        } else {
                            return checkStrictness(0);
                        }
                    }
                }
            }
        }
    }

    function prevent_get_csrf()
    {
        if (!empty($_SERVER['REQUEST_METHOD'])) {
            if ($_SERVER['REQUEST_METHOD']==='GET') {
                list($hostname, $port) = explode(':', $_SERVER["HTTP_HOST"].":");
                if(empty($port)){
                    $port = 443;
                }
                //list of possible entries route and these should never be blocked
                if (in_array($_SERVER['DOCUMENT_URI'], array('/list/user/index.php', '/login/index.php','/list/web/index.php','/list/dns/index.php','/list/mail/index.php','/list/db/index.php','/list/cron/index.php','/list/backup/index.php','/reset/index.php'))) {
                    return true;
                }
                if (isset($_SERVER['HTTP_REFERER'])) {
                    $referrer_host = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
                    if (strcmp($referrer_host, gethostname()) === 0 && in_array($port, array('443',$_SERVER['SERVER_PORT']))) {
                        return checkStrictness(2);
                    } else {
                        if (strcmp($referrer_host, $hostname) === 0 && in_array($port, array('443',$_SERVER['SERVER_PORT']))) {
                            return checkStrictness(1);
                        } else {
                            return checkStrictness(0);
                        }
                    }
                } else {
                    return checkStrictness(0);
                }
            }
        }
    }

    if ($check_csrf == true) {
        prevent_post_csrf();
        prevent_get_csrf();
    }
