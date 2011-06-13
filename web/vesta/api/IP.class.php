<?php
/**
 * IP 
 * 
 * @author vesta, http://vestacp.com/
 * @copyright vesta 2010 
 * 
 */
class IP extends AjaxHandler
{
    function getListExecute($request) 
    {
        $reply = array();
	
        $result = Vesta::execute(Vesta::V_LIST_SYS_IPS, array(Config::get('response_type')));
        foreach ($result['data'] as $ip => $details) {
	  $reply[] = array_merge(
				 array(
				       'IP_ADDRESS' => $ip,
				       'DATE' => date(Config::get('ui_date_format', strtotime($details['DATE'])))
				       ), $details);
        }
	
        if(!$result['status'])
	  $this->errors[] = array($result['error_code'] => $result['error_message']);
	
	return $this->reply($result['status'], $reply);
    }


    function getListUserIpsExecute($request) 
    {
        $reply = array();
	
        $result = Vesta::execute(Vesta::V_LIST_SYS_IPS, array(Config::get('response_type')));
        foreach ($result['data'] as $ip => $details) {
	  $reply[] = array_merge(
				 array(
				       'IP_ADDRESS' => $ip,
				       'DATE' => date(Config::get('ui_date_format', strtotime($details['DATE'])))
				       ), $details);
        }
	
        if(!$result['status'])
	  $this->errors[] = array($result['error_code'] => $result['error_message']);
	
	return $this->reply($result['status'], $reply);
    }
    

    function addExecute($request) 
    {
      $r = new Request();
      $_s = $r->getSpell();
      $_user = 'vesta';
      
      $params = array(
		      'IP_ADDRESS' => $_s['IP_ADDRESS'],
		      'MASK' => $_s['MASK'],
		      'INTERFACE' => $_s['INTERFACE'],
		      'OWNER' => $_s['OWNER'],
		      'IP_STATUS' => $_s['IP_STATUS'],
		      'IP_NAME' => $_s['IP_NAME']
		      );
      
      $result = Vesta::execute(Vesta::V_ADD_SYS_IP, $params);
      

      if(!$result['status'])
	$this->errors[] = array($result['error_code'] => $result['error_message']);
      
      return $this->reply($result['status'], $result['data']);
    }

    function delExecute($request) 
    {
      $r = new Request();
      $_s = $r->getSpell();
      $_user = 'vesta';
      
      $params = array(
		    'IP_ADDRESS' => $_s['IP_ADDRESS']
		    );
      
      $result = Vesta::execute(Vesta::V_DEL_SYS_IP, $params);
      
      if(!$result['status'])
	$this->errors[] = array($result['error_code'] => $result['error_message']);
      
      return $this->reply($result['status'], $result['data']);
    }


  
    function changeExecute($request)
    {
      $r = new Request();
      $_s = $r->getSpell();
      $_old = $_s['old'];
      $_new = $_s['new'];
      
      $_user = 'vesta';

    
      if($_old['OWNER'] != $_new['OWNER'])
	{
	  $result = array();
	  $result = Vesta::execute(Vesta::V_CHANGE_SYS_IP_OWNER, array('OWNER' => $_new['OWNER'], 'IP' => $_new['IP_ADDRESS']));
	  if(!$result['status'])
	    {
	      $this->status = FALSE;
	      $this->errors['OWNER'] = array($result['error_code'] => $result['error_message']);
	    }
	}

      if($_old['NAME'] != $_new['NAME'])
	{
	  $result = array();
	  $result = Vesta::execute(Vesta::V_CHANGE_SYS_IP_NAME, array('IP' => $_new['IP_ADDRESS'], 'NAME' => $_new['NAME']));
	  if(!$result['status'])
	    {
	      $this->status = FALSE;
	      $this->errors['NAME'] = array($result['error_code'] => $result['error_message']);
	    }
	}

      if($_old['IP_STATUS'] != $_new['IP_STATUS'])
	{
	  $result = array();
	  $result = Vesta::execute(Vesta::V_CHANGE_SYS_IP_STATUS, array('IP' => $_new['IP_ADDRESS'], 'IP_STATUS' => $_new['IP_STATUS']));
	  if(!$result['status'])
	    {
	      $this->status = FALSE;
	      $this->errors['IP_STATUS'] = array($result['error_code'] => $result['error_message']);
	    }
	}


      if(!$result['status'])
	$this->errors[] = array($result['error_code'] => $result['error_message']);
      
      return $this->reply($result['status'], $result['data']);
    }


    function getSysInterfacesExecute($request) 
    {
        $reply = array();
	
        $result = Vesta::execute(Vesta::V_LIST_SYS_INTERFACES, array(Config::get('response_type')));

        foreach ($result['data'] as $iface) 
	  $reply[$iface] = $iface;
	
        if(!$result['status'])
	  $this->errors[] = array($result['error_code'] => $result['error_message']);
	
	return $this->reply($result['status'], $reply);
    }
    
}