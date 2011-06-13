<?php

/**
 * Request
 *
 * Holds parameters, decorating them and providing easy access
 *
 * @author vesta, http://vestacp.com/
 * @copyright vesta 2010
 */
class Request {

    protected $server = array();
    protected $post = array();
    protected $get = array();
    protected $global = array();
    protected $_merged = array();
    protected $_spell = array();

    /**
     *
     */
    public function __construct() {
        $this->post = $_POST;
        $this->get = $_GET;
        $this->server = $_SERVER;

        $this->mergeContainers();
    }

    /**
     * Merge holders into single holder
     */
    function mergeContainers() {
        $this->_merged = array_merge($this->server, $this->post, $this->get, $this->global);
	$this->_spell = json_decode($this->_merged['spell'], true);
    }

    /**
     * Get parameter
     *
     * @param <string> $key
     * @param <mixed> $default
     * @return <mixed>
     * 
     */
    function getParameter($key, $default=false) {
      return isset($this->_merged[$key]) ? $this->_merged[$key] : $default;
      //        return isset($this->_spell[$key]) ? $this->_spell[$key] : $default;
    }

    function getSpell() {
      return $this->_spell;
    }

    function hasParameter($key, $default=false) {
        return isset($this->_merged[$key]);
    }

    /**
     * Check if request is post
     * 
     * TODO: write the method
     *
     * @return <boolean>
     */
    function isPost() {
        return true;
    }

    static function parseAjaxMethod($request) {
        if (!$request->hasParameter('jedi_method')) {
            throw new ProtectionException(Message::INVALID_METHOD);
        }

        $method = explode('.', $request->getParameter('jedi_method'));

        if (count($method) != 2) {
            throw new ProtectionException(Message::INVALID_METHOD);
        }

        return array('namespace' => ucfirst($method[0]), 'function' => strtolower($method[1]));
    }

}

