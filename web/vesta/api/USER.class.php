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
            $fullname    = $users[$fullname_id];

            $reply[$user] = array(
                                "LOGIN_NAME"            => $user,
                                "FULLNAME"              => $fullname,                                     // TODO skid
                                "PACKAGE"               => $details['PACKAGE'],
                                "WEB_DOMAINS"           => $details['WEB_DOMAINS'],
                                "WEB_SSL"               => $details['WEB_SSL'],
                                "WEB_ALIASES"           => $details['WEB_ALIASES'],
                                "DATABASES"             => $details['DATABASES'],
                                "MAIL_DOMAINS"          => $details['MAIL_DOMAINS'],
                                "MAIL_BOXES"            => $details['MAIL_BOXES'],
                                "MAIL_FORWARDERS"       => $details['MAIL_FORWARDERS'],
                                "DNS_DOMAINS"           => $details['DNS_DOMAINS'],
                                "DISK_QUOTA"            => $details['DISK_QUOTA'],//$disk_quota,
                                "BANDWIDTH"             => $details['BANDWIDTH'],//$bandwidth,                                   
                                "NS_LIST"               => array($details['NS1'], $details['NS2']),      // TODO skid
                                "SHELL"                 => $details['"SHELL'],
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
                    'USER'     => $spell['USER'],
                    'PASSWORD' => $spell['PASSWORD'],
                    'EMAIL'    => $spell['EMAIL'],
                    'ROLE'     => $spell['ROLE'],
                    'OWNER'    => $user['uid'],
                    'PACKAGE'  => $spell['PACKAGE'],
                    'NS1'      => $spell['NS1'],
                    'NS2'      => $spell['NS2']
                  );
    
        $result = Vesta::execute(Vesta::V_ADD_SYS_USER, $params);
      
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
    public function delExecute($_spell = false) 
    {
        $r = new Request();
        if ($_spell) {
            $_s = $_spell;
        }
        else {
            $_s = $r->getSpell();
        }

        $_user = 'vesta';
        $params = array(
                    'USER' => $_s['USER']
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
    public function changeExecute($request)
    {
        $r = new Request();
        $_s = $r->getSpell();
        $_old = $_s['old'];
        $_new = $_s['new'];

        $_USER = $_new['USER'];
    
        if ($_old['USER'] != $_new['USER']) {
            $result = array();
            // creating new user
            $result = $this->addExecute($_new);    
            // deleting old
            if ($result['status']) {
                $result = array();
    
                $result = $this->delExecute($_old);
                return $this->reply($this->status, '');
            }
        }

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
  
        if ($_old['EMAIL'] != $_new['EMAIL']) {
            $result = array();
            $result = Vesta::execute(Vesta::V_CHANGE_SYS_USER_CONTACT, array('USER' => $_USER, 'EMAIL' => $_new['EMAIL']));
            if (!$result['status']) {
                $this->status = FALSE;
                $this->errors['EMAIL'] = array($result['error_code'] => $result['error_message']);
            }
        }

        if ($_old['NS1'] != $_new['NS1']  || $_old['NS2'] != $_new['NS2']) {
            $result = array();
            $result = Vesta::execute(Vesta::V_CHANGE_SYS_USER_NS, array('USER' => $_USER, 'NS1' => $_new['NS1'], 'NS2' => $_new['NS2']));
            if (!$result['status']) {
                $this->status = FALSE;
                $this->errors['NS'] = array($result['error_code'] => $result['error_message']);
            }
        }

        if ($_old['SHELL'] != $_new['SHELL']) {
            $result = array();
            $result = Vesta::execute(Vesta::V_CHANGE_SYS_USER_SHELL, array('USER' => $_USER, 'SHELL' => $_new['SHELL']));
            if (!$result['status']) {
                $this->status = FALSE;
                $this->errors['SHELL'] = array($result['error_code'] => $result['error_message']);
            }
        }

        if (!$this->status) {
            Vesta::execute(Vesta::V_CHANGE_SYS_USER_PASSWORD, array('USER' => $_USER, 'PASSWORD' => $_old['PASSWORD']));
            Vesta::execute(Vesta::V_CHANGE_SYS_USER_PACKAGE, array('USER' => $_USER, 'PACKAGE' => $_old['PACKAGE']));
            Vesta::execute(Vesta::V_CHANGE_SYS_USER_CONTACT, array('USER' => $_USER, 'EMAIL' => $_old['EMAIL']));
            Vesta::execute(Vesta::V_CHANGE_SYS_USER_NS, array('USER' => $_USER, 'NS1' => $_old['NS1'], 'NS2' => $_old['NS2']));
            Vesta::execute(Vesta::V_CHANGE_SYS_USER_SHELL, array('USER' => $_USER, 'SHELL' => $_old['SHELL']));
        }

        return $this->reply($this->status, '');
    }
}
