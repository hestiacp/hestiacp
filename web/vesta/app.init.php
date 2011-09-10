<?php

$url = "http://dev.vestacp.com:8083/dispatch.php";  
$useragent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";  
$ch = curl_init();
curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($_POST));
$result= curl_exec ($ch);
curl_close ($ch);
die();


define('V_ROOT_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);

require_once V_ROOT_DIR . 'config/Config.class.php';
require_once V_ROOT_DIR . 'core/utils/Utils.class.php';
require_once V_ROOT_DIR . 'core/VestaSession.class.php';
require_once V_ROOT_DIR . 'core/Vesta.class.php';
require_once V_ROOT_DIR . 'core/exceptions/SystemException.class.php';
require_once V_ROOT_DIR . 'core/exceptions/ProtectionException.class.php';
require_once V_ROOT_DIR . 'core/utils/Message.class.php';
require_once V_ROOT_DIR . 'core/Request.class.php';
require_once V_ROOT_DIR . 'api/AjaxHandler.php';

/**
 * App execution 
 * 
 * @author Malishev Dima <dima.malishev@gmail.com>
 * @author vesta, http://vestacp.com/
 * @copyright vesta 2010-2011
 */
try {
    // Execution
    AjaxHandler::makeReply(
        AjaxHandler::getInstance()->dispatch(new Request())
    );
}
//
//  Errors handling
//
catch (SystemException $e) {
    AjaxHandler::systemError($e);
}
catch (ProtectionException $e) {
    AjaxHandler::protectionError($e);
}
catch (Exception $e) {
    AjaxHandler::generalError($e);
}   
