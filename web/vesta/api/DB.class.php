<?php

/**
 * DB 
 * 
 * @author vesta, http://vestacp.com/
 * @author Dmitry Malishev <dima.malishev@gmail.com>
 * @author Dmitry Naumov-Socolov <naumov.socolov@gmail.com>
 * @copyright vesta 2010-2011
 */
 
class DB extends AjaxHandler 
{
    
    /**
     * Get DB entries
     * 
     * @param Request $request
     * @return string - Ajax Reply
     */
    public function getListExecute($request) 
    {
        $_user  = 'vesta';
        $reply  = array();
        $result = Vesta::execute(Vesta::V_LIST_DB_BASES, array($_user, Config::get('response_type')));
    
        foreach ($result['data'] as $db => $record) {
            $type = $record['TYPE'];
            if (!isset($reply[$type])) {
                $reply[$type] = array();
            }

            $reply[$type][] = array(
                                'DB'        => $db,
                                'OWNER'     => 'John Travlolta',
                                'USERS'     => (array)$record['USER'],
                                'HOST'      => $record['HOST'],
                                'TYPE'      => $record['TYPE'],
                                'U_DISK'    => $record['U_DISK'],
                                'DISK'      => 2024,
                                'SUSPEND'   => $record['SUSPEND'],
                                'DATE'      => date(Config::get('ui_date_format', strtotime($record['DATE'])))
                              );
        }
    
        if (!$result['status']) {
            $this->errors[] = array($result['error_code'] => $result['error_message']);
        }

        $reply['postgre'][] = array(
                                'DB'        => 'x',
                                'OWNER'     => 'John Travlolta',
                                'USERS'     => array('E'),
                                'HOST'      => 'xxx',
                                'TYPE'      => '34',
                                'U_DISK'    => '0',
                                'SUSPEND'   => 'false',
                                'DATE'      => '2011-01-01'
                              );
    
        return $this->reply($result['status'], $reply);
    }

    /**
     * Add DB entry
     * 
     * @param Request $request
     * @return string - Ajax Reply
     */
    public function addExecute($request) 
    {
        $r      = new Request();
        $_s     = $r->getSpell();
        $_user  = 'vesta';
    
        $params = array(
                    'USER'          => $_user,
                    'DB'            => $_s['DB'],
                    'DB_USER'       => $_s['DB_USER'],
                    'DB_PASSWORD'   => $_s['DB_PASSWORD'],
                    'TYPE'          => $_s['TYPE']
                  );
                  
        if ($_s['HOST']) {
            $params['HOST'] = $_s['HOST'];
        }    
            
        $result = Vesta::execute(Vesta::V_ADD_DB_BASE, $params);
        
        if (!$result['status']) {
            $this->errors[] = array($result['error_code'] => $result['error_message']);
        }
    
        return $this->reply($result['status'], $result['data']);
    }
  
    /**
     * Delete DB entry
     * 
     * @param Request $request
     * @return string - Ajax Reply
     */
    public function delExecute($request) 
    {
        $r      = new Request();
        $_s     = $r->getSpell();
        $_user  = 'vesta';
        $params = array(
                    'USER'  => $_user,
                    'DB'    => $_user.'_'.$_s['DB']
                  );
    
        $result = Vesta::execute(Vesta::V_DEL_DB_BASE, $params);
    
        if (!$result['status']) {
            $this->errors[] = array($result['error_code'] => $result['error_message']);
        }
    
        return $this->reply($result['status'], $result['data']);
    }
  
    /**
     * Change Password
     * 
     * @param Request $request
     * @return string - Ajax Reply
     */
    public function changePasswordExecute($request)
    {
        $r      = new Request();
        $_s     = $r->getSpell();
        $_user  = 'vesta';
        $result = array();
        $params = array(
                    'USER'      => $_user,
                    'DB'        => $_user.'_'.$_s['DB'],
                    'PASSWORD'  => $_s['DB_PASSWORD']
                  );

        $result = Vesta::execute(Vesta::V_CHANGE_DB_PASSWORD, $params);
    
        if (!$result['status']) {
            $this->errors[] = array($result['error_code'] => $result['error_message']);
        }
    
        return $this->reply($result['status'], $result['data']);
    }

    /**
     * Suspend DB entry
     * 
     * @param Request $request
     * @return string - Ajax Reply
     */
    public function suspendExecute($request)
    {
        $r      = new Request();
        $_s     = $r->getSpell();
        $_user  = 'vesta';
        $params = array(
                    'USER'  => $_user,
                    'DB'    => $_user.'_'.$_s['DB']
                  );
    
        $result = Vesta::execute(Vesta::V_SUSPEND_DB_BASE, $params);
    
        if (!$result['status']) {
            $this->errors[] = array($result['error_code'] => $result['error_message']);
        }
    
        return $this->reply($result['status'], $result['data']);
    }

    /**
     * Unsuspend DB entry
     * 
     * @param Request $request
     * @return string - Ajax Reply
     */
    public function unsuspendExecute($request)
    {
        $r      = new Request();
        $_s     = $r->getSpell();
        $_user  = 'vesta';
        $params = array(
                    'USER'  => $_user,
                    'DB'    => $_user.'_'.$_s['DB']
                  );
        $result = Vesta::execute(Vesta::V_UNSUSPEND_DB_BASE, $params);
    
        if (!$result['status']) {
            $this->errors[] = array($result['error_code'] => $result['error_message']);
        }
    
        return $this->reply($result['status'], $result['data']);
    }

    /**
     * Batch Suspend DB entries
     * 
     * @param Request $request
     * @return string - Ajax Reply
     */
    public function suspendAllExecute($request)
    {
        $r      = new Request();
        $_s     = $r->getSpell();
        $_user  = 'vesta';
        $_JOB   = $_s['JOB'];
    
        $params = array(
                    'USER' => $_user
                  );
    
        $result = Vesta::execute(Vesta::V_SUSPEND_DB_BASES, $params);
    
        if (!$result['status']) {
            $this->errors[] = array($result['error_code'] => $result['error_message']);
        }
    
        return $this->reply($result['status'], $result['data']);
    }

    /**
     * Batch unsuspend DB entries
     * 
     * @param Request $request
     * @return string - Ajax Reply
     */
    public function unsuspendAllExecute($request)
    {
        $r      = new Request();
        $_s     = $r->getSpell();
        $_user  = 'vesta';
        $params = array(
                    'USER' => $_user
                  );
        $result = Vesta::execute(Vesta::V_UNSUSPEND_DB_BASES, $params);
    
        if (!$result['status']) {
            $this->errors[] = array($result['error_code'] => $result['error_message']);
        }
    
        return $this->reply($result['status'], $result['data']);
    }
    
}
