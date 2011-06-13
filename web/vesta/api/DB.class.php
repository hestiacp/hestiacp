<?php

/**
 * DB 
 * 
 * @author vesta, http://vestacp.com/
 * @copyright vesta 2011
 */
class DB extends AjaxHandler {
    function getListExecute($request) 
    {
    $_user = 'vesta';
    $reply = array();
    
    $result = Vesta::execute(Vesta::V_LIST_DB_BASES, array($_user, Config::get('response_type')));
    
    //    echo '<pre>';
    //    print_r($result);

    foreach($result['data'] as $db => $record)
      {
	$reply[$db] = array(
			    'DB' => $db,
			    'USER' => $record['USER'],
			    'HOST' => $record['HOST'],
			    'TYPE' => $record['TYPE'],
			    'U_DISK' => $record['U_DISK'],
			    'SUSPEND' => $record['SUSPEND'],
			    'DATE' => date(Config::get('ui_date_format', strtotime($record['DATE'])))
			    );
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
		    'USER' => $_user,
		    'DB' => $_s['DB'],
		    'DB_USER' => $_s['DB_USER'],
		    'DB_PASSWORD' => $_s['DB_PASSWORD'],
		    'TYPE' => $_s['TYPE']
		    );
    if($_s['HOST'])
      $params['HOST'] = $_s['HOST'];
    
    $result = Vesta::execute(Vesta::V_ADD_DB_BASE, $params);


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
		    'USER' => $_user,
		    'DB' => $_user.'_'.$_s['DB']
		    );
    
    $result = Vesta::execute(Vesta::V_DEL_DB_BASE, $params);
    
    if(!$result['status'])
      $this->errors[] = array($result['error_code'] => $result['error_message']);
    
    return $this->reply($result['status'], $result['data']);
  }
  
    
  
  function changePasswordExecute($request)
  {
    $r = new Request();
    $_s = $r->getSpell();

    $_user = 'vesta';
    
    $result = array();
    $params = array(
		    'USER' => $_user,
		    'DB' => $_user.'_'.$_s['DB'],
		    'PASSWORD' => $_s['DB_PASSWORD']
		    );

    $result = Vesta::execute(Vesta::V_CHANGE_DB_PASSWORD, $params);
    
    if(!$result['status'])
      $this->errors[] = array($result['error_code'] => $result['error_message']);
    
    return $this->reply($result['status'], $result['data']);
  }



  function suspendExecute($request)
  {
    $r = new Request();
    $_s = $r->getSpell();

    $_user = 'vesta';
    
    $params = array(
		    'USER' => $_user,
		    'DB' => $_user.'_'.$_s['DB']
		    );
    
    $result = Vesta::execute(Vesta::V_SUSPEND_DB_BASE, $params);
    
    if(!$result['status'])
      $this->errors[] = array($result['error_code'] => $result['error_message']);
    
    return $this->reply($result['status'], $result['data']);
  }



  function unsuspendExecute($request)
  {
    $r = new Request();
    $_s = $r->getSpell();

    $_user = 'vesta';
    
    $params = array(
		    'USER' => $_user,
		    'DB' => $_user.'_'.$_s['DB']
		    );
    
    $result = Vesta::execute(Vesta::V_UNSUSPEND_DB_BASE, $params);
    
    if(!$result['status'])
      $this->errors[] = array($result['error_code'] => $result['error_message']);
    
    return $this->reply($result['status'], $result['data']);
  }



  function suspendAllExecute($request)
  {
    $r = new Request();
    $_s = $r->getSpell();

    $_user = 'vesta';
    $_JOB = $_s['JOB'];
    
    $params = array(
		    'USER' => $_user
		    );
    
    $result = Vesta::execute(Vesta::V_SUSPEND_DB_BASES, $params);
    
    if(!$result['status'])
      $this->errors[] = array($result['error_code'] => $result['error_message']);
    
    return $this->reply($result['status'], $result['data']);
  }



  function unsuspendAllExecute($request)
  {
    $r = new Request();
    $_s = $r->getSpell();

    $_user = 'vesta';
    
    $params = array(
		    'USER' => $_user
		    );
    
    $result = Vesta::execute(Vesta::V_UNSUSPEND_DB_BASES, $params);
    
    if(!$result['status'])
      $this->errors[] = array($result['error_code'] => $result['error_message']);
    
    return $this->reply($result['status'], $result['data']);
  }
    
}
