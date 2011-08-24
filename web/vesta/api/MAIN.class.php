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

    /**
     * Get Version
     * 
     * @param Request $request
     * @return string - Ajax Reply
     */ 
    public function versionExecute($request) 
    {
        $result = array(
                    'version' => '1.0',
                    'author'  => 'http://vestacp.com/',
                    'docs'    => 'http://vestacp.com/docs'
                  );

        return $this->reply(true, $result);
    }

    /**
     * Get Initial params.
     * Global constants / variables / configs
     * 
     * @param Request $request
     * @return string - Ajax Reply
     */ 
    public function getInitialExecute($request) 
    {
        require_once V_ROOT_DIR . 'api/IP.class.php';
        require_once V_ROOT_DIR . 'api/USER.class.php';
        // IP
        $ip_obj          = new IP();
        $user_ips        = json_decode($ip_obj->getListUserIpsExecute(), TRUE);
        foreach ($user_ips['data'] as $ip) {
            $ips[$ip['IP_ADDRESS']] = $ip['IP_ADDRESS'];
        }
        // USER
        $user_obj        = new USER();
        $users           = json_decode($user_obj->getListExecute(), TRUE);
        $user_names      = array_keys($users['data']['data']);
        $db_types        = array('mysql' => 'mysql', 'postgress' => 'postgress');
        $interfaces_arr  = json_decode($ip_obj->getSysInterfacesExecute(), TRUE);
        $interfaces      = $interfaces_arr['data'];

        $data_web_domain = array('ips' => $ips);
        $data_ip         = array('user_names' => $user_names, 'interfaces' => $interfaces);
        $data_dns        = array('ips' => $ips);
        $data_db         = array('db_types' => $db_types);
        $data_users      = array('user_names' => $user_names);
    
        $reply = array(
                    'WEB_DOMAIN' => $this->getWebDomainParams($data_web_domain),
                    'CRON'       => $this->getCronParams(),
                    'IP'         => $this->getIpParams($data_ip),
                    'DNS'        => $this->getDnsParams(),
                    'DB'         => $this->getDbParams($data_db),
                    'USERS'      => $this->getUsersParams($data_users),
                    'totals'     => $this->getTotals()
                );

        return $this->reply(true, $reply);
    }
    
    // 
    //
    //
    
    public function getTotals($data = array())
    {
        return array(
                'USER'       => array('total' => 7, 'blocked' => 0),
                'WEB_DOMAIN' => array('total' => 4, 'blocked' => 0),
                'MAIL'       => array('total' => 0),
                'DB'         => array('total' => 4, 'blocked' => 0),
                'DNS'        => array('total' => 4, 'blocked' => 0),
                'IP'         => array('total' => 2, 'blocked' => 0),
                'CRON'       => array('total' => 5, 'blocked' => 0)                
            );
    }
    
    /**
     * WEB DOMAIN initial params
     * 
     * @params array $data
     * @return array
     */
    public function getWebDomainParams($data = array())
    {
        return array(
                'TPL' => array('default' => 'default'),
                'ALIAS' => array(),
                'STAT' => array(
                            'webalizer' => 'webalizer',
                            'awstats' => 'awstats'
                          ),
                'IP' => $data['ips']
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
    public function getIpParams($data = array())
    {
        return array(
                'SYS_USERS' => $data['user_names'],
                'STATUSES' => array(
                                'shared' => 'shared',
                                'exclusive' => 'exclusive'
                              ),
                'INTERFACES' => $data['interfaces'],
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
                'OWNER' => array('Chuck Norris' => 'Chuck Norris')
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
        return  array(
                'IP' => $data['ips'],
                'TPL' => array('default' => 'default'),
                'EXP' => array(),
                'SOA' => array(),
                'TTL' => array(),
                'record' => array(
                                'RECORD' => array(),
                                'RECORD_TYPE' => array('a' => 'a', 'reverse' => 'reverse'),
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
        return array(
                    'TYPE' => $data['db_types'],
                    'HOST' => array('vestacp.com' => 'vestacp.com', 'askcow.org' => 'askcow.org')
                );
    }
    
    /**
     * Users initial params
     * 
     * @params array $data
     * @return array
     */
    public function getUsersParams($data = array())
    {
        return array(
                'ROLE'      => array('user' => 'user'),
                'OWNER'     => $data['user_names'],
                'PACKAGE'   => array('default' => 'default'),
                'NS1'       => array('' => ''),
                'NS2'       => array('' => ''),
                'SHELL'     => array(
                                '/bin/sh' => '/bin/sh',
                                '/bin/bash' => '/bin/bash',
                                '/sbin/nologin' => '/sbin/nologin',
                                '/bin/tcsh' => '/bin/tcsh',
                                '/bin/csh' => '/bin/csh')
                );
    }
        
}
