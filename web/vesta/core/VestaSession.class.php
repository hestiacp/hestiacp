<?php

class VestaSession
{
    
    static public $instance = null;
    
    public function __construct()
    {
	//session_start();
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
     
    public function getUser()
    {
	//var_dump($_SESSION);die();
        return array('uid' => 'vesta');
    }
    
}

?>
