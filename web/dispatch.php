<?php

function error_dumper($errno, $errstr, $errfile, $errline)
{
    if (!(error_reporting() & $errno)) {
        return;
    }
    
    $log = fopen('/tmp/vesta.php.log', 'a+');
    
    switch ($errno) {
    case E_USER_ERROR:
        $o = "ERROR: [$errno] $errstr\n";
        $o.= "  Fatal error on line $errline in file $errfile";
        $o.= ", PHP " . PHP_VERSION . " (" . PHP_OS . ")\n";
        $o.= "Aborting...\n";
	fwrite($log, $o);
	fclose($log);
        exit(1);
        break;

    case E_USER_WARNING:
        $o = "WARNING: [$errno] $errstr\n";
        fwrite($log, $o);
	fclose($log);
	break;

    case E_USER_NOTICE:
        $o =  "NOTICE: [$errno] $errstr\n";
        fwrite($log, $o);
	fclose($log);
	break;

    default:
        $o = "Unknown error type: [$errno] $errstr\n";
        fwrite($log, $o);
	fclose($log);
	break;
    }

    /* Don't execute PHP internal error handler */
    return true;
}

set_error_handler('error_dumper');

require dirname(__FILE__).DIRECTORY_SEPARATOR.'vesta/app.init.php';

?>