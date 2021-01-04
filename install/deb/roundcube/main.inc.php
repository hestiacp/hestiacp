<?php
/*
 +-----------------------------------------------------------------------+
 | Main configuration file                                               |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2011, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+

*/

$config['default_host'] = 'ssl://localhost';
$config['default_port'] = 993;
$config['default_user'] = '%u';
$config['default_pass'] = '%p';

$config['imap_conn_options'] = array(
    'ssl'         => array(
        'verify_peer'  => false,
        'verify_peer_name' => false,
        'verify_depth' => 3,
        'cafile'       => '/etc/ssl/certs/ca-certificates.crt',
      ),
    );

$config['smtp_host'] = 'ssl://localhost';
$config['smtp_port'] = 587;
$config['smtp_user'] = '%u';
$config['smtp_pass'] = '%p';

$config['smtp_conn_options'] = array(
    'ssl'         => array(
        'verify_peer'  => false,
        'verify_peer_name' => false,
        'verify_depth' => 3,
        'cafile'       => '/etc/ssl/certs/ca-certificates.crt',
      ),
    );

$config['product_name'] = 'Roundcube Webmail';
$config['log_dir'] = '/var/log/roundcube/';
$config['login_lc'] = 2;

$config['debug_level'] = 1;
$config['log_driver'] = 'file';
// Log sent messages to <log_dir>/sendmail or to syslog
$config['smtp_log'] = false;
// Log successful logins to <log_dir>/userlogins or to syslog
$config['log_logins'] = false;
// Log session authentication errors to <log_dir>/session or to syslog
$config['log_session'] = true;
// Log SQL queries to <log_dir>/sql or to syslog
$config['sql_debug'] = false;
// Log IMAP conversation to <log_dir>/imap or to syslog
$config['imap_debug'] = true;
// Log LDAP conversation to <log_dir>/ldap or to syslog
$config['ldap_debug'] = false;
$config['smtp_debug'] = true;
// List of active plugins (in plugins/ directory)
$config['plugins'] = array('password','newmail_notifier','zipdownload','archive');
$config['skin'] = 'elastic';

$config['max_recipients'] = 100;

//rewrite below this line
$config['db_dsnw'] = 'mysql://roundcube:%password%@localhost/roundcube';
$config['des_key'] = '%des_key%';
?>