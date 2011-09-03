<?php

function error_dumper($errno, $errstr, $errfile, $errline)
{
    if (!(error_reporting() & $errno)) {
        return;
    }
    
    $log = fopen('/tmp/vesta.php.log', 'a+');
    
    switch ($errno) {
    case E_USER_ERROR:
        $o = date('Y-m-d H:i:s')."ERROR: $errstr [$errfile $errline]\n";
        $o.= "  Fatal error on line $errline in file $errfile";
        $o.= ", PHP " . PHP_VERSION . " (" . PHP_OS . ")\n";
        $o.= "Aborting...\n";
        fwrite($log, $o);
        fclose($log);
        exit(1);
        break;

    case E_USER_WARNING:
        $o = date('Y-m-d H:i:s')."WARNING: $errstr [$errfile $errline]\n";
        fwrite($log, $o);
        fclose($log);
    break;

    case E_USER_NOTICE:
        $o =  date('Y-m-d H:i:s')."NOTICE: $errstr [$errfile $errline]\n";
        fwrite($log, $o);
        fclose($log);
    break;

    default:
        $o = date('Y-m-d H:i:s')."Unknown error type: $errstr [$errfile $errline]\n";
        fwrite($log, $o);
        fclose($log);
    break;
    }

    /* Don't execute PHP internal error handler */
    return true;
}

set_error_handler('error_dumper');

?>
