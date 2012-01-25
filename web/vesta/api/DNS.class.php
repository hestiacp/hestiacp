<?php

/**
 * DNS 
 * 
 * TODO: Too many "if" statements. Code should be refactored in order to not use a lot of "if" conditions
 * 
 * @author vesta, http://vestacp.com/
 * @author Dmitry Malishev <dima.malishev@gmail.com>
 * @author Dmitry Naumov-Socolov <naumov.socolov@gmail.com>
 * @copyright vesta 2010-2011
 */
 
class DNS extends AjaxHandler 
{

    /**
     * Get DNS entries
     * 
     * @param Request $request
     * @return string - Ajax Reply
     */
    public function getListExecute(Request $request) 
    {
        $user = $this->getLoggedUser();
        $reply = array();
        $result = Vesta::execute(Vesta::V_LIST_DNS_DOMAINS, array($user['uid'], Config::get('response_type')));
        foreach ($result['data'] as $dns_domain => $details) {
            $reply[] = array(
                         'DNS_DOMAIN'   => $dns_domain,
                         'IP'           => $details['IP'],
                         'TPL'          => $details['TPL'],
                         'TTL'          => $details['TTL'],
                         'EXP'          => $details['EXP'],
                         'SOA'          => $details['SOA'],
                         'SUSPEND'      => $details['SUSPEND'],
                         'DATE'         => $details['DATE'] // date(Config::get('ui_date_format', strtotime($details['DATE'])))
                      );
        }
        
        if (!$result['status']) {
            $this->errors[] = array($result['error_code'] => $result['error_message']);
        }
     
        return $this->reply($result['status'], $reply);
    }
    
    /**
     * Get DNS records
     * 
     * @param Request $request
     * @return string - Ajax Reply
     */
    public function getListRecordsExecute(Request $request) 
    {
        $_s     = $request->getParameter('spell');
        $user   = $this->getLoggedUser();
        $reply  = array();
       
        $params = array(
            'USER'   => $user['uid'], 
            'DOMAIN' => $_s['DNS_DOMAIN']
          );

        $result = Vesta::execute(Vesta::V_LIST_DNS_DOMAIN_RECORDS, $params, self::JSON);
        foreach ($result['data'] as $record_id => $details) {
            $reply[$record_id] = array(
                                     'ID'            => $record_id,
                                     'RECORD_ID'    => $record_id,
                                     'RECORD'       => $details['RECORD'],
                                     'RECORD_TYPE'  => $details['TYPE'],
                                     'RECORD_VALUE' => str_replace('"', '', $details['VALUE']),
                                     'SUSPEND'      => $details['SUSPEND'],
                                     'DATE'         => date(Config::get('ui_date_format', strtotime($details['DATE'])))
                                  );
        }

        if (!$result['status']) {
            $this->errors[] = array($result['error_code'] => $result['error_message']);
        }
     
        return $this->reply($result['status'], $reply);
    }

    /**
     * Add DNS entry
     * 
     * v_add_dns_domain user domain ip [template] [exp] [soa] [ttl]
     * http://95.163.16.160:8083/dispatch.php?jedi_method=DNS.add&USER=vesta&DOMAIN=dev.vestacp.com&IP_ADDRESS=95.163.16.160&TEMPLATE=default&EXP=01-01-12&SOA=ns1.vestacp.com&TTL=3600
     * 
     * @param Request $request
     * @return string - Ajax Reply
     */
    public function addExecute(Request $request) 
    {
        $user = $this->getLoggedUser();
        $_s   = $request->getParameter('spell');
        $params = array(
                    'USER'          => $user['uid'],  /// OWNER ???
                    'DNS_DOMAIN'    => $_s['DNS_DOMAIN'],
                    'IP'            => $_s['IP'],
                    'TPL'           => $_s['TPL'],
                    'EXP'           => $_s['EXP'],
                    'SOA'           => $_s['SOA'],
                    'TTL'           => $_s['TTL']
                );
    
        $result = Vesta::execute(Vesta::V_ADD_DNS_DOMAIN, $params);
        if (!$result['status']) {
            $this->errors[] = array($result['error_code'] => $result['error_message']);
        }

    
        if (@Utils::getCheckboxBooleanValue($_s['SUSPEND'])) {
            if($result['status']){
                $result = array();

                $result = Vesta::execute(Vesta::V_SUSPEND_DNS_DOMAIN, array('USER' => $user['uid'], 'DNS_DOMAIN' => $_s['DNS_DOMAIN']));
                if (!$result['status']) {
                    $this->status = FALSE;
                    $this->errors['SUSPEND'] = array($result['error_code'] => $result['error_message']);
                }   
            }
        }

        return $this->reply($result['status'], $result['data']);
    }

    /**
     * Add DNS record
     * 
     * v_add_dns_domain_record user domain record type value [id]
     * http://95.163.16.160:8083/dispatch.php?jedi_method=DNS.addRecord&USER=vesta&DOMAIN=dev.vestacp.com&RECORD=ftp&TYPE=a&VALUE=87.248.190.222
     * 
     * @param Request $request
     * @return string - Ajax Reply
     */
     public function addRecordExecute(Request $request) 
     {
        $user = $this->getLoggedUser();
        $_s = $request->getParameter('spell');    
    
        $params = array(
                    'USER' => $user['uid'],
                    'DOMAIN' => $_s['DOMAIN'],
                    'RECORD' => $_s['RECORD'],
                    'RECORD_TYPE' => $_s['TYPE'],
                    'RECORD_VALUE' => $_s['VALUE'],
                    'RECORD_ID' => $_s['RECORD_ID']
                  );
        
        $result = Vesta::execute(Vesta::V_ADD_DNS_DOMAIN_RECORD, $params);
     
        if (!$result['status']) {
            $this->errors[] = array($result['error_code'] => $result['error_message']);
        }

        return $this->reply($result['status'], $result['data']);
    }

    /**
     * Delete DNS entry
     * 
     * v_delete_dns_domain user domain
     * http://95.163.16.160:8083/dispatch.php?jedi_method=DNS.del&USER=vesta&DOMAIN=dev.vestacp.com
     * 
     * @param Request $request
     * @return string - Ajax Reply
     */
    public function deleteExecute(Request $request) 
    {
        $user = $this->getLoggedUser();
        $_s = $request->getParameter('spell');        
        $params = array(
                    'USER' => $user['uid'],  /// OWNER ???
                    'DOMAIN' => $_s['DNS_DOMAIN'],
                  );
    
        $result = Vesta::execute(Vesta::V_DEL_DNS_DOMAIN, $params);

        if (!$result['status']) {
            $this->errors[] = array($result['error_code'] => $result['error_message']);
        }

        return $this->reply($result['status'], $result['data']);
    }

    /**
     * Add DNS record
     * 
     * v_delete_dns_domain_record user domain id 
     * http://95.163.16.160:8083/dispatch.php?jedi_method=DNS.delRecord&USER=vesta&DOMAIN=dev.vestacp.com&RECORD_ID=9
     * 
     * @param Request $request
     * @return string - Ajax Reply
     */
    public function deleteRecordExecute(Request $request) 
    {
        $_s = $request->getParameter('spell');
        $dns = $request->getParameter('dns');
        $user = $this->getLoggedUser();
    
        $params = array(
                    'USER'      => $user['uid'],  // TODO: OWNER ???
                    'DOMAIN'    => $dns['DNS_DOMAIN'],
                    'RECORD_ID' => $_s['RECORD_ID']
                  );
    
        $result = Vesta::execute(Vesta::V_DEL_DNS_DOMAIN_RECORD, $params);
     
        if (!$result['status']) {
            $this->errors[] = array($result['error_code'] => $result['error_message']);
        }

        return $this->reply($result['status'], $result['data']);
    }

    /**
     * Change DNS entry
     * 
     * TODO: get away from multiple "if" statements
     * 
     * DNS.change&spell={"old":{"DNS_DOMAIN": "dev.vestacp.com","IP": "95.163.16.160","TPL": "default","TTL": "3377","EXP": "12-12-12","SOA": "ns2.vestacp.com"},"new":{"DNS_DOMAIN": "dev.vestacp.com","IP": "95.163.16.160","TPL": "default","TTL": "3600","EXP": "02-02-12","SOA": "ns1.vestacp.com"}}
     * 
     * @param Request $request
     * @return string - Ajax Reply
     */
    public function changeExecute(Request $request)
    {    
        $_old        = $request->getParameter('old');
        $_new        = $request->getParameter('new');
        $user       = $this->getLoggedUser();
        $_DNS_DOMAIN = $_old['DNS_DOMAIN'];

		$result = array();
		if(@Utils::getCheckboxBooleanValue($_new['SUSPEND'])){
			$result = Vesta::execute(Vesta::V_SUSPEND_DNS_DOMAIN, array('USER' => $user['uid'], 'DNS_DOMAIN' => $_DNS_DOMAIN));
			return $this->reply($result['status'], $result['error_message']);
		}
		elseif(@Utils::getCheckboxBooleanValue($_old['SUSPEND'])){
			$result = Vesta::execute(Vesta::V_UNSUSPEND_DNS_DOMAIN, array('USER' => $user['uid'], 'DNS_DOMAIN' => $_DNS_DOMAIN));
    		if (!$result['status']) {
    			$this->status = FALSE;
    			$this->errors['UNSUSPEND'] = array($result['error_code'] => $result['error_message']);
    			return $this->reply($result['status'], $result['error_message']);
        	}
		}

        if ($_old['IP'] != $_new['IP']) {
            $result = array();
            
            $result = Vesta::execute(Vesta::V_CHANGE_DNS_DOMAIN_IP, array('USER' => $user['uid'], 'DNS_DOMAIN' => $_DNS_DOMAIN, 'IP' => $_new['IP']));
            if (!$result['status']) {
                $this->status = FALSE;
                $this->errors['IP_ADDRESS'] = array($result['error_code'] => $result['error_message']);
            }
        }
    
        if ($_old['TPL'] != $_new['TPL']) {
            $result = array();
            $result = Vesta::execute(Vesta::V_CHANGE_DNS_DOMAIN_TPL, array('USER' => $user['uid'], 'DNS_DOMAIN' => $_DNS_DOMAIN, 'TPL' => $_new['TPL']));
            if (!$result['status']) {
                $this->status = FALSE;
                $this->errors['TPL'] = array($result['error_code'] => $result['error_message']);
            }
        }
    
        if ($_old['TTL'] != $_new['TTL']) {
            echo 'changing ttl';
            $result = array();
            $result = Vesta::execute(Vesta::V_CHANGE_DNS_DOMAIN_TTL, array('USER' => $user['uid'], 'DNS_DOMAIN' => $_DNS_DOMAIN, 'TTL' => $_new['TTL']));
            if (!$result['status']) {
                $this->status = FALSE;
                $this->errors['TTL'] = array($result['error_code'] => $result['error_message']);
            }
        }

        if ($_old['EXP'] != $_new['EXP']) {
            $result = array();
            $result = Vesta::execute(Vesta::V_CHANGE_DNS_DOMAIN_EXP, array('USER' => $user['uid'], 'DNS_DOMAIN' => $_DNS_DOMAIN, 'EXP' => $_new['EXP']));
            if (!$result['status']) {
                $this->status = FALSE;
                $this->errors['EXP'] = array($result['error_code'] => $result['error_message']);
            }
        }
    
        if ($_old['SOA'] != $_new['SOA']) {
            $result = array();
            $result = Vesta::execute(Vesta::V_CHANGE_DNS_DOMAIN_SOA, array('USER' => $user['uid'], 'DNS_DOMAIN' => $_new['DNS_DOMAIN'], 'SOA' => $_new['SOA']));
            if (!$result['status']) {
                $this->status = FALSE;
                $this->errors['SOA'] = array($result['error_code'] => $result['error_message']);
            }
        }

        if (!$this->status) {
            Vesta::execute(Vesta::V_CHANGE_DNS_DOMAIN_IP,  array('USER' => $user['uid'], 'DNS_DOMAIN' => $_DNS_DOMAIN, 'IP' => $_old['IP']));
            Vesta::execute(Vesta::V_CHANGE_DNS_DOMAIN_TPL, array('USER' => $user['uid'], 'DNS_DOMAIN' => $_DNS_DOMAIN, 'IP' => $_old['TPL']));
            Vesta::execute(Vesta::V_CHANGE_DNS_DOMAIN_TTL, array('USER' => $user['uid'], 'DNS_DOMAIN' => $_DNS_DOMAIN, 'IP' => $_old['TTL']));
            Vesta::execute(Vesta::V_CHANGE_DNS_DOMAIN_EXP, array('USER' => $user['uid'], 'DNS_DOMAIN' => $_DNS_DOMAIN, 'IP' => $_old['EXP']));
            Vesta::execute(Vesta::V_CHANGE_DNS_DOMAIN_SOA, array('USER' => $user['uid'], 'DNS_DOMAIN' => $_new['DNS_DOMAIN'], 'IP' => $_old['SOA']));
        }

        return $this->reply($this->status, '');
    }

    /**
     * Change DNS record
     * 
     * @param Request $request
     * @return string - Ajax Reply
     */ 
    public function changeRecordsExecute(Request $request)
    {
        $records     = $request->getParameter('spell');
        $dns         = $request->getParameter('dns');
            $user        = $this->getLoggedUser();
        $domain      = $dns['DNS_DOMAIN'];

        // Get current records
        $curr_records = array();
        $params = array(
                    'USER'   => $user['uid'], 
                    'DOMAIN' => $domain
                  );

        $result = Vesta::execute(Vesta::V_LIST_DNS_DOMAIN_RECORDS, $params, self::JSON);
        foreach ($result['data'] as $record_id => $details) {
        $curr_records[] = $record_id;
        }

        $new_records = array();
        foreach ($records as $record) {
            if ((int)$record['RECORD_ID'] > 0) {
                $new_records[] = $record['RECORD_ID'];
            }
            }

        $delete = array_diff(array_values($curr_records), array_values($new_records));
        foreach ($records as $record) {
            if (((int)$record['RECORD_ID'] > 0) == false) {
            $params = array(
                'USER'          => $user['uid'],
                'DOMAIN'        => $domain,
                'RECORD'        => $record['RECORD'],
                'RECORD_TYPE'  => $record['RECORD_TYPE'],
                'RECORD_VALUE' => $record['RECORD_VALUE']
            );
        
            $result = Vesta::execute(Vesta::V_ADD_DNS_DOMAIN_RECORD, $params);
            if (!$result['status']) {
                        $this->status = FALSE;
                        $this->errors[$record_id] = array($result['error_code'] => $result['error_message']);
                    }
            }
            else {
            $params = array(
                'USER'            => $user['uid'],
                    'DOMAIN'       => $domain,
                    'ID'           => (int)$record['RECORD_ID'],
                'RECORD'       => $record['RECORD'],
                'RECORD_TYPE'  => $record['RECORD_TYPE'],
                'RECORD_VALUE' => $record['RECORD_VALUE']
            );
                $result = Vesta::execute(Vesta::V_CHANGE_DNS_DOMAIN_RECORD, $params);
                if (!$result['status']) {
                        $this->status = FALSE;
                        $this->errors[$record_id] = array($result['error_code'] => $result['error_message']);
                    }        
            }
        }
        foreach ($delete as $record_id) {
            $params = array(
                    'USER'            => $user['uid'],
                    'DOMAIN'       => $domain,
                    'ID'           => $record_id
                );
                $result = Vesta::execute(Vesta::V_DEL_DNS_DOMAIN_RECORD, $params);
                if (!$result['status']) {
                    $this->status = FALSE;
                    $this->errors[$record_id] = array($result['error_code'] => $result['error_message']);
                }        
        }

        return $this->reply($this->status, '');
    }

    public function getTemplateInfoExecute($request)
    {
        $spell  = $request->getParameter('spell');
        $result = Vesta::execute('v_list_user_packages', null, self::JSON);
        $reply = $result['data'];
        if (isset($spell['PACKAGE'])) {
            $reply = $result['data'][$spell['PACKAGE']];
        }
        
        return $this->reply(true, $reply);
    }



    public function massiveSuspendExecute(Request $request)
    {
        $user   = $this->getLoggedUser();
        $_entities = $request->getParameter('entities');

        foreach($_entities as $entity){
          $result = Vesta::execute(Vesta::V_SUSPEND_DNS_DOMAIN, array('USER' => $user['uid'], $entity['DNS_DOMAIN']));
        }

        return $this->reply($result['status'], $result['data']);
    }

    public function massiveUnsuspendExecute(Request $request)
    {
        $user   = $this->getLoggedUser();
        $_entities = $request->getParameter('entities');

        foreach($_entities as $entity){
            $result = Vesta::execute(Vesta::V_UNSUSPEND_DNS_DOMAIN, array('USER' => $user['uid'], $entity['DNS_DOMAIN']));
        }

        return $this->reply($result['status'], $result['data']);
    }

    public function massiveDeleteExecute(Request $request)
    {
        $user   = $this->getLoggedUser();
        $_entities = $request->getParameter('entities');

        foreach($_entities as $entity){
            $result = Vesta::execute(Vesta::V_DEL_DNS_DOMAIN, array('USER' => $user['uid'], $entity['DNS_DOMAIN']));
        }

        return $this->reply($result['status'], $result['data']);
    }
}
