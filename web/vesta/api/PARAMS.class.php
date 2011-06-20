<?php

/**
 * PARAMS
 * 
 * @author Naumov-Socolov <naumov.socolov@gmail.com>
 * @author Malishev Dima <dima.malishev@gmail.com>
 * @author vesta, http://vestacp.com/
 * @copyright vesta 2010-2011 
 */
class PARAMS extends AjaxHandler 
{
    /**
	 * Get initial params
	 * 
	 * @param Request $request
	 * @return
	 */
    public function getInitialExecute($request) 
    {
		require_once V_ROOT_DIR . 'api/IP.class.php';
		require_once V_ROOT_DIR . 'api/USER.class.php';
        
        $ip_obj = new IP();
        $user_obj = new USER();
         
        $user_ips = json_decode($ip_obj->getListUserIpsExecute(), TRUE);
		foreach ($user_ips['data'] as $ip)
		{
			$ips[$ip['IP_ADDRESS']] = $ip['IP_ADDRESS'];
		}	
       
        $users = json_decode($user_obj->getListExecute(), TRUE);
		$user_names = array_keys($users['data']['data']);

		$db_types = array(
						  'mysql' => 'mysql', 
						  'postgress' => 'postgress'
						 );

		$interfaces_arr = json_decode($ip_obj->getSysInterfacesExecute(), TRUE);
		$interfaces = $interfaces_arr['data'];

        $reply = array(
            'WEB_DOMAIN' => array(
								'TPL' => array('default' => 'default'),
								'ALIAS' => array(),
								'STAT' => array(
										'webalizer' => 'webalizer',
										'awstats' => 'awstats'),
								'IP' => $ips
							),
			'CRON' 		=> array(),
			'IP' 		=> array(
							'SYS_USERS' => $user_names,
							'STATUSES' => array(
											'shared' => 'shared',
											'exclusive' => 'exclusive'
										),
							'INTERFACES' => $interfaces,
							'MASK' => array(
										'255.255.255.0' => '255.255.255.0',
										'255.255.255.128' => '255.255.255.128',
										'255.255.255.192' => '255.255.255.192',
										'255.255.255.224' => '255.255.255.224', 
										'255.255.255.240' => '255.255.255.240', 
										'255.255.255.248' => '255.255.255.248',
										'255.255.255.252' => '255.255.255.252',
										'255.255.255.255' => '255.255.255.255'
									),
							'OWNER' => array()
						),
			'DNS' 		=> array(
							'IP' => $ips,
							'TPL' => array(
										'default' => 'default'
									),
							'EXP' => array(),
							'SOA' => array(),
							'TTL' => array(),
							'record' => array(
										'RECORD' => array(),
										'RECORD_TYPE' => array('a' => 'a', 'reverse' => 'reverse'),
										'RECORD_VALUE' => array()
									)
						),
			'DB' 		=> array(
							'TYPE' => $db_types
							),
			'USERS' 	=> array(
								'ROLE' => array('user' => 'user'),
								'OWNER' => $user_names,
								'PACKAGE' => array('default' => 'default'),
								'NS1' => array('' => ''),
								'NS2' => array('' => ''),
								'SHELL' => array(
											'/bin/sh' => '/bin/sh',
											'/bin/bash' => '/bin/bash',
											'/sbin/nologin' => '/sbin/nologin',
											'/bin/tcsh' => '/bin/tcsh',
											'/bin/csh' => '/bin/csh')
										)
		);

        return $this->reply(true, $reply);
    }
}
