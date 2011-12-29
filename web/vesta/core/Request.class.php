<?php

/**
 * Request
 *
 * Holds parameters, decorating them and providing easy access
 *
 * @author Malishev Dima <dima.malishev@gmail.com>
 * @author vesta, http://vestacp.com
 * @copyright vesta 2010-2011
 */
class Request 
{

    protected $server = array();
    protected $post = array();
    protected $get = array();
    protected $global = array();
    protected $_merged = array();
    //protected $_spell = array();

    /**
     *
     */
    public function __construct() 
    {
        $this->post = $_POST;
        $this->get = $_GET;
        $this->server = $_SERVER;

        $this->mergeContainers();
    }

    /**
     * Merge holders into single holder
     */
    public function mergeContainers() 
    {
        $this->_merged = array_merge($this->server, 
                                     $this->post, 
                                     $this->get, 
                                     $this->global);
        //$this->_spell = json_decode($this->_merged['spell'], true);
    }

    /**
     * Get parameter
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getParameter($key, $default=false) 
    {
        $param = isset($this->_merged[$key]) ? $this->_merged[$key] : $default;      
        if ($json = @json_decode($param, true)) {
            return $json;
        }

        return $param;
    }

    /**
     * Get spell variable from parameters
     *     
     * @return array
     */
    /*public function getSpell() 
    {
      return $this->_spell;
    }*/

    /**
     * Check if parameter is set
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     * 
     */
    public function hasParameter($key, $default=false) 
    {
        return isset($this->_merged[$key]);
    }

    /**
     * Check if request is post
     * 
     * TODO: write the method
     *
     * @return boolean
     */
    public function isPost() 
    {
        return true;
    }
    
    public function getUserIP()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Dissassemble ajax method
     * Breaks ajax requested method param into "ENTITY"."ACTION"
     * for instance DNS.getList into "namespase" => DNS, "function" => "getList" 
     * for triggering ($DNS->getListExecuty();)
     * 
     * TODO: write the method
     *
     * @return array
     */
    static public function parseAjaxMethod($request) 
    {
        if (!$request->hasParameter('jedi_method')) 
        {
            throw new ProtectionException(Message::INVALID_METHOD);
        }
        $method = explode('.', $request->getParameter('jedi_method'));
        if (count($method) != 2) 
        {
            throw new ProtectionException(Message::INVALID_METHOD);
        }

        return array('namespace' => ($method[0]), 'function' => ($method[1]));
    }

}

