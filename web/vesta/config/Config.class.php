<?php

/**
 * Config class
 *
 * Reads, manipulate configs
 *
 * @author vesta, http://vestacp.com/
 * @author Dmitry Malishev <dima.malishev@gmail.com>
 * @copyright vesta 2010-2011
 */
class Config 
{
    
    protected $_config      = array();   
    static public $instance = null;
    
    /**
     * Config constructor
     * 
     */
    public function __construct() 
    {
        $this->_config = parse_ini_file(V_ROOT_DIR.'config'.DIRECTORY_SEPARATOR.'vesta_config.ini');
    }

    /**
     * get config parameter
     * 
     * @param string $key
     * @return mixed
     */
    public function getParameter($key)
    {
        return isset($this->_config[$key]) ? $this->_config[$key] : false;
    }
    
    /**
     * Grab current instance or create it
     *
     * @return Config
     */
    static public function getInstance($request = null) 
    {
        return null == self::$instance ? self::$instance = new self() : self::$instance;
    }
    
    /**
     * Shortcut method: get config parameter
     *
     * @param string $key
     * @return mixed
     */
    static public function get($key)
    {
        $ref = self::getInstance();
        
        return $ref->getParameter($key);
    }
    
}
