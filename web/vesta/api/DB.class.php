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
                                'SUSPEND'   => $record['SUSPEND'],
                                'DATE'      => date(Config::get('ui_date_format', strtotime($record['DATE'])))
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
                    'TYPE'          => $_s['TYPE']
                  );
        // TODO: do not user it. Will be used in later releases         
        /*if ($_s['HOST']) {
            $params['HOST'] = $_s['HOST'];
        }*/   
            
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
        $_s     = $request->getParameter('new');
        $user  = $this->getLoggedUser();
        $result = array();
        $params = array(
                    'USER'      => $user['uid'],
                    'DB'        => $_s['DB'],
                    'PASSWORD'  => $_s['PASSWORD']
                  );

        $result = Vesta::execute(Vesta::V_CHANGE_DB_PASSWORD, $params);
    
        if (!$result['status']) {
            $this->errors[] = array($result['error_code'] => $result['error_message']);
        }
    
        return $this->reply($result['status'], $result['data']);
    }
       
}
