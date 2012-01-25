<?php
/*
 * CRON 
 * 
 * @author vesta, http://vestacp.com/
 * @author Dmitry Malishev <dima.malishev@gmail.com>
 * @author Dmitry Naumov-Socolov <naumov.socolov@gmail.com>
 * @copyright vesta 2010-2011
 */
 
class CRON extends AjaxHandler 
{
    /**
     * Get CRON entries
     * 
     * @param Request $request
     * @return string - Ajax Reply
     */
    public function getListExecute(Request $request) 
    {
        $user = $this->getLoggedUser();
        $reply = array();
        $result = Vesta::execute(Vesta::V_LIST_CRON_JOBS, array($user['uid'], Config::get('response_type')));
      
        foreach ($result['data'] as $id => $record) {
            $reply[$id] = array(
                            'CMD'       => $record['CMD'],
                            'MIN'       => $record['MIN'],
                            'HOUR'      => $record['HOUR'],
                            'DAY'       => $record['DAY'],
                            'MONTH'     => $record['MONTH'],
                            'WDAY'      => $record['WDAY'],
                            'SUSPEND'   => $record['SUSPEND'],
                            'DATE'      => $record['DATE'], //date(Config::get('ui_date_format', strtotime($record['DATE']))),
                            'JOB'    => $id
                          );
        }
    
        if (!$result['status']) {
            $this->errors[] = array($result['error_code'] => $result['error_message']);
        }
    
        return $this->reply($result['status'], $reply);
    }

    /**
     * Add CRON entry
     * 
     * @param Request $request
     * @return string - Ajax Reply
     */
    public function addExecute(Request $request) 
    {
        $user   = $this->getLoggedUser();
        $_s  = $request->getParameter('spell');
        $params = array(
                    'USER'      => $user['uid'],
                    'MIN'       => $_s['MIN'],
                    'HOUR'      => $_s['HOUR'],
                    'DAY'       => $_s['DAY'],
                    'MONTH'     => $_s['MONTH'],
                    'WDAY'      => $_s['WDAY'],
                    'CMD'       => $_s['CMD']
                  );
    
        $result = Vesta::execute(Vesta::V_ADD_CRON_JOB, $params);

        if ($spell['REPORTS']) {
            $result = array();
            $result = Vesta::execute(Vesta::V_ADD_SYS_USER_REPORTS, array('USER' => $user['uid']));
            if (!$result['status']) {
                $this->status            = FALSE;
                $this->errors['REPORTS'] = array($result['error_code'] => $result['error_message']);
            }
        }


        if ($_s['SUSPEND'] == 'on') {
            if($result['status']){
                $result = array();

                $result = Vesta::execute(Vesta::V_SUSPEND_CRON_JOB, array('USER' => $user['uid'], 'JOB' => $_s['CMD']));
                if (!$result['status']) {
                    $this->status = FALSE;
                    $this->errors['SUSPEND'] = array($result['error_code'] => $result['error_message']);
                }   
            }
        }


        if (!$result['status']) {
            $this->errors[] = array($result['error_code'] => $result['error_message']);
        }
    
        return $this->reply($result['status'], $result['data']);
    }
  
    /**
     * Delete CRON entry
     * 
     * @param Request $request
     * @return string - Ajax Reply
     */
    public function deleteExecute(Request $request) 
    {
        $user   = $this->getLoggedUser();
        $spell  = $request->getParameter('spell'); 
        $params = array(
                    'USER' => $user['uid'],
                    'JOB'  => $spell['JOB']
                  );
    
        $result = Vesta::execute(Vesta::V_DEL_CRON_JOB, $params);
        
        if (!$result['status']) {
            $this->errors[] = array($result['error_code'] => $result['error_message']);
        }
    
        return $this->reply($result['status'], $result['data']);
    }
  
    /**
     * Change CRON entry
     * 
     * @param Request $request
     * @return string - Ajax Reply
     */
    public function changeExecute(Request $request)
    {
        $user = $this->getLoggedUser();
        $_old   = $request->getParameter('old');
        $_new   = $request->getParameter('new');
        $result = array();


		$result = array();
		if(@Utils::getCheckboxBooleanValue($_new['SUSPEND'])){
			$result = Vesta::execute(Vesta::V_SUSPEND_CRON_JOB, array('USER' => $user['uid'], 'JOB' => $_old['JOB']));
			return $this->reply($result['status'], $result['error_message']);
		}
		elseif(@Utils::getCheckboxBooleanValue($_old['SUSPEND'])){
			$result = Vesta::execute(Vesta::V_UNSUSPEND_CRON_JOB, array('USER' => $user['uid'], 'JOB' => $_old['JOB']));
    		if (!$result['status']) {
    			$this->status = FALSE;
    			$this->errors['UNSUSPEND'] = array($result['error_code'] => $result['error_message']);
    			return $this->reply($result['status'], $result['error_message']);
        	}
		}

        $params = array(
                    'USER' => $user['uid'],
                    'JOB' => $_old['JOB'],
                    'MIN' => $_new['MIN'],
                    'HOUR' => $_new['HOUR'],
                    'DAY' => $_new['DAY'],
                    'MONTH' => $_new['MONTH'],
                    'WDAY' => $_new['WDAY'],
                    'CMD' => $_new['CMD']
                  );

        $result = Vesta::execute(Vesta::V_CHANGE_CRON_JOB, $params);

        if (!$result['status']) {
            $this->errors[] = array($result['error_code'] => $result['error_message']);
        }

                   

        return $this->reply($result['status'], $result['data']);
    }


    /**
     * Suspend CRON entry
     * 
     * @param Request $request
     * @return string - Ajax Reply
     */
    public function suspendExecute(Request $request)
    {
        $user   = $this->getLoggedUser();
        $spell  = $request->getParameter('spell');
        $params = array(
                    'USER' => $user['uid'],
                    'JOB'  => $spell['JOB']
                  );
    
        $result = Vesta::execute(Vesta::V_SUSPEND_CRON_JOB, $params);
    
        if (!$result['status']) {
            $this->errors[] = array($result['error_code'] => $result['error_message']);
        }
    
        return $this->reply($result['status'], $result['data']);
    }

    /**
     * Unsuspend CRON entry
     * 
     * @param Request $request
     * @return string - Ajax Reply
     */
    public function unsuspendExecute(Request $request)
    {        
        $user   = $this->getLoggedUser();
        $spell  = $request->getParameter('spell');
        $params = array(
                    'USER' => $user['uid'],
                    'JOB'  => $spell['JOB']
                  );

        $result = Vesta::execute(Vesta::V_UNSUSPEND_CRON_JOB, $params);
    
        if (!$result['status']) {
            $this->errors[] = array($result['error_code'] => $result['error_message']);
        }
    
        return $this->reply($result['status'], $result['data']);
    }

    public function massiveSuspendExecute(Request $request)
    {
        $user   = $this->getLoggedUser();
        $_entities = $request->getParameter('entities');

        foreach($_entities as $entity){
          $result = Vesta::execute(Vesta::V_SUSPEND_CRON_JOB, array('USER' => $user['uid'], $entity['JOB']));
        }

        return $this->reply($result['status'], $result['data']);
    }

    public function massiveUnsuspendExecute(Request $request)
    {
        $user   = $this->getLoggedUser();
        $_entities = $request->getParameter('entities');

        foreach($_entities as $entity){
            $result = Vesta::execute(Vesta::V_UNSUSPEND_CRON_JOB, array('USER' => $user['uid'], $entity['JOB']));
        }

        return $this->reply($result['status'], $result['data']);
    }

    public function massiveDeleteExecute(Request $request)
    {
        $user   = $this->getLoggedUser();
        $_entities = $request->getParameter('entities');

        foreach($_entities as $entity){
            $result = Vesta::execute(Vesta::V_DEL_CRON_JOB, array('USER' => $user['uid'], $entity['JOB']));
        }

        return $this->reply($result['status'], $result['data']);
    }
   
}
