<?php

/**
 * Api Main class
 * Calls / Executes native vesta methods
 * 
 * @author vesta, http://vestacp.com/
 * @author Dmitry Malishev <dima.malishev@gmail.com>
 * @author Dmitry Naumov-Socolov <naumov.socolov@gmail.com>
 * @copyright vesta 2010-2011
 */
class Vesta 
{
    const SAME_PASSWORD			            = '********'; 

    const ADMIN                             = 1; 
    const USER                              = 0; 

    const PARAM_DELIMETER                   = ' ';


    // commands 

    const V_LIST_SYS_CONFIG                 = 'v_list_sys_config';

    // USER
    const V_GET_SYS_USER_VALUE		        = 'v_get_user_value';
    const V_LIST_SYS_USERS                  = 'v_list_users';
    const V_LIST_SYS_USER                   = 'v_list_user';
    const V_LIST_USER_PACKAGES              = 'v_list_user_packages';
    const V_ADD_SYS_USER                    = 'v_add_user';
    const V_ADD_SYS_USER_REPORTS            = 'v_add_user_reports';
    const V_CHANGE_SYS_USER_CONTACT         = 'v_change_user_contact';
    const V_CHANGE_SYS_USER_NS              = 'v_change_user_ns';
    const V_CHANGE_SYS_USER_PACKAGE         = 'v_change_user_package';
    const V_CHANGE_SYS_USER_PASSWORD        = 'v_change_user_password';
    const V_CHANGE_SYS_USER_SHELL           = 'v_change_user_shell';
    const V_CHANGE_SYS_USER_ROLE            = 'v_change_sys_user_role';
    const V_CHANGE_SYS_USER_NAME	        = 'v_change_user_name';
    const V_SUSPEND_SYS_USER				= 'v_suspend_user';
    const V_UNSUSPEND_SYS_USER				= 'v_unsuspend_user';
    const V_DEL_SYS_USER                    = 'v_delete_user';
    const V_DEL_SYS_USER_REPORTS            = 'v_delete_user_reports';
    // WEB_DOMAIN
    const V_LIST_WEB_DOMAINS                = 'v_list_web_domains';
    const V_LIST_WEB_DOMAINS_ALIAS          = 'v_list_web_domains_alias';
    const V_LIST_WEB_DOMAINS_ELOG           = 'v_list_web_domains_elog';
    const V_LIST_WEB_DOMAINS_PROXY          = 'v_list_web_domains_proxy';
    const V_LIST_WEB_DOMAINS_SSL            = 'v_list_web_domains_ssl';
    const V_LIST_WEB_DOMAINS_STATS          = 'v_list_web_domains_stats';
    const V_LIST_WEB_DOMAIN_SSL             = 'v_list_web_domain_ssl';
    const V_LIST_WEB_TEMPLATES              = 'v_list_web_templates';
    const V_ADD_WEB_DOMAIN                  = 'v_add_web_domain';
    const V_ADD_WEB_DOMAIN_ALIAS            = 'v_add_web_domain_alias';
    const V_ADD_WEB_DOMAIN_STAT             = 'v_add_web_domain_stat';
    const V_ADD_WEB_DOMAIN_STAT_AUTH        = 'v_add_web_domain_stat_auth';
    const V_ADD_WEB_DOMAIN_SSL              = 'v_add_web_domain_ssl';
    const V_ADD_WEB_DOMAIN_ELOG             = 'v_add_web_domain_elog';
    const V_ADD_WEB_DOMAIN_CGI              = 'v_add_web_domain_cgi';
    const V_CHANGE_WEB_DOMAIN_IP            = 'v_change_web_domain_ip';
    const V_CHANGE_WEB_DOMAIN_SSL           = 'v_change_web_domain_sslcert';
    const V_CHANGE_WEB_DOMAIN_SSLHOME       = 'v_change_web_domain_sslhome';
    const V_CHANGE_WEB_DOMAIN_TPL           = 'v_change_web_domain_tpl';
    const V_DEL_WEB_DOMAIN_CGI              = 'v_delete_web_domain_cgi';
    const V_DEL_WEB_DOMAIN_ELOG             = 'v_delete_web_domain_elog';
    const V_DEL_WEB_DOMAIN_SSL              = 'v_delete_web_domain_ssl';
    const V_DEL_WEB_DOMAIN_STAT             = 'v_delete_web_domain_stat';
    const V_DEL_WEB_DOMAIN_STAT_AUTH        = 'v_delete_web_domain_stat_auth';
    const V_DEL_WEB_DOMAIN_ALIAS            = 'v_delete_web_domain_alias';
    const V_UPD_WEB_DOMAIN_DISK             = 'v_update_web_domain_disk';
    const V_UPD_WEB_DOMAINS_DISK            = 'v_update_web_domains_disk';
    const V_UPD_WEB_DOMAIN_TRAFF            = 'v_update_web_domain_traff';
    const V_UPD_WEB_DOMAINS_TRAFF           = 'v_update_web_domains_traff';
    const V_SUSPEND_WEB_DOMAIN              = 'v_suspend_web_domain';
    const V_SUSPEND_WEB_DOMAINS             = 'v_suspend_web_domains';
    const V_UNSUSPEND_WEB_DOMAIN            = 'v_unsuspend_web_domain';
    const V_UNSUSPEND_WEB_DOMAINS           = 'v_unsuspend_web_domains';
    const V_DEL_WEB_DOMAIN                  = 'v_delete_web_domain';
    // BACKUP
    const V_LIST_SYS_USER_BACKUPS			= 'v_list_user_backups';
    // IP
    const V_LIST_SYS_IPS                    = 'v_list_sys_ips';
    const V_LIST_USER_IPS 		            = 'v_list_user_ips';
    const V_LIST_SYS_USER_IPS 		        = 'v_list_sys_user_ips';
    const V_ADD_SYS_IP                      = 'v_add_sys_ip';
    const V_ADD_SYS_USER_IP                 = 'v_add_user_ip'; 
    const V_CHANGE_SYS_IP_OWNER             = 'v_change_sys_ip_owner';
    const V_CHANGE_SYS_IP_NAME              = 'v_change_sys_ip_name';
    const V_CHANGE_SYS_IP_STATUS            = 'v_change_sys_ip_status';    
    const V_DEL_SYS_IP                      = 'v_delete_sys_ip';    
    const V_UPD_SYS_IP                      = 'v_update_sys_ip';
    const V_LIST_SYS_INTERFACES             = 'v_list_sys_interfaces';
    // DNS
    const V_LIST_DNS_DOMAINS                = 'v_list_dns_domains';
    const V_LIST_DNS_DOMAIN_RECORDS         = 'v_list_dns_domain';
    const V_LIST_DNS_TEMPLATES              = 'v_list_dns_templates';
    const V_ADD_DNS_DOMAIN                  = 'v_add_dns_domain';
    const V_ADD_DNS_DOMAIN_RECORD           = 'v_add_dns_domain_record';
    const V_CHANGE_DNS_DOMAIN_IP            = 'v_change_dns_domain_ip';
    const V_CHANGE_DNS_DOMAIN_SOA           = 'v_change_dns_domain_soa';
    const V_CHANGE_DNS_DOMAIN_TPL           = 'v_change_dns_domain_tpl';
    const V_CHANGE_DNS_DOMAIN_TTL           = 'v_change_dns_domain_ttl';
    const V_CHANGE_DNS_DOMAIN_EXP           = 'v_change_dns_domain_exp';
    const V_CHANGE_DNS_DOMAIN_RECORD        = 'v_change_dns_domain_record';
    const V_SUSPEND_DNS_DOMAIN              = 'v_suspend_dns_domain';
    const V_UNSUSPEND_DNS_DOMAIN            = 'v_unsuspend_dns_domain';
    const V_DEL_DNS_DOMAIN                  = 'v_delete_dns_domain';
    const V_DEL_DNS_DOMAIN_RECORD           = 'v_delete_dns_domain_record';
    // DB    
    const V_LIST_DB_BASES                   = 'v_list_db_bases';
    const V_LIST_DB_HOSTS                   = 'v_list_db_hosts';
    const V_LIST_WEB_DOMAIN_ALIAS	        = 'v_list_web_domain_alias';
    const V_ADD_DB_BASE                     = 'v_add_db_base';
    const V_ADD_DB_HOST                     = 'v_add_db_host';
    const V_CHANGE_DB_PASSWORD              = 'v_change_db_password';
    const V_UPD_DB_BASE_DISK                = 'v_update_db_base_disk';
    const V_UPD_DB_BASES_DISK               = 'v_update_db_bases_disk';
    const V_SUSPEND_DB_BASE                 = 'v_suspend_db_base';
    const V_SUSPEND_DB_BASES                = 'v_suspend_db_bases';
    const V_UNSUSPEND_DB_BASE               = 'v_unsuspend_db_base';
    const V_UNSUSPEND_DB_BASES              = 'v_unsuspend_db_bases';
    const V_DEL_DB_BASE                     = 'v_delete_db_base';
    const V_DEL_DB_HOST                     = 'v_delete_db_host';
    // CRON
    const V_LIST_CRON_JOBS                  = 'v_list_cron_jobs';
    const V_ADD_CRON_JOB                    = 'v_add_cron_job';
    //    const V_ADD_SYS_USER_REPORTS            = 'v_add_user_reports';
    const V_CHANGE_CRON_JOB                 = 'v_change_cron_job';
    const V_SUSPEND_CRON_JOB                = 'v_suspend_cron_job';
    const V_SUSPEND_CRON_JOBS               = 'v_suspend_cron_jobs';
    const V_UNSUSPEND_CRON_JOB              = 'v_unsuspend_cron_job';
    const V_UNSUSPEND_CRON_JOBS             = 'v_unsuspend_cron_jobs';
    const V_DEL_CRON_JOB                    = 'v_delete_cron_job';
    //    const V_DEL_SYS_USER_REPORTS            = 'v_delete_user_reports';
    // STATS
    const V_LIST_SYS_RRD                    = 'v_list_sys_rrd json';
    const V_UPDATE_SYS_RRD                  = 'v_update_sys_rrd';

    /**
     * Execute vesta command
     * 
     * @param string $cms_command
     * @param array $parameters
     * @return string
     */
    static function execute($cmd_command, $parameters = array(), $reply = '') 
    {
        $r = new Request();
        $_DEBUG = $r->getParameter("debug", FALSE);

        if (!isset($cmd_command)) {
            throw new ProtectionException('No function name passed into Vesta::execute'); // TODO: move msg to Messages::
        }
 
        if(!self::hasRights((int)VestaSession::getInstance()->getUserRole(), $cmd_command)){
            return array('status' => 'TRUE');
        }

        $reply_type = $reply;
        if ($reply != AjaxHandler::JSON) {
            $reply = '';
        }    

        $params = array(
                    'sudo'       => Config::get('sudo_path'),
                    'functions'  => Config::get('vesta_functions_path'),
                    'parameters' => is_array($parameters) ? "'".implode("' '", $parameters)."'" : $parameters,
                    'reply'      => $reply
                  );

        // e.g.: /usr/bin/sudo /usr/local/vesta/bin/v_list_users vesta json 
        $cmd = "{$params['sudo']} {$params['functions']}{$cmd_command} {$params['parameters']} {$params['reply']}";
        
        exec($cmd, $output, $return);
        $result = 0;
        $result = array(
                        'status'        => TRUE,
                        'data'          => '',
                        'error_code'    => '',
                        'error_message' => ''
                  );

        // TODO: please remove this later :)
        if ($_DEBUG) {
            $result['debug'] = array(
                     "cmd" => $cmd,
                     "output" => $output,
                     "return" => $return
                 );
            if ($_DEBUG == 2) {
                echo '<p>'.$cmd;
                echo '<br> output: '; print_r($output);
                echo '<br> return: '.$return;
                echo '</p>';
            }
        }

        if (!!(int)$return) { 
            $result['status'] = FALSE;
            $result['error_code'] = (int)$return;
            $result['error_message'] = implode('', $output);

	    return $result;
        }
        
	if ($reply_type == 'text') {
	    $result['data'] = implode('', $output);
	} 
	else {
            $result['data'] = json_decode(implode('', $output), true);
        }
    
        return $result;
    }  

    /**
     * User Rights management
     * 
     * @params array $commands
     * @params int $role
     * @return BOOL
     */
    public function hasRights($role, $command)
    {        
      //      return TRUE;
      //        echo 'role - '.$role;
      //        exit();

        $rights = array(
            self::ADMIN =>  array(),
            self::USER =>   array(
// sys 
                self::V_LIST_SYS_CONFIG, 
// user
                self::V_GET_SYS_USER_VALUE, self::V_LIST_SYS_USERS, self::V_ADD_SYS_USER, self::V_CHANGE_SYS_USER_CONTACT, self::V_CHANGE_SYS_USER_NS, self::V_CHANGE_SYS_USER_PACKAGE, self::V_CHANGE_SYS_USER_PASSWORD, self::V_CHANGE_SYS_USER_SHELL, self::V_CHANGE_SYS_USER_ROLE, self::V_CHANGE_SYS_USER_NAME, self::V_SUSPEND_SYS_USER, self::V_UNSUSPEND_SYS_USER, self::V_DEL_SYS_USER, 
                'login_as',

// ip 
                self::V_LIST_SYS_IPS, self::V_ADD_SYS_IP, self::V_ADD_SYS_USER_IP, self::V_DEL_SYS_IP, self::V_UPD_SYS_IP, self::V_CHANGE_SYS_IP_OWNER, self::V_CHANGE_SYS_IP_NAME, self::V_CHANGE_SYS_IP_STATUS, self::V_UPD_SYS_IP, self::V_LIST_SYS_INTERFACES, 

// web domain
                self::V_UPD_WEB_DOMAIN_DISK, self::V_UPD_WEB_DOMAINS_DISK, self::V_UPD_WEB_DOMAIN_TRAFF, self::V_UPD_WEB_DOMAINS_TRAFF, self::V_SUSPEND_WEB_DOMAIN, self::V_SUSPEND_WEB_DOMAINS, self::V_UNSUSPEND_WEB_DOMAIN, self::V_UNSUSPEND_WEB_DOMAINS, 

// dns 
                self::V_SUSPEND_DNS_DOMAIN, self::V_UNSUSPEND_DNS_DOMAIN, 

// db 
                self::V_ADD_DB_HOST, self::V_DEL_DB_HOST, self::V_UPD_DB_BASE_DISK, self::V_UPD_DB_BASES_DISK, self::V_SUSPEND_DB_BASE, self::V_SUSPEND_DB_BASES, self::V_UNSUSPEND_DB_BASE, self::V_UNSUSPEND_DB_BASES,

// cron 
                self::V_ADD_SYS_USER_REPORTS, self::V_DEL_SYS_USER_REPORTS, self::V_SUSPEND_CRON_JOB, self::V_SUSPEND_CRON_JOBS, self::V_UNSUSPEND_CRON_JOB, self::V_UNSUSPEND_CRON_JOBS, 

// backups 
                self::V_LIST_SYS_USER_BACKUPS
            )
        );


        if(in_array($command, $rights[$role])){
                return FALSE; 
        }

        return TRUE;
    }

}
