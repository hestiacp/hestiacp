<?php
/**
 * USERS 
 * 
 * @author vesta, http://vestacp.com/
 * @author Dmitry Malishev <dima.malishev@gmail.com>
 * @author Dmitry Naumov-Socolov <naumov.socolov@gmail.com>
 * @copyright vesta 2010-2011
 */
 
class USER extends AjaxHandler 
{
    
    /**
     * Get USER entries
     * 
     * @param Request $request
     * @return string - Ajax Reply
     */
    public function getListExecute(Request $request) 
    {
        if(!VestaSession::getUserRole()){
          return self::getUserExecute($request);
        }

        $reply  = array();
        $result = Vesta::execute(Vesta::V_LIST_SYS_USERS, array(Config::get('response_type')));

        foreach ($result['data'] as $user => $details) {
            $user_details = array(
                                "FNAME"                 => $details['FNAME'],
                                "LNAME"                 => $details['LNAME'],
                                "LOGIN_NAME"            => $user,
                                "FULLNAME"              => $details['FNAME'].' '.$details['LNAME'],                               
                                "PACKAGE"               => $details['PACKAGE'],
                                "WEB_DOMAINS"           => $details['WEB_DOMAINS'],
                                "WEB_SSL"               => $details['WEB_SSL'],
                                "WEB_ALIASES"           => $details['WEB_ALIASES'],
                                "DATABASES"             => $details['DATABASES'],
                                "MAIL_DOMAINS"          => $details['MAIL_DOMAINS'],
                                "MAIL_BOXES"            => $details['MAIL_BOXES'],
                                "MAIL_FORWARDERS"       => $details['MAIL_FORWARDERS'],
                                "DNS_DOMAINS"           => $details['DNS_DOMAINS'],
                                "DISK_QUOTA"            => $details['DISK_QUOTA'],
                                "BANDWIDTH"             => $details['BANDWIDTH'],                                
                                "SHELL"                 => $details['SHELL'],
                                "BACKUPS"               => $details['BACKUPS'],
                                "WEB_TPL"               => $details['WEB_TPL'],
                                "SUSPEND"               => $details['SUSPENDED'],
                                "CONTACT"               => $details['CONTACT'],
                                //                                "REPORTS"               => $details['REPORTS'],
                                "REPORTS_ENABLED"       => $details['REPORTS'],
                                "IP_OWNED"              => $details['IP_OWNED'],
                                "U_DIR_DISK"            => $details['U_DIR_DISK'],
                                "U_DISK"                => $details['U_DISK'],
                                "U_BANDWIDTH"           => $details['U_BANDWIDTH'],
                                "U_WEB_DOMAINS"         => $details['U_WEB_DOMAINS'],
                                "U_WEB_SSL"             => $details['U_WEB_SSL'],
                                "U_DNS_DOMAINS"         => $details['U_DNS_DOMAINS'],
                                "U_DATABASES"           => $details['U_DATABASES'],
                                "U_MAIL_DOMAINS"        => $details['U_MAIL_DOMAINS'],
                                "DATE"                  => $details['DATE'],
                                "U_MAIL_FORWARDERS"     => '0',
                                "U_MAIL_BOXES"          => '0',
                                "U_CRON_JOBS"           => $details['U_CRON_JOBS'],
                                "IP_OWNED"              => $details['IP_OWNED'],
                                "NGINX_EXT"				=> $details['"NGINX_EXT']
                            );
            $nses = $this->getNS($user, $details);
            $reply[$user] = array_merge($user_details, $nses);
            //            $reply[$user] = $user_details;
        }

        return $this->reply(TRUE, $reply);
    }

    public function getUserExecute(Request $request) 
    {
        $user = $this->getLoggedUser();

        $reply  = array();
        $result = Vesta::execute(Vesta::V_LIST_SYS_USER, array($user['uid'], Config::get('response_type')));

        foreach ($result['data'] as $user => $details) {
            $user_details = array(
                                "FNAME"                 => $details['FNAME'],
                                "LNAME"                 => $details['LNAME'],
                                "LOGIN_NAME"            => $user,
                                "FULLNAME"              => $details['FNAME'].' '.$details['LNAME'],                               
                                "PACKAGE"               => $details['PACKAGE'],
                                "WEB_DOMAINS"           => $details['WEB_DOMAINS'],
                                "WEB_SSL"               => $details['WEB_SSL'],
                                "WEB_ALIASES"           => $details['WEB_ALIASES'],
                                "DATABASES"             => $details['DATABASES'],
                                "MAIL_DOMAINS"          => $details['MAIL_DOMAINS'],
                                "MAIL_BOXES"            => $details['MAIL_BOXES'],
                                "MAIL_FORWARDERS"       => $details['MAIL_FORWARDERS'],
                                "DNS_DOMAINS"           => $details['DNS_DOMAINS'],
                                "DISK_QUOTA"            => $details['DISK_QUOTA'],
                                "BANDWIDTH"             => $details['BANDWIDTH'],                                
                                "NS"                    => $details['NS'],
                                "SHELL"                 => $details['SHELL'],
                                "BACKUPS"               => $details['BACKUPS'],
                                "WEB_TPL"               => $details['WEB_TPL'],
                                "SUSPEND"               => $details['SUSPENDED'],
                                "CONTACT"               => $details['CONTACT'],
                                "REPORTS"               => $details['REPORTS'],
                                "IP_OWNED"              => $details['IP_OWNED'],
                                "U_DIR_DISK"            => $details['U_DIR_DISK'],
                                "U_DISK"                => $details['U_DISK'],
                                "U_BANDWIDTH"           => $details['U_BANDWIDTH'],
                                "U_WEB_DOMAINS"         => $details['U_WEB_DOMAINS'],
                                "U_WEB_SSL"             => $details['U_WEB_SSL'],
                                "U_DNS_DOMAINS"         => $details['U_DNS_DOMAINS'],
                                "U_DATABASES"           => $details['U_DATABASES'],
                                "U_MAIL_DOMAINS"        => $details['U_MAIL_DOMAINS'],
                                "U_CRON_JOBS"           => 'todo',
                                "IP_OWNED"              => $details['IP_OWNED'],
                                "DATE"                  => $details['DATE']
                            );
            $nses = $this->getNS($user, $details);
            $reply[$user] = array_merge($user_details, $nses);
            //            $reply[$user] = $user_details;
        }

        return $this->reply(TRUE, $reply);
    }

    /**
     * Add USER entry
     * 
     * @param Request $request
     * @return string - Ajax Reply
     */
    public function addExecute(Request $request) 
    {
        $_s  = $request->getParameter('spell');
        $user   = $this->getLoggedUser(); 
        $params = array(
                    'USER'     => $_s['LOGIN_NAME'],
                    'PASSWORD' => $_s['PASSWORD'],
                    'EMAIL'    => $_s['CONTACT'],
                    'PACKAGE'  => $_s['PACKAGE'],
                    'FNAME'    => $_s['FNAME'],
                    'LNAME'    => $_s['LNAME']
                  );
           
        $result = Vesta::execute(Vesta::V_ADD_SYS_USER, $params);      
        // Reports
        //        $enable_reports = Utils::getCheckboxBooleanValue($spell['REPORTS_ENABLED']);
        //        $reports_result = $this->setUserReports($spell['LOGIN_NAME'], $spell['REPORTS_ENABLED']);              
        // Set SHELL
        //        $this->setShell($_s['LOGIN_NAME'], $_s['SHELL']);

        if (!$result['status']) {
            $this->errors[] = array($result['error_code'] => $result['error_message']);
        }

 		if(@Utils::getCheckboxBooleanValue($_s['REPORTS_ENABLED'])){
           $result = Vesta::execute(Vesta::V_ADD_SYS_USER_REPORTS, array('USER' => $_USER));
            if (!$result['status']) {
                $this->status = FALSE;
                $this->errors['REPORTS'] = array($result['error_code'] => $result['error_message']);
            }
        }


        if ($_s['SUSPEND'] == 'on') {
            if($result['status']){
                $result = array();

                $result = Vesta::execute(Vesta::V_SUSPEND_SYS_USER,  array('USER' => $user['uid'], 'USER' => $_s['LOGIN_NAME']));
                if (!$result['status']) {
                    $this->status = FALSE;
                    $this->errors['SUSPEND'] = array($result['error_code'] => $result['error_message']);
                }   
            }
        }

        return $this->reply($result['status'], $result['data']);
    }
  
    /**
     * Delete USER entry
     * 
     * @param Request $request
     * @return string - Ajax Reply
     */
    public function deleteExecute(Request $request) 
    {
        $user  = $this->getLoggedUser();
        $spell = $request->getParameter('spell');
        $params = array(
                    'USER' => $spell['LOGIN_NAME']
                  );

        $result = Vesta::execute(Vesta::V_DEL_SYS_USER, $params);
    
        if (!$result['status']) {
            $this->errors[] = array($result['error_code'] => $result['error_message']);
        }
    
        return $this->reply($result['status'], $result['data']);
    }

    /**
     * Change USER entry
     * 
     * @param Request $request
     * @return string - Ajax Reply
     */
    public function changeExecute(Request $request)
    {
        $_new = $request->getParameter('new');
        $_old = $request->getParameter('old');

        $_USER = $_old['LOGIN_NAME'];


		$result = array();
		if(@Utils::getCheckboxBooleanValue($_new['SUSPEND'])){
            $result = Vesta::execute(Vesta::V_SUSPEND_SYS_USER, array('USER' => $_USER));
			return $this->reply($result['status'], $result['error_message']);
		}
		elseif(@Utils::getCheckboxBooleanValue($_old['SUSPEND'])){
            $result = Vesta::execute(Vesta::V_UNSUSPEND_SYS_USER, array('USER' => $_USER));
    		if (!$result['status']) {
    			$this->status = FALSE;
    			$this->errors['UNSUSPEND'] = array($result['error_code'] => $result['error_message']);
    			return $this->reply($result['status'], $result['error_message']);
        	}
		}

        if (!empty($_new['PASSWORD']) && $_new['PASSWORD'] != Vesta::SAME_PASSWORD) {
            $result = array();
            $result = Vesta::execute(Vesta::V_CHANGE_SYS_USER_PASSWORD, array('USER' => $_USER, 'PASSWORD' => $_new['PASSWORD']));
            if (!$result['status']) {
                $this->status = FALSE;
                $this->errors['PASSWORD'] = array($result['error_code'] => $result['error_message']);
            }
        }

        if ($_old['PACKAGE'] != $_new['PACKAGE']) {
            $result = array();
            $result = Vesta::execute(Vesta::V_CHANGE_SYS_USER_PACKAGE, array('USER' => $_USER, 'PACKAGE' => $_new['PACKAGE']));
            if (!$result['status']) {
                $this->status = FALSE;
                $this->errors['PACKAGE'] = array($result['error_code'] => $result['error_message']);
            }
        }
  
        if ($_old['CONTACT'] != $_new['CONTACT']) {
            $result = array();
            $result = Vesta::execute(Vesta::V_CHANGE_SYS_USER_CONTACT, array('USER' => $_USER, 'EMAIL' => $_new['CONTACT']));
            if (!$result['status']) {
                $this->status = FALSE;
                $this->errors['EMAIL'] = array($result['error_code'] => $result['error_message']);
            }
        }

        if ($_old['REPORTS_ENABLED'] != $_new['REPORTS_ENABLED']) {
            $result = array();
    		if(@Utils::getCheckboxBooleanValue($_new['REPORTS_ENABLED'])){
                $result = Vesta::execute(Vesta::V_ADD_SYS_USER_REPORTS, array('USER' => $_USER));
            }
            else{
              $result = Vesta::execute(Vesta::V_DEL_SYS_USER_REPORTS, array('USER' => $_USER));
            }
            if (!$result['status']) {
                $this->status = FALSE;
                $this->errors['REPORTS'] = array($result['error_code'] => $result['error_message']);
            }
        }

        // Set SHELL
        if($_new['SHELL'] != $_old['SHELL']){
            $this->setShell($_USER, $_new['SHELL']);
        }
    
        if($_new[NS1].' '.$_new[NS2].' '.$_new[NS3].' '.$_new[NS4].' '.$_new[NS5].' '.$_new[NS6].' '.$_new[NS7].' '.$_new[NS8] != 
           $_old[NS1].' '.$_old[NS2].' '.$_old[NS3].' '.$_old[NS4].' '.$_old[NS5].' '.$_old[NS6].' '.$_old[NS7].' '.$_old[NS8]){
            $this->setNSentries($_USER, $_new); 
        }

        if($_new['FNAME'] != $_old['FNAME'] || $_new['LNAME'] != $_old['LNAME']){
            $names = array(
                    'USER'  => $_USER,
                    'FNAME' => $_new['FNAME'],
                    'LNAME' => $_new['LNAME']
                 );
 
            $result = Vesta::execute(Vesta::V_CHANGE_SYS_USER_NAME, $names);
            if (!$result['status']) {
                $this->status = FALSE;
                $this->errors['NAMES'] = array($result['error_code'] => $result['error_message']);
            }        
        }

        if (!$this->status) {
            Vesta::execute(Vesta::V_CHANGE_SYS_USER_PASSWORD, array('USER' => $_USER, 'PASSWORD' => $_old['PASSWORD']));
            Vesta::execute(Vesta::V_CHANGE_SYS_USER_PACKAGE,  array('USER' => $_USER, 'PACKAGE'  => $_old['PACKAGE']));
            Vesta::execute(Vesta::V_CHANGE_SYS_USER_CONTACT,  array('USER' => $_USER, 'EMAIL'    => $_old['EMAIL']));
            //            Vesta::execute(Vesta::V_CHANGE_SYS_USER_NS,       array('USER' => $_USER, 'NS1'      => $_old['NS1'], 'NS2' => $_old['NS2']));
            Vesta::execute(Vesta::V_CHANGE_SYS_USER_SHELL,    array('USER' => $_USER, 'SHELL'    => $_old['SHELL']));
        }

        return $this->reply($this->status, '');
    }

    protected function setUserReports($user, $enabled)
    {
        if ($enabled == 'off') {
            $result = Vesta::execute(Vesta::V_DEL_SYS_USER_REPORTS, array('USER' => $user));
        }
        else {
            $result = Vesta::execute(Vesta::V_ADD_SYS_USER_REPORTS, array('USER' => $user));
        }

        return $result['status'];
    }

    protected function setNSentries($user, $data)
    {
        $ns = array();
        $ns['USER'] = $user;
        $ns['NS1']  = $data['NS1'];
        $ns['NS2']  = $data['NS2'];
        $ns['NS3']  = isset($data['NS3']) ? $data['NS3'] : '';
        $ns['NS4']  = isset($data['NS4']) ? $data['NS4'] : '';
        $ns['NS5']  = isset($data['NS5']) ? $data['NS5'] : '';
        $ns['NS6']  = isset($data['NS6']) ? $data['NS6'] : '';
        $ns['NS7']  = isset($data['NS7']) ? $data['NS7'] : '';
        $ns['NS8']  = isset($data['NS8']) ? $data['NS8'] : '';

        $result = Vesta::execute(Vesta::V_CHANGE_SYS_USER_NS, $ns);

        return $result['status'];
    }
    
    protected function getNS($user, $data)
    {
        $result  = array();
        $ns_str  = $data['NS'];
        $ns_list = explode(',', $ns_str);
        
        foreach (range(0, 7) as $index) {
            $result['NS'.($index + 1)] = @trim(@$ns_list[$index]);
        }
        
        return $result;
    }

    /**
     * TODO: handle result set errors
     */
    protected function setShell($user, $shell)
    {
        $result = Vesta::execute(Vesta::V_CHANGE_SYS_USER_SHELL, array('USER' => $user, 'SHELL' => $shell));
    }


    public function massiveSuspendExecute(Request $request)
    {
        $_entities = $request->getParameter('entities');

        foreach($_entities as $entity){
            $result = Vesta::execute(Vesta::V_SUSPEND_SYS_USER, array('USER' => $entity['LOGIN_NAME']));
        }

        return $this->reply($result['status'], $result['data']);
    }

    public function massiveUnsuspendExecute(Request $request)
    {
        $_entities = $request->getParameter('entities');

        foreach($_entities as $entity){
            $result = Vesta::execute(Vesta::V_UNSUSPEND_SYS_USER, array('USER' => $entity['LOGIN_NAME']));
        }

        return $this->reply($result['status'], $result['data']);
    }

    public function massiveDeleteExecute(Request $request)
    {
        $_entities = $request->getParameter('entities');

        foreach($_entities as $entity){
            $result = Vesta::execute(Vesta::V_DEL_SYS_USER, array('USER' => $entity['LOGIN_NAME']));
        }

        return $this->reply($result['status'], $result['data']);
    }

    public function loginAsExecute(Request $request)
    {
        $_user = $request->getParameter('user');

        if(Vesta::hasRights(VestaSession::getInstance()->getUserRole(), 'login_as'))
          {
            VestaSession::loginAs($_user);
            return $this->reply(TRUE, '');
          }

        return $this->reply(FALSE, '');
    }

    public function logoutAsExecute(Request $request)
    {
        VestaSession::logoutAs();
        return $this->reply(TRUE, '');
    }
    

}
