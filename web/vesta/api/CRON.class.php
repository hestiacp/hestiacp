<?php

/**
 * CRON
 * 
 * @author Naumov-Socolov <naumov.socolov@gmail.com>
 * @author Malishev Dima <dima.malishev@gmail.com>
 * @author vesta, http://vestacp.com/
 * @copyright vesta 2010-2011
 */
class CRON extends AjaxHandler 
{
  
	/**
	 * List cron entries
	 * 
	 * @param Request $request
	 * @return
	 */
	public function getListExecute($request) 
	{
		$_user = 'vesta';
		$reply = array();

		$result = Vesta::execute(Vesta::V_LIST_CRON_JOBS, 
								array($_user, Config::get('response_type')));

		foreach ($result['data'] as $id => $record) 
		{
			$reply[$id] = array(
				'CMD' => $record['CMD'],
				'MIN' => $record['MIN'],
				'HOUR' => $record['HOUR'],
				'DAY' => $record['DAY'],
				'MONTH' => $record['MONTH'],
				'WDAY' => $record['WDAY'],
				'SUSPEND' => $record['SUSPEND'],
				'DATE' => date(Config::get('ui_date_format', strtotime($record['DATE'])))
			);
		}

		if (!$result['status']) 
		{
			$this->errors[] = array($result['error_code'] => $result['error_message']);
		}

		return $this->reply($result['status'], $reply);
	}

	/**
	 * Add cron entry
	 * 
	 * @param Request $request
	 * @return
	 */
	public function addExecute($request) 
	{
		$r = new Request();
		$_s = $r->getSpell();
		$_user = 'vesta';

		$params = array(
			'USER' => $_user,
			'MIN' => $_s['MIN'],
			'HOUR' => $_s['HOUR'],
			'DAY' => $_s['DAY'],
			'MONTH' => $_s['MONTH'],
			'WDAY' => $_s['WDAY'],
			'CMD' => $_s['CMD']
		);

		$result = Vesta::execute(Vesta::V_ADD_CRON_JOB, $params);

		if ($_s['REPORTS'])
		{
			$result = array();
			$result = Vesta::execute(Vesta::V_ADD_SYS_USER_REPORTS, 
									 array('USER' => $_user));
			if (!$result['status'])
			{
				$this->status = FALSE;
				$this->errors['REPORTS'] = array($result['error_code'] => $result['error_message']);
			}
		}

		if (!$result['status']) 
		{
			$this->errors[] = array($result['error_code'] => $result['error_message']);
		}

		return $this->reply($result['status'], $result['data']);
	}
      
    /**
	 * Delete cron entry
	 * 
	 * @param Request $request
	 * @return
	 */  
	function delExecute($request) 
	{
		$r = new Request();
		$_s = $r->getSpell();
		$_user = 'vesta';

		$params = array(
				'USER' => $_user,
				'JOB' => $_s['JOB']
		);
		$result = Vesta::execute(Vesta::V_DEL_CRON_JOB, $params);

		if (!$result['status']) 
		{
			$this->errors[] = array($result['error_code'] => $result['error_message']);
		}

		return $this->reply($result['status'], $result['data']);
	}
  
	/**
	 * Change cron entry
	 * 
	 * @param Request $request
	 * @return
	 */
	function changeExecute($request)
	{
		$r = new Request();
		$_s = $r->getSpell();
		$_old = $_s['old'];
		$_new = $_s['new'];

		$_user = 'vesta';
		$_JOB = $_new['JOB'];

		$result = array();
		$params = array(
			'USER' => $_user,
			'JOB' => $_JOB,
			'MIN' => $_new['MIN'],
			'HOUR' => $_new['HOUR'],
			'DAY' => $_new['DAY'],
			'MONTH' => $_new['MONTH'],
			'WDAY' => $_new['WDAY'],
			'CMD' => $_new['CMD']
		);

		$result = Vesta::execute(Vesta::V_CHANGE_CRON_JOB, $params);

		if (!$result['status']) 
		{
			$this->errors[] = array($result['error_code'] => $result['error_message']);
		}

		return $this->reply($result['status'], $result['data']);
	}

	/**
	 * Suspend cron entry
	 * 
	 * @param Request $request
	 * @return
	 */
	function suspendExecute($request)
	{
		$r = new Request();
		$_s = $r->getSpell();

		$_user = 'vesta';
		$_JOB = $_s['JOB'];

		$params = array(
			'USER' => $_user,
			'JOB' => $_JOB
		);

		$result = Vesta::execute(Vesta::V_SUSPEND_CRON_JOB, $params);
		if (!$result['status']) 
		{
			$this->errors[] = array($result['error_code'] => $result['error_message']);
		}

		return $this->reply($result['status'], $result['data']);
	}


	/**
	 * Unsuspend cron entry
	 * 
	 * @param Request $request
	 * @return
	 */
	function unsuspendExecute($request)
	{
		$r = new Request();
		$_s = $r->getSpell();

		$_user = 'vesta';
		$_JOB = $_s['JOB'];

		$params = array(
			'USER' => $_user,
			'JOB' => $_JOB
		);

		$result = Vesta::execute(Vesta::V_UNSUSPEND_CRON_JOB, $params);

		if (!$result['status']) 
		{
			$this->errors[] = array($result['error_code'] => $result['error_message']);
		}

		return $this->reply($result['status'], $result['data']);
	}

	/**
	 * Batch suspend cron entries
	 * 
	 * @param Request $request
	 * @return
	 */
	function suspendAllExecute($request)
	{
		$r = new Request();
		$_s = $r->getSpell();

		$_user = 'vesta';
		$_JOB = $_s['JOB'];

		$params = array(
			'USER' => $_user
		);

		$result = Vesta::execute(Vesta::V_SUSPEND_CRON_JOBS, $params);

		if (!$result['status']) 
		{
			$this->errors[] = array($result['error_code'] => $result['error_message']);
		}

		return $this->reply($result['status'], $result['data']);
	}
	
	/**
	 * Batch suspend cron entry
	 * 
	 * @param Request $request
	 * @return
	 */
	function unsuspendAllExecute($request)
	{
		$r = new Request();
		$_s = $r->getSpell();

		$_user = 'vesta';

		$params = array(
			'USER' => $_user
		);

		$result = Vesta::execute(Vesta::V_UNSUSPEND_CRON_JOBS, $params);

		if (!$result['status'])
		{
			$this->errors[] = array($result['error_code'] => $result['error_message']);
		}

		return $this->reply($result['status'], $result['data']);
	}
   
}
