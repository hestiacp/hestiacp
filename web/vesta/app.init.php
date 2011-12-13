<?php

define('VESTA_DIR',  dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
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
