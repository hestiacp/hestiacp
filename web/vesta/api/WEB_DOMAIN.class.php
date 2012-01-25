<?php
/**
 * DOMAIN
 * 
 * @author vesta, http://vestacp.com/
 * @author Dmitry Malishev <dima.malishev@gmail.com>
 * @author Dmitry Naumov-Socolov <naumov.socolov@gmail.com>
 * @copyright vesta 2010-2011
 */
class WEB_DOMAIN extends AjaxHandler 
{

    public function getListExecute(Request $request) 
    {
        $user = $this->getLoggedUser();
        $reply = array();

        $result = Vesta::execute(Vesta::V_LIST_WEB_DOMAINS, array('USER' => $user['uid']), self::JSON);

        $stat = array();
        $result_stat = Vesta::execute(Vesta::V_LIST_WEB_DOMAINS_STATS, array('USER' => $user['uid']), self::JSON);

        foreach ($result_stat['data'] as $w_d => $w_d_details) {
            $stat[$w_d] = $w_d_details;
        }
        foreach($result['data'] as $web_domain => $record)
        {
            $web_details = array(
                              'IP'          => $record['IP'],
                              'U_DISK'      => (int)$record['U_DISK'],
                              'U_BANDWIDTH' => (int)$record['U_BANDWIDTH'],
                              'TPL'         => $record['TPL'],
                              'ALIAS'       => @str_replace(",", ", ", $record['ALIAS']),
                              'PHP'         => $record['PHP'],
                              'CGI'         => $record['CGI'],
                              'ELOG'        => $record['ELOG'],
                              'STAT'        => $record['STATS'],
                              'STATS_LOGIN' => $record['STATS_AUTH'],
                              'SSL'         => $record['SSL'] == 'yes' ? 'on' : 'off',
                              'SSL_HOME'    => $record['SSL_HOME'] == 'same' ? 'off' : 'on',
                              'SSL_CRT'     => '',
                              'SSL_KEY'		=> '',
                              'SSL_CA'		=> '',
                              'NGINX'       => $record['NGINX'],
                              'NGINX_EXT'   => $record['NGINX_EXT'],
                              'SUSPEND'     => $record['SUSPEND'], // == 'yes' ? 'on' : 'off',
                              'DATE'        => $record['DATE'] //date(Config::get('ui_date_format', strtotime($record['DATE'])))
                          );
            $web_details['STAT'] == '' ? $web_details['STAT'] = 'none' : true;

            if($record['SSL'] == 'yes'){
                $result_ssl = Vesta::execute(Vesta::V_LIST_WEB_DOMAIN_SSL, array('USER' => $user['uid'], 'DOMAIN' => $web_domain, self::JSON));

                if($result_ssl['status']){
                    foreach ($result_ssl['data'][$web_domain] as $key => $value) {
                        $web_details['SSL_'.$key] = $value;
                    }
                }
            }
            
            $reply[$web_domain] = $web_details;
        }

        if (!$result['status']) {
            $this->errors[] = array($result['error_code'] => $result['error_message']);
        }

        return $this->reply($result['status'], $reply);
    }
    
    
    
    public function addExecute(Request $request) 
    {
        $_s = $request->getParameter('spell');
        $user = $this->getLoggedUser();

        $params = array(
                      'USER'   => $user['uid'],
                      'DOMAIN' => $_s['DOMAIN'],
                      'IP'     => $_s['IP']
                  );
        
        $result = Vesta::execute(Vesta::V_ADD_WEB_DOMAIN, $params);

        if (!$result['status']) {
            $this->errors[] = array($result['error_code'] => $result['error_message']);
            return $this->reply($result['status'], $result['data']);
        }

        if (!empty($_s['TPL'])) {
            $params = array(
                        'USER'   => $user['uid'],
                        'DOMAIN' => $_s['DOMAIN'],
                        'TPL'    => $_s['TPL']
                      );
            $result = 0;
            $result = Vesta::execute(Vesta::V_CHANGE_WEB_DOMAIN_TPL, $params);

            if (!$result['status']) {
                $this->errors['CHANGE_TPL'] = array($result['error_code'] => $result['error_message']);
            }
        }
      

        if (!empty($_s['ALIAS'])) {
            $alias_arr = explode(',', str_replace("\n", "", $_s['ALIAS']));

            foreach ($alias_arr as $alias) {
                $params = array(
                            'USER'   => $user['uid'],
                            'DOMAIN' => $_s['DOMAIN'],
                            'ALIAS'  => trim($alias)
                           );
                $result = 0;
                $result = Vesta::execute(Vesta::V_ADD_WEB_DOMAIN_ALIAS, $params);

                if (!$result['status']) {
                    $this->errors['ALIAS'] = array($result['error_code'] => $result['error_message']);
                }
            }
        }
            
        if (!empty($_s['STAT']) && @$_s['STAT'] != 'none') {
            $params = array(
                        'USER'   => $user['uid'],
                        'DOMAIN' => $_s['DOMAIN'],
                        'STAT'   => $_s['STAT']);
            $result = 0;
            $result = Vesta::execute(Vesta::V_ADD_WEB_DOMAIN_STAT, $params);

            if (!$result['status']) {
                $this->errors['STATS'] = array($result['error_code'] => $result['error_message']);
            }
        }
           
        if (!empty($_s['STAT_AUTH']) && @Utils::getCheckboxBooleanValue($_s['STATS_AUTH'])) {
            $params = array(
                        'USER'          => $user['uid'],
                        'DOMAIN'        => $_s['DOMAIN'],
                        'STAT_USER'     => $_s['STAT_USER'],
                        'STAT_PASSWORS' => $_s['STAT_PASSWORD']
                      );
            $result = 0;
            $result = Vesta::execute(Vesta::V_ADD_WEB_DOMAIN_STAT_AUTH, $params);

            if(!$result['status'])
                $this->errors['STAT_AUTH'] = array($result['error_code'] => $result['error_message']);
            }

        if (!empty($_s['CGI'])) {
            if (Utils::getCheckboxBooleanValue($_s['CGI'])) {
                /* removed due to CGI is set by default at WEB DOMAIN creation
                $result = array();
                $result = Vesta::execute(Vesta::V_ADD_WEB_DOMAIN_CGI, array('USER' => $user['uid'], 'DOMAIN' => $_s['DOMAIN']));
                if (!$result['status']) {
                    $this->status = FALSE;
                    $this->errors['ADD_CGI'] = array($result['error_code'] => $result['error_message']);
                }
                */
            }
            else{
                $result = array();
                $result = Vesta::execute(Vesta::V_DEL_WEB_DOMAIN_CGI, array('USER' => $user['uid'], 'DOMAIN' => $_s['DOMAIN']));
                if (!$result['status']) {
                    $this->status = FALSE;
                    $this->errors['DEL_CGI'] = array($result['error_code'] => $result['error_message']);
                }
            }
        }

        if (!empty($_s['ELOG'])) {
            if (Utils::getCheckboxBooleanValue($_s['ELOG'])) {
                $result = array();
                $result = Vesta::execute(Vesta::V_ADD_WEB_DOMAIN_ELOG, array('USER' => $user['uid'], 'DOMAIN' => $_s['DOMAIN']));
                if (!$result['status']) {
                    $this->status = FALSE;
                    $this->errors['ADD_ELOG'] = array($result['error_code'] => $result['error_message']);
                }
            }
        }

        if (Utils::getCheckboxBooleanValue($_s['DNS_DOMAIN'])) {

            $params = array(
                        'USER'       => $user['uid'],
                        'DNS_DOMAIN' => $_s['DOMAIN'],
                        'IP'         => $_s['IP']
                      );

            $result = Vesta::execute(Vesta::V_ADD_DNS_DOMAIN, $params);
            if (!$result['status']) {
                $this->errors[] = array($result['error_code'] => $result['error_message']);
            }

    
            if (@Utils::getCheckboxBooleanValue($_s['SUSPEND'])) {
                if($result['status']){
                    $result = array();

                    $result = Vesta::execute(Vesta::V_SUSPEND_DNS_DOMAIN, array('USER' => $user['uid'], 'DNS_DOMAIN' => $_s['DOMAIN']));
                    if (!$result['status']) {
                        $this->status = FALSE;
                        $this->errors['DNS_SUSPEND'] = array($result['error_code'] => $result['error_message']);
                    }   
                }
            }


        }
      
        
        /*if (!empty($_s['MAIL'])) {
            $params = array(
                        'USER'        => $_user,
                        'MAIL_DOMAIN' => $_s['DOMAIN'],
                        'IP'          => $_s['IP']
                      );


        require_once V_ROOT_DIR . 'api/MAIL.class.php';

        $mail = new MAIL();
        $result = 0;
        $result = $mail->addExecute($params);
        if (!$result['status']) 
          $this->errors['MAIL_DOMAIN'] = array($result['error_code'] => $result['error_message']);
        }*/



		if (!empty($_s['SSL_KEY']) && !empty($_s['SSL_CRT']) && $_s['SSL'] == 'on' ) {
            $ssl_dir = sys_get_temp_dir().'/';
            
            $ssl_crt_file = $ssl_dir . $_s['DOMAIN'] . '.crt';
            file_put_contents($ssl_crt_file, $_s['SSL_CRT']);

            $ssl_key_file = $ssl_dir . $_s['DOMAIN'] . '.key';
            file_put_contents($ssl_key_file, $_s['SSL_KEY']);

    		if (!empty($_s['SSL_CA'])) {
                $ssl_ca_file = $ssl_dir . $_s['DOMAIN'] . '.ca';
                file_put_contents($ssl_ca_file, $_s['SSL_CA']);
            }

            //            $_GET['debug'] = 2;
			$params = array(
						'USER'     => $user['uid'],
						'DOMAIN'   => $_s['DOMAIN'],
                        'SSL_DIR'  => $ssl_dir,
                        'SSL_HOME' => $_s['SSL_HOME'] == 'on' ? 'single' : 'same'
                        );

			$result = 0;
            $result = Vesta::execute(Vesta::V_ADD_WEB_DOMAIN_SSL, $params);
    		if (!$result['status']) {
                $this->errors['SSL'] = array($result['error_code'] => $result['error_message']);
            }

            unlink($ssl_crt_file);
            unlink($ssl_key_file);
            unlink($ssl_ca_file);
		}

        if (@Utils::getCheckboxBooleanValue($_s['SUSPEND'])) {
            if($result['status']){
                $result = array();
          
                $result = Vesta::execute(Vesta::V_SUSPEND_WEB_DOMAIN, array('USER' => $user['uid'], 'JOB' => $_s['DOMAIN']));
                if (!$result['status']) {
                    $this->status = FALSE;
                    $this->errors['SUSPEND'] = array($result['error_code'] => $result['error_message']);
                }   
            }
        }


        return $this->reply($result['status'], $result['data']);
    }
    
    public function deleteExecute(Request $request) 
    {
        $_s = $request->getParameter('spell');
        $user = $this->getLoggedUser();

        $params = array(
                    'USER'   => $user['uid'],
                    'DOMAIN' => $_s['DOMAIN']
                  );
      
        $result = Vesta::execute(Vesta::V_DEL_WEB_DOMAIN, $params);
      
        if (!$result['status']) {
            $this->errors[] = array($result['error_code'] => $result['error_message']);
        }

        $params = array(
                    'USER'       => $_user,
                    'DNS_DOMAIN' => $_s['DOMAIN']
                  );
   
        return $this->reply($result['status'], $result['data']);
    }
  
    public function changeExecute(Request $request)
    {        
        $_s = $request->getParameter('spell');
        $_old = $request->getParameter('old');
        $_new = $request->getParameter('new');

        $_old['ELOG'] = $_old['ELOG'] == 'yes' ? 'on' : 'off';
        $_old['CGI']  = $_old['CGI']  == 'yes' ? 'on' : 'off';
        $_old['AUTH'] = $_old['AUTH'] == 'yes' ? 'on' : 'off';
        //        $_old['SSL']  = $_old['SSL']  == 'yes' ? 'on' : 'off';
        //        $_new['SSL']  = $_new['SSL']  == 'yes' ? 'on' : 'off';
        //        $_new['SSL_HOME']  = $_new['SSL_HOME']  == 'no' ? 'shared' : 'single';

        $user = $this->getLoggedUser();
        $_DOMAIN = $_new['DOMAIN'];

        $result['status'] = TRUE;


    	if(@Utils::getCheckboxBooleanValue($_new['SUSPEND'])){
            $result = Vesta::execute(Vesta::V_SUSPEND_WEB_DOMAIN, array('USER' => $user['uid'], 'DOMAIN' => $_DOMAIN));
			return $this->reply($result['status'], $result['error_message']);
		}
		elseif(@Utils::getCheckboxBooleanValue($_old['SUSPEND'])){
            $result = Vesta::execute(Vesta::V_UNSUSPEND_WEB_DOMAIN, array('USER' => $user['uid'], 'DOMAIN' => $_DOMAIN));
    		if (!$result['status']) {
    			$this->status = FALSE;
    			$this->errors['UNSUSPEND'] = array($result['error_code'] => $result['error_message']);
    			return $this->reply($result['status'], $result['error_message']);
        	}
		}

        if ($_old['IP'] != $_new['IP']) {
            $result = array();
            $result = Vesta::execute(Vesta::V_CHANGE_WEB_DOMAIN_IP, array('USER' => $user['uid'], 'DOMAIN' => $_DOMAIN, 'IP' => $_new['IP']));
            if (!$result['status']) {
                $this->status = FALSE;
                $this->errors['IP_ADDRESS'] = array($result['error_code'] => $result['error_message']);
            }
        }

        if ($_old['TPL'] != $_new['TPL']) {
            $result = array();
            $result = Vesta::execute(Vesta::V_CHANGE_WEB_DOMAIN_TPL, array('USER' => $user['uid'], 'DOMAIN' => $_DOMAIN, 'TPL' => $_new['TPL']));
            if (!$result['status']) {
                $this->status = FALSE;
                $this->errors['TPL'] = array($result['error_code'] => $result['error_message']);
            }
        }

        if ($_old['ALIAS'] != $_new['ALIAS']) {
            $result = array();
			
            $old_arr_raw = preg_split('/[,\s]/', $_old['ALIAS']);
            $new_arr_raw = preg_split('/[,\s]/', $_new['ALIAS']);
			$old_arr = array();
			$new_arr = array();
			foreach ($old_arr_raw as $alias) {
				if ('' != trim($alias)) {
					$old_arr[] = $alias;
				}
			}
			foreach ($new_arr_raw as $alias) {
				if ('' != trim($alias)) {
					$new_arr[] = $alias;
				}
			}
			
            $added   = array_diff($new_arr, $old_arr);
            $deleted = array_diff($old_arr, $new_arr);
 
			foreach ($deleted as $alias) {
                $result = Vesta::execute(Vesta::V_DEL_WEB_DOMAIN_ALIAS, array('USER' => $user['uid'], 'DOMAIN' => $_DOMAIN, 'ALIAS' => $alias));
                if (!$result['status']) {
                    $this->status = FALSE;
                    $this->errors['DEL_ALIAS'] = array($result['error_code'] => $result['error_message']);
                }
            }
 
            foreach ($added as $alias) {
                $result = Vesta::execute(Vesta::V_ADD_WEB_DOMAIN_ALIAS, array('USER' => $user['uid'], 'DOMAIN' => $_DOMAIN, 'ALIAS' => $alias));
                if (!$result['status']) {
                    $this->status = FALSE;
                    $this->errors['ADD_ALIAS'] = array($result['error_code'] => $result['error_message']);
                }
            }
        }


        if (($_old['STAT_AUTH'] != $_new['STAT_AUTH']) && !empty($_s['STAT_AUTH']) && @Utils::getCheckboxBooleanValue($_s['STATS_AUTH'])) {
            $params = array(
                        'USER'          => $user['uid'],
                        'DOMAIN'        => $_DOMAIN,
                        'STAT_USER'     => $_new['STAT_USER'],
                        'STAT_PASSWORS' => $_new['STAT_PASSWORD']
                      );
            $result = 0;
            $result = Vesta::execute(Vesta::V_ADD_WEB_DOMAIN_STAT_AUTH, $params);

            if(!$result['status']) {
                $this->errors['STAT_AUTH'] = array($result['error_code'] => $result['error_message']);
            }
        }

        if (($_old['STAT'] != $_new['STAT'])) {
            if ($_new['STAT'] != 'none') {
                $result = array();
                $result = Vesta::execute(Vesta::V_ADD_WEB_DOMAIN_STAT, array('USER' => $user['uid'], 'DOMAIN' => $_DOMAIN, 'STAT' => $_new['STAT']));
                if (!$result['status']) {
                    $this->status = FALSE;
                    $this->errors['ADD_STAT'] = array($result['error_code'] => $result['error_message']);
                }
            }
	    else {
                $result = array();
                $result = Vesta::execute(Vesta::V_DEL_WEB_DOMAIN_STAT, array('USER' => $user['uid'], 'DOMAIN' => $_DOMAIN));
                if (!$result['status']) {
                    $this->status = FALSE;
                    $this->errors['DEL_STAT'] = array($result['error_code'] => $result['error_message']);
                }
                $result = array();

                $result = Vesta::execute(Vesta::V_DEL_WEB_DOMAIN_STAT_AUTH, array('USER' => $user['uid'], 'DOMAIN' => $_DOMAIN, 'STAT_USER' => $_new['STAT_USER']));
                if (!$result['status']) {
                    $this->status = FALSE;
                    $this->errors['DEL_STAT_AUTH'] = array($result['error_code'] => $result['error_message']);
                }
            }
        }

        if (($_old['CGI'] != $_new['CGI'])) {
            if (Utils::getCheckboxBooleanValue($_new['CGI'])) {
                $result = array();
                $result = Vesta::execute(Vesta::V_ADD_WEB_DOMAIN_CGI, array('USER' => $user['uid'], 'DOMAIN' => $_DOMAIN));
                if (!$result['status']) {
                    $this->status = FALSE;
                    $this->errors['ADD_CGI'] = array($result['error_code'] => $result['error_message']);
                }
            }
	    else {
                $result = array();
                $result = Vesta::execute(Vesta::V_DEL_WEB_DOMAIN_CGI, array('USER' => $user['uid'], 'DOMAIN' => $_DOMAIN));
                if (!$result['status']) {
                    $this->status = FALSE;
                    $this->errors['DEL_CGI'] = array($result['error_code'] => $result['error_message']);
                }
            }
        }

        if (($_old['ELOG'] != $_new['ELOG'])) {
            if (Utils::getCheckboxBooleanValue($_new['ELOG'])) {
                $result = array();
                $result = Vesta::execute(Vesta::V_ADD_WEB_DOMAIN_ELOG, array('USER' => $user['uid'], 'DOMAIN' => $_DOMAIN));
                if (!$result['status']) {
                    $this->status = FALSE;
                    $this->errors['ADD_ELOG'] = array($result['error_code'] => $result['error_message']);
                }
            }
    	    else {
                $result = array();
                $result = Vesta::execute(Vesta::V_DEL_WEB_DOMAIN_ELOG, array('USER' => $user['uid'], 'DOMAIN' => $_DOMAIN));
                if (!$result['status']) {
                    $this->status = FALSE;
                    $this->errors['DEL_ELOG'] = array($result['error_code'] => $result['error_message']);
                }
            }
        }
        

		if (    (   $_old['SSL_KEY'] != $_new['SSL_KEY'] 
                    || $_old['SSL_CRT'] != $_new['SSL_CRT'] 
                    || $_old['SSL_CA'] != $_new['SSL_CA'] 
                    || $_old['SSL_HOME'] != $_new['SSL_HOME'] 
                )
                && !empty($_new['SSL_KEY']) 
                && !empty($_new['SSL_CRT']) 
                && $_new['SSL'] == 'on'
            ) {
            $ssl_dir = sys_get_temp_dir().'/';
            
            $ssl_crt_file = $ssl_dir . $_new['DOMAIN'] . '.crt';
            file_put_contents($ssl_crt_file, $_new['SSL_CRT']);

            $ssl_key_file = $ssl_dir . $_new['DOMAIN'] . '.key';
            file_put_contents($ssl_key_file, $_new['SSL_KEY']);

    		if (!empty($_new['SSL_CA'])) {
                $ssl_ca_file = $ssl_dir . $_new['DOMAIN'] . '.ca';
                file_put_contents($ssl_ca_file, $_new['SSL_CA']);
            }
 
			$params = array(
						'USER'     => $user['uid'],
						'DOMAIN'   => $_DOMAIN,
                        'SSL_DIR'  => $ssl_dir,
                        'SSL_HOME' => $_new['SSL_HOME'] == 'on' ? 'single' : 'same'
                        );

			$result = 0;

            // updating ssl 
            if($_old['SSL'] == 'on'){
                $result = Vesta::execute(Vesta::V_CHANGE_WEB_DOMAIN_SSL, $params);
        		if (!$result['status']) {
           			$this->errors['SSL'] = array($result['error_code'] => $result['error_message']);
                }
 
                // if ssl home dir changed, updating it
                if($_old['SSL_HOME'] != $_new['SSL_HOME']){
             		$params = array(
        				'USER'     => $user['uid'],
            			'DOMAIN'   => $_DOMAIN,
                        'SSL_HOME' => $_new['SSL_HOME'] == 'on' ? 'single' : 'same'
                        );
                    $result = Vesta::execute(Vesta::V_CHANGE_WEB_DOMAIN_SSLHOME, $params);
            		if (!$result['status']) {
                		$this->errors['SSL_HOME'] = array($result['error_code'] => $result['error_message']);
                    }
                }
            }
            // adding new ssl
            else{
                $result = Vesta::execute(Vesta::V_ADD_WEB_DOMAIN_SSL, $params);
    			if (!$result['status']) {
                    $this->errors['SSL'] = array($result['error_code'] => $result['error_message']);
                }
            }


            unlink($ssl_crt_file);
            unlink($ssl_key_file);
            unlink($ssl_ca_file);
		}

		if ( ((empty($_new['SSL_KEY']) || empty($_new['SSL_CRT'])) && $_old['SSL'] == 'on') || ( $_old['SSL'] == 'on' && $_new['SSL'] == 'off') ){
			$result = 0;
			$result = Vesta::execute(Vesta::V_DEL_WEB_DOMAIN_SSL, array('USER' => $user['uid'], 'DOMAIN' => $_DOMAIN));
			if (!$result['status']) {
				$this->errors['SSL_REMOVING'] = array($result['error_code'] => $result['error_message']);
			}
        }

        return $this->reply($result['status'], $result['data']);
    }



    public function suspendExecute(Request $request)
    {    
        $_s = $request->getParameter('spell');
        $user = $this->getLoggedUser();

        $params = array(
                    'USER' => $user['uid'],
                    'DOMAIN' => $_s['DOMAIN']
                  );

        $result = Vesta::execute(Vesta::V_SUSPEND_WEB_DOMAIN, $params);

        if (!$result['status']) {
            $this->errors[] = array($result['error_code'] => $result['error_message']);
        }

        return $this->reply($result['status'], $result['data']);
    }


    public function unsuspendExecute(Request $request)
    {        
        $_s = $request->getParameter('spell');
        $user = $this->getLoggedUser();
    
        $params = array(
                    'USER'   => $user['uid'],
                    'DOMAIN' => $_s['DOMAIN']
                  );
    
        $result = Vesta::execute(Vesta::V_UNSUSPEND_WEB_DOMAIN, $params);
    
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
          $result = Vesta::execute(Vesta::V_SUSPEND_WEB_DOMAIN, array('USER' => $user['uid'], $entity['DOMAIN']));
        }

        return $this->reply($result['status'], $result['data']);
    }

    public function massiveUnsuspendExecute(Request $request)
    {
        $user   = $this->getLoggedUser();
        $_entities = $request->getParameter('entities');

        foreach($_entities as $entity){
            $result = Vesta::execute(Vesta::V_UNSUSPEND_WEB_DOMAIN, array('USER' => $user['uid'], $entity['DOMAIN']));
        }

        return $this->reply($result['status'], $result['data']);
    }

    public function massiveDeleteExecute(Request $request)
    {
        $user   = $this->getLoggedUser();
        $_entities = $request->getParameter('entities');

        foreach($_entities as $entity){
            $result = Vesta::execute(Vesta::V_DEL_WEB_DOMAIN, array('USER' => $user['uid'], $entity['DOMAIN']));
        }

        return $this->reply($result['status'], $result['data']);
    }
}
