<?php

/**
 * Main entity class
 * Provides usefull methods (utils), shared for sub entities (DNS, IP etc)
 * Subentities should be extended from MAIN class
 * 
 * Details:
 *  - methods, used for ajax executions must be postfixed with execute keyword
 *      Eg.: getDnsInformationExecute()
 * 
 * @author vesta, http://vestacp.com/
 * @author Dmitry Malishev <dima.malishev@gmail.com>
 * @author Dmitry Naumov-Socolov <naumov.socolov@gmail.com>
 * @copyright vesta 2010-2011
 */
class MAIN extends AjaxHandler 
{

    protected $templates = null;

    public function aboutExecute($request)
    {
        // defaults
        $about  = array('version' => '0', 'company_email' => 'support@vestacp.com', 
                        'version_name' => 'OGRE-23-1', 'company_name' => 'vestacp.com');
        // real data
        $config = Vesta::execute(Vesta::V_LIST_SYS_CONFIG, 'json');
        if (!empty($config['data']) && !empty($config['data']['config'])) {
            $config = $config['data']['config'];
            $about['version'] = $config['VERSION'];
            $about['version_name']  = $config['VERSION_NAME'];
            $about['company_email'] = $config['COMPANY_EMAIL'];
            $about['company_name']  = $config['COMPANY_NAME'];
        }
        
        return $this->reply(true, $about);
    }

    public function requestPasswordExecute($request)
    {        
        if (empty($_SESSION['captcha_key']) 
                || $_SESSION['captcha_key'] != $request->getParameter('captcha')) {
            return $this->reply(false, null, 'Captcha is invalid ');
        }
        
        $users = Vesta::execute(Vesta::V_LIST_SYS_USERS, 'json');
        $email_matched_count = array();
        
        if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/",$request->getParameter('email'))) {
            return $this->reply(false, null, 'Email is invalid');
        }
        
        foreach ($users['data'] as $user) {           
            if ($user['CONTACT'] == trim($request->getParameter('email'))) {
                $email_matched_count[] = $user;
            }
        }
        
        if (empty($email_matched_count)) {
            return $this->reply(false, null, 'There is no such user.');
        }

        foreach ($email_matched_count as $reset_user) {
            
            $secret_key = $reset_user['RKEY'];
            $reset_link = 'https://'.$_SERVER['HTTP_HOST'].'/change_password.php?v='.$secret_key;
            
            $mail_body = <<<MAIL
                <div lang="en" style="background-color:#fff;color:#222">  
                    <a target="_blank" href="" style="color:#FFF">
                        <img width="81" height="22" style="display:block;border:0" src="http://vestacp.com/i/logo.png" alt="Twitter">
                    </a>  
                    <div style="font-family:'Helvetica Neue', Arial, Helvetica, sans-serif;font-size:13px;margin:14px">
                    <h2 style="font-family:'Helvetica Neue', Arial, Helvetica, sans-serif;margin:0 0 16px;font-size:18px;font-weight:normal">
                        Vesta received a request to reset the password for your account {$reset_user['FNAME']} {$reset_user['LNAME']}?
                    </h2>
                    <p>
                        If you want to reset your password, click on the link below (or copy and paste the URL into your browser):<br>
                        <a target="_blank" href="{$reset_link}">{$reset_link}</a>
                    </p>
                    <p>
                        If you don't want to reset your password, please ignore this message.
                        Your password will not be reset.
                        If you have any concerns, please contact us at support@vestacp.com.
                    </p>
                    <p style="font-family:'Helvetica Neue', Arial, Helvetica, sans-serif;font-size:13px;line-height:18px;border-bottom:1px solid rgb(238, 238, 238);padding-bottom:10px;margin:0 0 10px">
                        <span style="font:italic 13px Georgia,serif;color:rgb(102, 102, 102)">VestaCP</span>
                    </p>
                    <p style="font-family:'Helvetica Neue', Arial, Helvetica, sans-serif;margin-top:5px;font-size:10px;color:#888888">
                        Please do not reply to this message; it was sent from an unmonitored email address.      
                    </p>
                    </div>
                </div>
MAIL;
            
            $headers           = 'MIME-Version: 1.0' . "\n";
            $headers           .= 'Content-type: text/html; charset=UTF-8' . "\n";
            $to                 = $request->getParameter('email');
            $subject            = 'Reset your Vesta password';
            $message            = $mail_body;
            mail($to, $subject, $message, $headers);
        }
       
        return $this->reply(true);
    }
    
    public function generateResetPasswordKey()
    {
        /*$key = sha1($_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR']);
        $key = substr($key, 0, 10) . $_SERVER['REQUEST_TIME'] . substr($key, 10, strlen($key));*/
        $user   = $this->getLoggedUser();
        $rs = Vesta::execute('v_get_user_value', array('USER' => $user['uid'], 'VALUE' => 'RKEY'));
        
        return $rs[''];
    }

    public function signinExecute($request)
    {
        $login    = $request->getParameter('login');
        $password = $request->getParameter('password');
        $ip       = $request->getUserIP();
        $result   = Vesta::execute('v_check_user_password', array('USER' => $login, 'PASSWORD' => $password, 'IP' => $ip), self::TEXT);

        if ($result['status'] == true) {
            return $this->reply(true, array('v_sd' => VestaSession::authorize($login)));
        }
        else {
            return $this->reply(false, array('error_msg' => 'Incorrect login or password'));
        }
    }
    
    public function logoffExecute($request)
    {
        VestaSession::logoff();
        return $this->reply(true);
    }
    
    public function getBackupsExecute(Request $request)
    {
		$user = VestaSession::getInstance()->getUser();
		$rs = Vesta::execute(Vesta::V_LIST_SYS_USER_BACKUPS, array('USER' => $user['uid'], 'RESPONSE' => 'json'));
		
		return $this->reply($rs['status'], @$rs['data']);
	}

    /**
     * Get Initial params.
     * Global constants / variables / configs
     * 
     * @param Request $request
     * @return string - Ajax Reply
     */ 
    public function getInitialExecute(Request $request) 
    {
        $user = VestaSession::getInstance()->getUser();
        $global_data = array();
        $totals = array(
                    'USER'       => array('total' => 0, 'blocked' => 0),
                    'WEB_DOMAIN' => array('total' => 0, 'blocked' => 0),
                    'MAIL'       => array('total' => 0),
                    'DB'         => array('total' => 0, 'blocked' => 0),
                    'DNS'        => array('total' => 0, 'blocked' => 0),
                    'IP'         => array('total' => 0, 'blocked' => 0),
                    'CRON'       => array('total' => 0, 'blocked' => 0)                
                );
    
        // users
        $rs = Vesta::execute(Vesta::V_LIST_SYS_USERS, null, self::JSON);
        $data_user = $rs['data'];
        $global_data['users'] = array();
        foreach ($data_user as $login_name => $usr) {
            $totals['USER']['total'] += 1;
            if ($usr['SUSPENDED'] != 'yes') {		
                $global_data['users'][$login_name] = $login_name;
            }
            else {
                $totals['USER']['blocked'] += 1;
            }            
        }
        // web_domains
        $rs = Vesta::execute(Vesta::V_LIST_WEB_DOMAINS, array('USER' => $user['uid']), self::JSON);
        $data_web_domain = $rs['data'];
        foreach ($data_web_domain as $web) {
            $totals['WEB_DOMAIN']['total'] += 1;
        }
        // db
        $rs = Vesta::execute(Vesta::V_LIST_DB_BASES, array('USER' => $user['uid']), self::JSON);
        $data_db = $rs['data'];
        foreach ($data_db as $db) {
            $totals['DB']['total'] += 1;            
        }
        // dns
        $rs = Vesta::execute(Vesta::V_LIST_DNS_DOMAINS, array('USER' => $user['uid']), self::JSON);
        $data_dns = $rs['data'];
        foreach ($data_dns as $dns) {
            $totals['DNS']['total'] += 1;
        }
        // ip
        $global_data['ips'] = array();
        $rs = Vesta::execute(Vesta::V_LIST_SYS_IPS, null, self::JSON);
        $data_ip = $rs['data'];
        foreach ($data_ip as $ip => $obj) {
            $totals['IP']['total'] += 1;
            $global_data['ips'][$ip] = $ip;
        }
        // cron
        $rs = Vesta::execute(Vesta::V_LIST_CRON_JOBS, array('USER' => $user['uid']), self::JSON);
        $data_cron = $rs['data'];
        foreach ($data_cron as $cron) {
            $totals['CRON']['total'] += 1;
            $cron['SUSPEND'] == 'yes' ? $totals['CRON']['blocked'] += 1 : false;
        }

        $rs1 = Vesta::execute(Vesta::V_GET_SYS_USER_VALUE, array('USER' => $user['uid'], 'KEY' => 'BANDWIDTH'));
        $bandwidth = $rs1['data'];
        $rs = Vesta::execute(Vesta::V_GET_SYS_USER_VALUE, array('USER' => $user['uid'], 'KEY' => 'DISK_QUOTA'));
        $disk_quota = $rs['data'];

        $reply = array(
                    'auth_user'  => array('uid' => $this->getLoggedUser()),
                    'user_data'  => array('BANDWIDTH' => (int)$bandwidth, 'DISK_QUOTA' => (int)$disk_quota),
                    'WEB_DOMAIN' => $this->getWebDomainParams($data_web_domain, $global_data),
                    'CRON'       => $this->getCronParams(),
                    'IP'         => $this->getIpParams($data_ip, $global_data),
                    'DNS'        => $this->getDnsParams(),
                    'DB'         => $this->getDbParams($data_db),
                    'USERS'      => $this->getUsersParams($data_user),
                    'totals'     => $totals,
                    'PROFILE'    => $user,
                    'real_user'  => $_SESSION['real_user'] ? $_SESSION['real_user'] : NULL
                );

        return $this->reply(true, $reply);
    }

    protected function getTemplates()
    {
        if (null != $this->templates) {
            return $this->templates;
        }
        else {
            $user = $this->getLoggedUser();
            $this->templates = array();
            $result = Vesta::execute(Vesta::V_LIST_WEB_TEMPLATES, array('USER' => $user['uid']), self::JSON);
            // TODO: handle errors!
            foreach ($result['data'] as $tpl => $description) {
                $this->templates[$tpl] = $description;
            }

            return $this->templates;
        }
    }
    
    /**
     * WEB DOMAIN initial params
     * 
     * @params array $data
     * @return array
     */
    public function getWebDomainParams($data, $global_data)
    {
        $user = $this->getLoggedUser();
        $ips = array();
        $result	= Vesta::execute(Vesta::V_LIST_USER_IPS, array('USER' => $user['uid']), self::JSON);
        foreach ($result['data'] as $sys_ip => $ip_data) {
            $ips[$sys_ip] = $sys_ip;
        }

        if (empty($ips)) {
            $ips['No available IP'] = 'No available IP';
        }

        return array(
                'TPL' => $this->getTemplates(),
                'ALIAS' => array(),
                'STAT'  => array(
			    'none'  => 'none',
                            'webalizer' => 'webalizer',
                            'awstats'   => 'awstats'
                          ),
                'IP' => $ips
           );
    }
    
    /**
     * CRON initial params
     * 
     * @params array $data
     * @return array
     */
    public function getCronParams($data = array())
    {
        return array();
    }
    
    /**
     * IP initial params
     * 
     * @params array $data
     * @return array
     */
    public function getIpParams($data = array(), $global_data = array())
    {
        $ifaces  = array();                                                                                                                                                                                                            
        $result = Vesta::execute(Vesta::V_LIST_SYS_INTERFACES, array(Config::get('response_type')));                                                                                                                                  
                                                                                                                                                                                                                                      
        foreach ($result['data'] as $iface) {                                                                                                                                                                                         
            $ifaces[$iface] = $iface;                                                                                                                                                                                                  
        }                
	
        return array(
                'SYS_USERS' => $global_data['users'],
                'STATUSES' => array(
                                'shared'    => 'shared',
                                'exclusive' => 'exclusive'
                              ),
                'INTERFACES' => $ifaces,
                'OWNER' => $global_data['users'],
                'MASK' => array(
                            '255.255.255.0'   => '255.255.255.0',
                            '255.255.255.128' => '255.255.255.128',
                            '255.255.255.192' => '255.255.255.192',
                            '255.255.255.224' => '255.255.255.224', 
                            '255.255.255.240' => '255.255.255.240', 
                            '255.255.255.248' => '255.255.255.248',
                            '255.255.255.252' => '255.255.255.252',
                            '255.255.255.255' => '255.255.255.255'
                          )
            );
    }
    
    /**
     * DNS initial params
     * 
     * @params array $data
     * @return array
     */
    public function getDnsParams($data = array())
    {
        $dns_templates = array();
        $user = $this->getLoggedUser();
        $this->templates = array();
        $result = Vesta::execute(Vesta::V_LIST_DNS_TEMPLATES, null, self::JSON);
        // TODO: handle errors!
        foreach ($result['data'] as $tpl => $description) {
            $dns_templates[$tpl] = $description;
        }

        return  array(
                'IP' => @$data['ips'],
                'TPL' => $dns_templates,
                'EXP' => array(),
                'SOA' => array(),
                'TTL' => array(),
                'record' => array(
                                'RECORD' => array(),
                                'RECORD_TYPE' => array('A' => 'A', 'NS' => 'NS', 'MX' => 'MX', 'TXT' => 'TXT'),
                                'RECORD_VALUE' => array()
                            )
            );
    }
    
    /**
     * DB initial params
     * 
     * @params array $data
     * @return array
     */
    public function getDbParams($data = array())
    {
        $db_types = $this->getDBTypes();
        return array(
                    'TYPE' => $db_types,
                    'HOST' => array('vestacp.com' => 'vestacp.com', 'askcow.org' => 'askcow.org')
                );
    }
    
    public function getDBTypes()
    {
        return array('mysql' => 'mysql', 'postgre' => 'postgre');
    }
    
    /**
     * Users initial params
     * 
     * @params array $data
     * @return array
     */
    public function getUsersParams($data = array(), $global_data = array())
    {        
        $pckg = array();
        // json
        $result = Vesta::execute(Vesta::V_LIST_USER_PACKAGES, null, self::JSON);        
        foreach ($result['data'] as $pckg_name => $pckg_data) {
            $pckg[$pckg_name] = $pckg_name;
        }
        return array(
                'PACKAGE'   => $pckg,
                'SHELL'     => array(
                                'sh'       => 'sh',
                                'bash'     => 'bash',
                                'nologin'  => 'nologin',
                                'tcsh'     => 'tcsh',
                                'csh'      => 'csh')
                );
    }
}
