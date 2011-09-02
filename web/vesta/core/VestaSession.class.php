<?php

class VestaSession
{
    
    static public $instance = null;
    
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
        return array('uid' => 'vesta');
    }
    
}

?>
