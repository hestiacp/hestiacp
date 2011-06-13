<?php

class Config {
    
    protected $_config = array();   
    static public $instance = null;
    
    public function __construct() {
        $this->_config = parse_ini_file(V_ROOT_DIR.'config'.DIRECTORY_SEPARATOR.'vesta_config.ini');
    }

    public function getParameter($key){
        return isset($this->_config[$key]) ? $this->_config[$key] : false;
    }
    
    /**
     * Grab current instance or create it
     *
     * @return <type>
     */
    static function getInstance($request=null) {
        return null == self::$instance ? self::$instance = new self() : self::$instance;
    }
    
    static function get($key){
        $ref = self::getInstance();
        
        return $ref->getParameter($key);
    }
    
}