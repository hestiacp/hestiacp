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
    public function getListExecute(Request $request) 
    {
        $user  = $this->getLoggedUser();
        $reply  = array();
        $result = Vesta::execute(Vesta::V_LIST_DB_BASES, array($user['uid'], Config::get('response_type')));
    
        foreach ($result['data'] as $db => $record) {
            $type = $record['TYPE'];
            if (!isset($reply[$type])) {
                $reply[$type] = array();
            }
            $reply[$type][] = array(
                                'DB'        => $db,
                                'OWNER'     => $record['USER'],
                                'USER'      => $record['USER'],
                                'USERS'     => (array)$record['USER'],
                                'HOST'      => $record['HOST'],
                                'TYPE'      => $record['TYPE'],
                                'U_DISK'    => $record['U_DISK'],
                                'DISK'      => 2024,
                                'CHARSET'   => strtolower($record['CHARSET']),
                                'SUSPEND'   => $record['SUSPEND'],
                                'DATE'      => $record['DATE']// date(Config::get('ui_date_format', strtotime($record['DATE'])))
                              );
        }
    
        if (!$result['status']) {
            $this->errors[] = array($result['error_code'] => $result['error_message']);
        }

        return $this->reply($result['status'], $reply);
    }

    /**
     * Add DB entry
     * 
     * @param Request $request
     * @return string - Ajax Reply
     */
    public function addExecute(Request $request) 
    {
        $user   = $this->getLoggedUser();
        $_s     = $request->getParameter('spell'); 
        $params = array(
                    'USER'          => $user['uid'],
                    'DB'            => $_s['DB'],
                    'DB_USER'       => $_s['USER'],
                    'DB_PASSWORD'   => $_s['PASSWORD'],
                    'TYPE'          => $_s['TYPE'],
                    'HOST'          => $_s['HOST'],
                    'CHARSET'       => $_s['CHARSET']
                  );

        $result = Vesta::execute(Vesta::V_ADD_DB_BASE, $params);
        
        if (!$result['status']) {
            $this->errors[] = array($result['error_code'] => $result['error_message']);
        }

        if (Utils::getCheckboxBooleanValue($_s['SUSPEND'])) {
            if($result['status']){
                $result = array();

                $result = Vesta::execute(Vesta::V_SUSPEND_DB_BASE, array('USER' => $user['uid'], 'JOB' => $_s['DB']));
                if (!$result['status']) {
                    $this->status = FALSE;
                    $this->errors['SUSPEND'] = array($result['error_code'] => $result['error_message']);
                }   
            }
        }

        return $this->reply($result['status'], $result['data']);
    }
  
    /**
     * Delete DB entry
     * 
     * @param Request $request
     * @return string - Ajax Reply
     */
    public function deleteExecute(Request $request) 
    {
        $_s    = $request->getParameter('spell');
        $user  = $this->getLoggedUser();
        $params = array(
                    'USER'  => $user['uid'],
                    'DB'    => $_s['DB']
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
    public function changeExecute(Request $request)
    {
        $_s = $request->getParameter('spell');
        $_old = $request->getParameter('old');
        $_new = $request->getParameter('new');

        $user  = $this->getLoggedUser();
        $result = array();

		$result = array();
		if(@Utils::getCheckboxBooleanValue($_new['SUSPEND'])){
			$result = Vesta::execute(Vesta::V_SUSPEND_DB_BASE, array('USER' => $user['uid'], 'DB' => $_new['DB']));
			return $this->reply($result['status'], $result['error_message']);
		}
		elseif(@Utils::getCheckboxBooleanValue($_old['SUSPEND'])){
			$result = Vesta::execute(Vesta::V_UNSUSPEND_DB_BASE, array('USER' => $user['uid'], 'DB' => $_new['DB']));
    		if (!$result['status']) {
    			$this->status = FALSE;
    			$this->errors['UNSUSPEND'] = array($result['error_code'] => $result['error_message']);
    			return $this->reply($result['status'], $result['error_message']);
        	}
		}

        if ($_new['PASSWORD'] != Vesta::SAME_PASSWORD && $_new['PASSWORD'] != $_old['PASSWORD']) {
			$params = array(
						'USER'      => $user['uid'],
						'DB'        => $_new['DB'],
						'PASSWORD'  => $_new['PASSWORD']
					  );

			$result = Vesta::execute(Vesta::V_CHANGE_DB_PASSWORD, $params);
    
			if (!$result['status']) {
				$this->errors[] = array($result['error_code'] => $result['error_message']);
			}
		}
    
        return $this->reply($result['status'], $result['data']);
    }

    public function massiveSuspendExecute(Request $request)
    {
        $user   = $this->getLoggedUser();
        $_entities = $request->getParameter('entities');

        foreach($_entities as $entity){
          $result = Vesta::execute(Vesta::V_SUSPEND_DB_BASE, array('USER' => $user['uid'], $entity['DB']));
        }

        return $this->reply($result['status'], $result['data']);
    }

    public function massiveUnsuspendExecute(Request $request)
    {
        $user   = $this->getLoggedUser();
        $_entities = $request->getParameter('entities');

        foreach($_entities as $entity){
            $result = Vesta::execute(Vesta::V_UNSUSPEND_DB_BASE, array('USER' => $user['uid'], $entity['DB']));
        }

        return $this->reply($result['status'], $result['data']);
    }

    public function massiveDeleteExecute(Request $request)
    {
        $user   = $this->getLoggedUser();
        $_entities = $request->getParameter('entities');

        foreach($_entities as $entity){
            $result = Vesta::execute(Vesta::V_DEL_DB_BASE, array('USER' => $user['uid'], $entity['DB']));
        }

        return $this->reply($result['status'], $result['data']);
    }
       
}