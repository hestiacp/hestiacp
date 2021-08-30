<?php
    $check_csrf = true;
    
    if ( $_SERVER['SCRIPT_FILENAME'] == '/usr/local/hestia/web/inc/mail-wrapper.php '){ $check_csrf=false; } // Excutede only from CLI
    if ( $_SERVER['SCRIPT_FILENAME'] == '/usr/local/hestia/web/reset/mail/index.php '){ $check_csrf=false; } // Localhost only
    if ( $_SERVER['SCRIPT_FILENAME'] == '/usr/local/hestia/web/api/index.php' ){ $check_csrf=false; } // Own check
    if (substr($_SERVER['SCRIPT_FILENAME'], 0, 22)=='/usr/local/hestia/bin/' ){ $check_csrf=false; }
    
    function checkStrictness($level){
        if ($level >= $_SESSION['POLICY_CSRF_STRICTNESS']) {
            return true;
        }else{
            echo "CSRF detected (".$level.") Please disable any plugins/add-ons inside your browser or contact your system administrator. If you are the system administrator run v-change-sys-config-value 'POLICY_CSRF_STRICTNESS' '0' as root to disable this check.";
            die();
        }
    }
    function prevent_post_csrf(){
        if ($_SERVER['REQUEST_METHOD']=='POST') {
            $hostname = explode( ':', $_SERVER['HTTP_HOST']);
            $port=$hostname[1];
            $hostname=$hostname[0];
            if (strpos($_SERVER['HTTP_ORIGIN'],gethostname()) !== false  && in_array($port, array('443',$_SERVER['SERVER_PORT'])) ) { 
                return checkStrictness(2);
            }else{
                if (strpos($_SERVER['HTTP_ORIGIN'],$hostname) !== false && in_array($port, array('443',$_SERVER['SERVER_PORT'])) ){ 
                    return checkStrictness(1);
                } else {
                    return checkStrictness(0);
                }
            }
        }
    }
    
    function prevent_get_csrf(){
        if ($_SERVER['REQUEST_METHOD']=='GET') {
            $hostname = explode( ':', $_SERVER['HTTP_HOST']);
            $port=$hostname[1];
            $hostname=$hostname[0];
            if (strpos($_SERVER['HTTP_REFERER'],gethostname()) !== false || strpos($_SERVER['HTTP_REFERER'],$hostname )) {
                $_SERVER['HTTP_ORIGIN'] = $_SERVER['HTTP_REFERER'];
            }
            if (in_array($_SERVER['DOCUMENT_URI'], array('/list/user/index.php', '/login/index.php','/list/web/index.php','/list/dns/index.php','/list/mail/index.php','/list/db/index.php','/list/cron/index.php','/list/backup/index.php')) ){
                return true;
            }
            if (strpos($_SERVER['HTTP_ORIGIN'],gethostname()) !== false  && in_array($port, array('443',$_SERVER['SERVER_PORT'])) ) { 
                return checkStrictness(2);
            }else{
                if (strpos($_SERVER['HTTP_ORIGIN'],$hostname) !== false && in_array($port, array('443',$_SERVER['SERVER_PORT'])) ){ 
                    return checkStrictness(1);
                } else {
                    return checkStrictness(0);
                }
            }
        }
    }
    
    if ( $check_csrf == true){
        prevent_post_csrf();
        prevent_get_csrf();
    }