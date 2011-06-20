<?php

/**
 * DNS 
 * 
 * @author Naumov-Socolov <naumov.socolov@gmail.com>
 * @author Malishev Dima <dima.malishev@gmail.com>
 * @author vesta, http://vestacp.com/
 * @copyright vesta 2010-2011
 */
class DNS extends AjaxHandler 
{
 
	/**
	 * list entries
	 * 
	 * @param Request $request
	 * @return
	 */
	public function getListExecute($request) {
		$_user = 'vesta';
		$reply = array();

		$result = Vesta::execute(Vesta::V_LIST_DNS_DOMAINS, array($_user, Config::get('response_type')));
		foreach ($result['data'] as $dns_domain => $details)
		{
			$reply[] = array(
				 'DNS_DOMAIN' => $dns_domain,
				 'IP' => $details['IP'],
				 'TPL' => $details['TPL'],
				 'TTL' => $details['TTL'],
				 'EXP' => $details['EXP'],
				 'SOA' => $details['SOA'],
				 'SUSPEND' => $details['SUSPEND'],				
				 'DATE' => date(Config::get('ui_date_format', strtotime($details['DATE'])))
			);
		}
		if (!$result['status'])
		{
			$this->errors[] = array($result['error_code'] => $result['error_message']);
		}

		return $this->reply($result['status'], $reply);
	}

	/**
	 * List records of entries
	 * 
	 * @param Request $request
	 * @return
	 */
    public function getListRecordsExecute($request) 
    {
		$r = new Request();
		$_s = $r->getSpell();
		$_user = 'vesta';

		$reply = array();
	   
		$result = Vesta::execute(Vesta::V_LIST_DNS_DOMAIN_RECORDS, array($_user, $_s['DNS_DOMAIN'], Config::get('response_type')));
		foreach ($result['data'] as $record_id => $details)
		{
			$reply[$record_id] = array(
				 'RECORD_ID' => $record_id,
				 'RECORD' => $details['RECORD'],
				 'RECORD_TYPE' => $details['TYPE'],
				 'RECORD_VALUE' => $details['VALUE'],
				 'SUSPEND' => $details['SUSPEND'],
				 'DATE' => date(Config::get('ui_date_format', strtotime($details['DATE'])))
			);
		}

		if (!$result['status'])
		{
			$this->errors[] = array($result['error_code'] => $result['error_message']);
		}
		 
		return $this->reply($result['status'], $reply);
    }

	/**
	 * Add entry
	 * 
	 * v_add_dns_domain user domain ip [template] [exp] [soa] [ttl]
	 * http://95.163.16.160:8083/dispatch.php?jedi_method=DNS.add&USER=vesta&DOMAIN=dev.vestacp.com&IP_ADDRESS=95.163.16.160&TEMPLATE=default&EXP=01-01-12&SOA=ns1.vestacp.com&TTL=3600
	 * 
	 * @param Request $request
	 * @return
	 */    
    public function addExecute($_spell = false) 
	{
		$r = new Request();
		if ($_spell) 
		{
			$_s = $_spell;
		}
		else
		{
			$_s = $r->getSpell();
		}

		$_user = 'vesta';
		$params = array(
			'USER' => $_user,  // TODO: examine if OWNER ???
			'DNS_DOMAIN' => $_s['DNS_DOMAIN'],
			'IP' => $_s['IP']
		);
		($_s['TPL']) ? $params['TPL'] = $_s['TPL'] : -1;
		($_s['EXP']) ? $params['EXP'] = $_s['EXP'] : -1;
		($_s['SOA']) ? $params['SOA'] = $_s['SOA'] : -1;
		($_s['TTL']) ? $params['TTL'] = $_s['TTL'] : -1;

		$result = Vesta::execute(Vesta::V_ADD_DNS_DOMAIN, $params);
		if (!$result['status'])
		{
			$this->errors[] = array($result['error_code'] => $result['error_message']);
		}

		return $this->reply($result['status'], $result['data']);
	}

	/**
	 * Add record
	 * 
	 * @param Request $request
	 * @return
	 */
    public function addRecordExecute($request) 
    {
		$r = new Request();
		$_s = $r->getSpell();
		$_user = 'vesta';

		$params = array(
			'USER' => $_s['USER'],  // TODO: find out if it's OWNER ???
			'DOMAIN' => $_s['DOMAIN'],
			'RECORD' => $_s['RECORD'],
			'RECORD_TYPE' => $_s['TYPE'],
			'RECORD_VALUE' => $_s['VALUE'],
			'RECORD_ID' => $_s['RECORD_ID']
		);

		$result = Vesta::execute(Vesta::V_ADD_DNS_DOMAIN_RECORD, $params);

		if (!$result['status']) 
		{
			$this->errors[] = array($result['error_code'] => $result['error_message']);
		}

		return $this->reply($result['status'], $result['data']);
    }


    /**
	 * Delete entry
	 * 
	 * @param mixed $_spell
	 * @return
	 */
    public function delExecute($_spell = false) 
    {
		$r = new Request();
		if ($_spell)
		{
			$_s = $_spell;
		}
		else
		{
			$_s = $r->getSpell();
		}

		$_user = 'vesta';
		$params = array(
			'USER' => $_user,  // TODO: find out -> OWNER ???
			'DOMAIN' => $_s['DNS_DOMAIN'],
		);

		$result = Vesta::execute(Vesta::V_DEL_DNS_DOMAIN, $params);

		if (!$result['status']) 
		{
			$this->errors[] = array($result['error_code'] => $result['error_message']);
		}

		return $this->reply($result['status'], $result['data']);
    }



    /**
	 * Delete record
	 * 
	 * @param Request $request
	 * @return
	 */
    function delRecordExecute($request) 
    {
		$r = new Request();
		$_s = $r->getSpell();
		$_user = 'vesta';

		$params = array(
			'USER' => $_user,  // TODO: find out if -> OWNER ???
			'DOMAIN' => $_s['DOMAIN'],
			'RECORD_ID' => $_s['RECORD_ID']
		);

		$result = Vesta::execute(Vesta::V_DEL_DNS_DOMAIN_RECORD, $params);

		if (!$result['status'])
		{
			$this->errors[] = array($result['error_code'] => $result['error_message']);
		}

		return $this->reply($result['status'], $result['data']);
    }
	
	/**
	 * Change entry
	 * 
	 * @param Request $request
	 * @return
	 */
    public function changeExecute($request)
    {
		$r = new Request();
		$_s = $r->getSpell();
		$_old = $_s['old'];
		$_new = $_s['new'];

		$_user = 'vesta';

		$_DNS_DOMAIN = $_new['DNS_DOMAIN'];


		if ($_old['IP'] != $_new['IP'])
		{
			$result = array();
			$result = Vesta::execute(Vesta::V_CHANGE_DNS_DOMAIN_IP, array('USER' => $_user, 'DNS_DOMAIN' => $_DNS_DOMAIN, 'IP' => $_new['IP']));
			if(!$result['status'])
			{
				$this->status = FALSE;
				$this->errors['IP_ADDRESS'] = array($result['error_code'] => $result['error_message']);
			}
		}

		if ($_old['TPL'] != $_new['TPL'])
		{
			$result = array();
			$result = Vesta::execute(Vesta::V_CHANGE_DNS_DOMAIN_TPL, array('USER' => $_user, 'DNS_DOMAIN' => $_DNS_DOMAIN, 'IP' => $_new['TPL']));
			if(!$result['status'])
			{
				$this->status = FALSE;
				$this->errors['TPL'] = array($result['error_code'] => $result['error_message']);
			}
		}

		if ($_old['TTL'] != $_new['TTL'])
		{
			$result = array();
			$result = Vesta::execute(Vesta::V_CHANGE_DNS_DOMAIN_TTL, array('USER' => $_user, 'DNS_DOMAIN' => $_DNS_DOMAIN, 'IP' => $_new['TTL']));
			if(!$result['status'])
			{
				$this->status = FALSE;
				$this->errors['TTL'] = array($result['error_code'] => $result['error_message']);
			}
		}

		if ($_old['EXP'] != $_new['EXP'])
		{
			$result = array();
			$result = Vesta::execute(Vesta::V_CHANGE_DNS_DOMAIN_EXP, array('USER' => $_user, 'DNS_DOMAIN' => $_DNS_DOMAIN, 'IP' => $_new['EXP']));
			if (!$result['status'])
			{
				$this->status = FALSE;
				$this->errors['EXP'] = array($result['error_code'] => $result['error_message']);
			}
		}

		if ($_old['SOA'] != $_new['SOA'])
		{
			$result = array();
			$result = Vesta::execute(Vesta::V_CHANGE_DNS_DOMAIN_SOA, array('USER' => $_user, 'DNS_DOMAIN' => $_new['DNS_DOMAIN'], 'IP' => $_new['SOA']));
			if (!$result['status'])
			{
				$this->status = FALSE;
				$this->errors['SOA'] = array($result['error_code'] => $result['error_message']);
			}
		}

		if (!$this->status)
		{
			Vesta::execute(Vesta::V_CHANGE_DNS_DOMAIN_IP, array('USER' => $_user, 'DNS_DOMAIN' => $_DNS_DOMAIN, 'IP' => $_old['IP']));
			Vesta::execute(Vesta::V_CHANGE_DNS_DOMAIN_TPL, array('USER' => $_user, 'DNS_DOMAIN' => $_DNS_DOMAIN, 'IP' => $_old['TPL']));
			Vesta::execute(Vesta::V_CHANGE_DNS_DOMAIN_TTL, array('USER' => $_user, 'DNS_DOMAIN' => $_DNS_DOMAIN, 'IP' => $_old['TTL']));
			Vesta::execute(Vesta::V_CHANGE_DNS_DOMAIN_EXP, array('USER' => $_user, 'DNS_DOMAIN' => $_DNS_DOMAIN, 'IP' => $_old['EXP']));
			Vesta::execute(Vesta::V_CHANGE_DNS_DOMAIN_SOA, array('USER' => $_user, 'DNS_DOMAIN' => $_new['DNS_DOMAIN'], 'IP' => $_old['SOA']));
		}

		return $this->reply($this->status, '');
    }

	/**
	 * Change record
	 * 
	 * @param Request $request
	 * @return
	 */
	public function changeRecordsExecute($request)
    {
		$r = new Request();
		$_s = $r->getSpell();
		$_old = $_s['old'];
		$_new = $_s['new'];
		$_user = 'vesta';
		$_DNS_DOMAIN = $_s['DNS_DOMAIN'];

		foreach ($_new as $record_id => $record_data)
		{
			// checking if record existed - update
			if(is_array($_old[$record_id]))
			{
				// TODO: Remove this. is it necessary??!
				echo '<br> updating'.$record_id;

				$result = Vesta::execute(Vesta::V_CHANGE_DNS_DOMAIN_RECORD, array('USER' => $_user, 'DNS_DOMAIN' => $_DNS_DOMAIN, 'ID' => $record_id, 'RECORD' => $record_data['RECORD'], 'TYPE' => $record_data['RECORD_TYPE'], 'VALUE' => $record_data['RECORD_VALUE']));
				if(!$result['status'])
				{
					$this->status = FALSE;
					$this->errors[$record_id] = array($result['error_code'] => $result['error_message']);
				}
			}
			else
			{
				// record is new - add
				// TODO: Remove this. is it necessary??!
				echo '<br> adding'.$record_id;

				$result = Vesta::execute(Vesta::V_ADD_DNS_DOMAIN_RECORD, array('USER' => $_user, 'DNS_DOMAIN' => $_DNS_DOMAIN, 'RECORD' => $record_data['RECORD'], 'TYPE' => $record_data['RECORD_TYPE'], 'VALUE' => $record_data['RECORD_VALUE'], 'ID' => $record_id));
				if(!$result['status'])
				{
					$this->status = FALSE;
					$this->errors[$record_id] = array($result['error_code'] => $result['error_message']);
				}
			}

			unset($_old[$record_id]);
		}

		// in $_old have remained only record that do not present in new - so they have to be deleted
		foreach ($_old as $record_id => $record_data)
		{
			echo '<br> deleting'.$record_id;		
		}

		return $this->reply($this->status, '');
    }
    
    
}
