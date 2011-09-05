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

        $result = Vesta::execute(Vesta::V_LIST_WEB_DOMAINS, array($user['uid'], Config::get('response_type')));

        foreach($result['data'] as $web_domain => $data)
        {
            $reply[$web_domain] = array(
                                      'IP'          => $record['IP'],
                                      'U_DISK'      => $record['U_DISK'],
                                      'U_BANDWIDTH' => $record['U_BANDWIDTH'],
                                      'TPL'         => $record['TPL'],
                                      'ALIAS'       => $record['ALIAS'],
                                      'PHP'         => $record['PHP'],
                                      'CGI'         => $record['CGI'],
                                      'ELOG'        => $record['ELOG'],
                                      'STATS'       => $record['STATS'],
                                      'STATS_AUTH'  => $record['STATS_AUTH'],
                                      'SSL'         => $record['SSL'],
                                      'SSL_HOME'    => $record['SSL_HOME'],
                                      'SSL_CERT'    => $record['SSL_CERT'],
                                      'NGINX'       => $record['NGINX'],
                                      'NGINX_EXT'   => $record['NGINX_EXT'],
                                      'SUSPEND'     => $record['SUSPEND'],
                                      'DATE'        => date(Config::get('ui_date_format', strtotime($record['DATE'])))
                                  );
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
        }

        if ($_s['TPL']) {
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
      
        if ($_s['ALIAS']) {
            $alias_arr = explode(',', $_s['ALIAS']);

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
            
        if ($_s['STAT']) {
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
           
        if ($_s['STAT_AUTH']) {
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

        if (0) {
            if ($_s['SSL']) {
                $params = array(
                            'USER'     => $user[''],
                            'DOMAIN'   => $_s['DOMAIN'],
                            'SSL_CERT' => $_s['SSL_CERT']
                          );

                if ($_s['SSL_HOME']) {
                    $params['SSL_HOME'] = $_s['SSL_HOME'];
                }

                if ($_s['SSL_TEXT']) {
                    // TODO: implement
                }

                $result = 0;
                $result = Vesta::execute(Vesta::V_ADD_WEB_DOMAIN_SSL, $params);

                if (!$result['status']) {
                    $this->errors['SSL'] = array($result['error_code'] => $result['error_message']);
                }
            }
        }
      
        if ($_s['CREATE_DNS_DOMAIN']) {
            $params = array(
                        'USER'       => $user['uid'],
                        'DNS_DOMAIN' => $_s['DOMAIN'],
                        'IP'         => $_s['IP']
                      );

            require_once V_ROOT_DIR . 'api/DNS.class.php';

            $dns = new DNS();
            $result = 0;
            $result = $dns->addExecute($params);
            if (!$result['status']) {
                $this->errors['DNS_DOMAIN'] = array($result['error_code'] => $result['error_message']);
            }            
        }
      
        /*
        if ($_s['CREATE_MAIL_DOMAIN']) {
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
        }
        */
      
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
   
        require_once V_ROOT_DIR . 'api/DNS.class.php';
        $dns = new DNS();
        $result = $dns->delExecute($params);
    
        if (!$result['status'] && $result['error_code'] != 31) { // domain not found
            $this->errors['DNS'] = array($result['error_code'] => $result['error_message']);
        }
   
        require_once V_ROOT_DIR . 'api/DNS.class.php';

        $params = array(
                    'USER'        => $user['uid'],
                    'MAIL_DOMAIN' => $_s['DOMAIN']
                  );

        return $this->reply($result['status'], $result['data']);
    }
  
    public function changeExecute(Request $request)
    {        
        $_s = $request->getParameter('spell');
        $_old = $request->getParameter('old');
        $_new = $request->getParameter('new');

        $user = $this->getLoggedUser();
        $_DOMAIN = $_new['DOMAIN'];
    
        if ($_old['IP'] != $_new['IP']) {
            $result = array();
            $result = Vesta::execute(Vesta::V_CHANGE_WEB_DOMAIN_IP, array('USER' => $_user, 'DOMAIN' => $_DOMAIN, 'IP' => $_new['IP']));
            if (!$result['status']) {
                $this->status = FALSE;
                $this->errors['IP_ADDRESS'] = array($result['error_code'] => $result['error_message']);
            }
        }

        if ($_old['TPL'] != $_new['TPL']) {
            $result = array();
            $result = Vesta::execute(Vesta::V_CHANGE_WEB_DOMAIN_TPL, array('USER' => $_user, 'DOMAIN' => $_DOMAIN, 'TPL' => $_new['TPL']));
            if (!$result['status']) {
                $this->status = FALSE;
                $this->errors['TPL'] = array($result['error_code'] => $result['error_message']);
            }
        }

        if ($_old['ALIAS'] != $_new['ALIAS']) {
            $result = array();

            $old_arr = explode(',', $_old['ALIAS']);
            $new_arr = explode(',', $_new['ALIAS']);

            $added = array_diff($new_arr, $old_arr);
            $deleted = array_diff($old_arr, $new_arr);

            foreach ($added as $alias) {
                $result = Vesta::execute(Vesta::V_ADD_WEB_DOMAIN_ALIAS, array('USER' => $_user, 'DOMAIN' => $_DOMAIN, 'ALIAS' => $alias));
                if (!$result['status']) {
                    $this->status = FALSE;
                    $this->errors['ADD_ALIAS'] = array($result['error_code'] => $result['error_message']);
                }
            }
        
            foreach ($deleted as $alias) {
                $result = Vesta::execute(Vesta::V_DEL_WEB_DOMAIN_ALIAS, array('USER' => $_user, 'DOMAIN' => $_DOMAIN, 'ALIAS' => $alias));
                if (!$result['status']) {
                    $this->status = FALSE;
                    $this->errors['DEL_ALIAS'] = array($result['error_code'] => $result['error_message']);
                }
            }
        }


        if ($_old['STAT'] != $_new['STAT']) {
            if ($_new['STAT'] == true) {
                $result = array();
                $result = Vesta::execute(Vesta::V_ADD_WEB_DOMAIN_STAT, array('USER' => $_user, 'DOMAIN' => $_DOMAIN, 'STAT' => $_new['STAT']));
                if (!$result['status']) {
                    $this->status = FALSE;
                    $this->errors['ADD_STAT'] = array($result['error_code'] => $result['error_message']);
                }
            }

            if ($_new['STAT'] == false) {
                $result = array();
                $result = Vesta::execute(Vesta::V_DEL_WEB_DOMAIN_STAT, array('USER' => $_user, 'DOMAIN' => $_DOMAIN));
                if (!$result['status']) {
                    $this->status = FALSE;
                    $this->errors['DEL_STAT'] = array($result['error_code'] => $result['error_message']);
                }
                $result = array();

                $result = Vesta::execute(Vesta::V_DEL_WEB_DOMAIN_STAT_AUTH, array('USER' => $_user, 'DOMAIN' => $_DOMAIN, 'STAT_USER' => $_new['STAT_USER']));
                if (!$result['status']) {
                    $this->status = FALSE;
                    $this->errors['DEL_STAT_AUTH'] = array($result['error_code'] => $result['error_message']);
                }
            }
        }

        if ($_old['CGI'] != $_new['CGI']) {
            if ($_new['CGI'] == true) {
                $result = array();
                $result = Vesta::execute(Vesta::V_ADD_WEB_DOMAIN_CGI, array('USER' => $_user, 'DOMAIN' => $_DOMAIN));
                if (!$result['status']) {
                    $this->status = FALSE;
                    $this->errors['ADD_CGI'] = array($result['error_code'] => $result['error_message']);
                }
            }
            
            if ($_new['CGI'] == false) {
                $result = array();
                $result = Vesta::execute(Vesta::V_DEL_WEB_DOMAIN_CGI, array('USER' => $_user, 'DOMAIN' => $_DOMAIN));
                if (!$result['status']) {
                    $this->status = FALSE;
                    $this->errors['DEL_CGI'] = array($result['error_code'] => $result['error_message']);
                }
            }
        }

        if ($_old['ELOG'] != $_new['ELOG']) {
            if ($_new['ELOG'] == true) {
                $result = array();
                $result = Vesta::execute(Vesta::V_ADD_WEB_DOMAIN_ELOG, array('USER' => $_user, 'DOMAIN' => $_DOMAIN));
                if (!$result['status']) {
                    $this->status = FALSE;
                    $this->errors['ADD_ELOG'] = array($result['error_code'] => $result['error_message']);
                }
            }
        
            if ($_new['ELOG'] == false) {
                $result = array();
                $result = Vesta::execute(Vesta::V_DEL_WEB_DOMAIN_ELOG, array('USER' => $_user, 'DOMAIN' => $_DOMAIN));
                if (!$result['status']) {
                    $this->status = FALSE;
                    $this->errors['DEL_ELOG'] = array($result['error_code'] => $result['error_message']);
                }
            }
        }

        return $this->reply($result['status'], $result['data']);
    }



    public function suspendExecute(Request $request)
    {    
        $_s = $request->getParameter('spell');
        $user = $this->getLoggedUser();

        $params = array(
                    'USER' => $_user['uid'],
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
    
}
