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
        $reply  = array();
        $result = Vesta::execute(Vesta::V_LIST_SYS_USERS, array(Config::get('response_type')));
        $users = array('Han Solo', 'Darth Vader', 'Jabba the Hutt', 'Boba Fett', 'Jango Fett', ' Aurra Sing', 'Padme', 
                       'Tusken Raider', 'General Grievous', 'Wedge Antilles', 'Padme Amidala', 'Bib Fortuna',   'Kyle Katarn', 
                       'Quinlan Vos', 'Princess Leia', 'Obi-Wan Kenobi', 'Han Solo', 'Hondo Ohnaka', 'Noa Briqualon', 'C3P0', 
                       'R2-D2', 'Quinlan Vos', 'Mara Jade' , 'Luke Skywalker', 'Luke Skywalker' , 'Luke Skywalker'
                 );

        foreach ($result['data'] as $user => $details) {
            $fullname_id = rand(0, count($users)-1);
            $fullname    = implode('', array($details['FNAME'], ' ', $details['LNAME']));
        
            $nses = $this->getNS($user, $details);
            $user_details = array(
                                "FNAME"                    => $details['FNAME'],
                                "LNAME"                    => $details['LNAME'],
                                "LOGIN_NAME"            => $user,
                                "FULLNAME"              => $fullname,                               
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
                                "MAX_CHILDS"            => $details['MAX_CHILDS'],
                                "SUSPENDED"             => $details['SUSPENDED'],
                                "OWNER"                 => $details['OWNER'],
                                "ROLE"                  => $details['ROLE'],
                                "IP_OWNED"              => $details['IP_OWNED'],
                                "U_CHILDS"              => $details['U_CHILDS'],
                                "U_DISK"                => $details['U_DISK'],//$u_disk,
                                "U_BANDWIDTH"           => $details['U_BANDWIDTH'],//$u_bandwidth, 
                                "U_WEB_DOMAINS"         => $details['U_WEB_DOMAINS'],
                                "U_WEB_SSL"             => $details['U_WEB_SSL'],
                                "U_DNS_DOMAINS"         => $details['U_DNS_DOMAINS'],
                                "U_DATABASES"           => $details['U_DATABASES'],
                                "U_MAIL_DOMAINS"        => $details['U_MAIL_DOMAINS'],
                                "CONTACT"               => $details['CONTACT'],
                                "DATE"                  => $details['DATE'],
                                "U_MAIL_BOXES"          => rand(1, 10),  // TODO: skid
                                "U_MAIL_FORWARDERS"     => rand(1, 10),  // TODO: skid
                                "REPORTS_ENABLED"       => 'enabled'     // TODO: skid
                            );
            $reply[$user] = array_merge($user_details, $nses);
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
        $spell  = $request->getParameter('spell');
        $user   = $this->getLoggedUser(); 
        $params = array(
                    'USER'     => $spell['LOGIN_NAME'],
                    'PASSWORD' => $spell['PASSWORD'],
                    'EMAIL'    => $spell['CONTACT'],
                    'ROLE'     => $spell['ROLE'],
                    'OWNER'    => $user['uid'],
                    'PACKAGE'  => $spell['PACKAGE'],
                    'FNAME'    => $spell['FNAME'],
                    'LNAME'    => $spell['LNAME']
                  );
    
        $result = Vesta::execute(Vesta::V_ADD_SYS_USER, $params);      
        // Reports
        $enable_reports = Utils::getCheckboxBooleanValue($spell['REPORTS_ENABLED']);
        $reports_result = $this->setUserReports($spell['LOGIN_NAME'], $spell['REPORTS_ENABLED']);
        // NS
        $ns_result = $this->setNSentries($spell['LOGIN_NAME'], $spell);
    
        if (!$result['status']) {
            $this->errors[] = array($result['error_code'] => $result['error_message']);
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

        if ($_old['PASSWORD'] != $_new['PASSWORD']) {
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

        $this->setNSentries($_USER, $_new);

        $names = array(
                'USER'  => $_USER,
                'NAME'  => $_new['LOGIN_NAME'],
                'FNAME' => $_new['FNAME'],
                'LNAME' => $_new['LNAME']
             );
        $result = Vesta::execute(Vesta::V_CHANGE_SYS_USER_NAME, $names);
        if (!$result['status']) {
            $this->status = FALSE;
            $this->errors['NAMES'] = array($result['error_code'] => $result['error_message']);
        }

        /*if ($_old['SHELL'] != $_new['SHELL']) {
            $result = array();
            $result = Vesta::execute(Vesta::V_CHANGE_SYS_USER_SHELL, array('USER' => $_USER, 'SHELL' => $_new['SHELL']));
            if (!$result['status']) {
                $this->status = FALSE;
                $this->errors['SHELL'] = array($result['error_code'] => $result['error_message']);
            }
        }*/

        if (!$this->status) {
            Vesta::execute(Vesta::V_CHANGE_SYS_USER_PASSWORD, array('USER' => $_USER, 'PASSWORD' => $_old['PASSWORD']));
            Vesta::execute(Vesta::V_CHANGE_SYS_USER_PACKAGE,  array('USER' => $_USER, 'PACKAGE'  => $_old['PACKAGE']));
            Vesta::execute(Vesta::V_CHANGE_SYS_USER_CONTACT,  array('USER' => $_USER, 'EMAIL'    => $_old['EMAIL']));
            Vesta::execute(Vesta::V_CHANGE_SYS_USER_NS,       array('USER' => $_USER, 'NS1'      => $_old['NS1'], 'NS2' => $_old['NS2']));
            Vesta::execute(Vesta::V_CHANGE_SYS_USER_SHELL,    array('USER' => $_USER, 'SHELL'    => $_old['SHELL']));
        }

        return $this->reply($this->status, '');
    }

    protected function setUserReports($user, $enabled)
    {
        if ($enabled === true) {
            $result = Vesta::execute(Vesta::V_ADD_SYS_USER_REPORTS, array('USER' => $user));
        }
        else {
            $result = Vesta::execute(Vesta::V_DEL_SYS_USER_REPORTS, array('USER' => $user));
        }

        return $result['status'];
    }

    protected function setNSentries($user, $data)
    {
        $ns = array();
        $ns['USER'] = $user;
        $ns['NS1'] = $data['NS1'];
        $ns['NS2'] = $data['NS2'];
        $ns['NS3'] = isset($data['NS3']) ? $data['NS3'] : '';
        $ns['NS4'] = isset($data['NS4']) ? $data['NS4'] : '';
        $ns['NS5'] = isset($data['NS5']) ? $data['NS5'] : '';
        $ns['NS6'] = isset($data['NS6']) ? $data['NS6'] : '';
        $ns['NS7'] = isset($data['NS7']) ? $data['NS7'] : '';
        $ns['NS8'] = isset($data['NS8']) ? $data['NS8'] : '';

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

}
