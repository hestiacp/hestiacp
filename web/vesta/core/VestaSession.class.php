<?php

class VestaSession
{
    
    static public $instance = null;
    
    static function start()
    {
	session_start();
    }
    
    /**
     * Grab current instance or create it
     *
     * @return AjaxHandler
     */
    static function getInstance() 
    {
        return null == self::$instance ? self::$instance = new self() : self::$instance;
    }
     
    static function authorize($username)
    {
	$_SESSION['user'] = $username;
	return true;
    }

    public function getUser()
    {
	//var_dump($_SESSION);die();
	if (isset($_SESSION['user'])) {
	    return array('uid' => $_SESSION['user']);
	}

	print '{"result": "NOT_AUTHORISED"}';
	exit;
    }
    
}

?>
