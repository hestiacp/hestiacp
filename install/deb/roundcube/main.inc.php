<?php
/* Local configuration for Roundcube Webmail */

//rewrite below this line
$config['db_dsnw'] = 'mysql://roundcube:%password%@localhost/roundcube';

// Log sent messages to <log_dir>/sendmail or to syslog
$config['smtp_log'] = false;

// Log IMAP conversation to <log_dir>/imap or to syslog
$config['imap_debug'] = true;

// Log SMTP conversation to <log_dir>/smtp.log or to syslog
$config['smtp_debug'] = true;

// ----------------------------------
// IMAP
// ----------------------------------
// The IMAP host chosen to perform the log-in.
// Leave blank to show a textbox at login, give a list of hosts
// to display a pulldown menu or set one host as string.
// Enter hostname with prefix ssl:// to use Implicit TLS, or use
// prefix tls:// to use STARTTLS.
// Supported replacement variables:
// %n - hostname ($_SERVER['SERVER_NAME'])
// %t - hostname without the first part
// %d - domain (http hostname $_SERVER['HTTP_HOST'] without the first part)
// %s - domain name after the '@' from e-mail address provided at login screen
// For example %n = mail.domain.tld, %t = domain.tld
// WARNING: After hostname change update of mail_host column in users table is
//          required to match old user data records with the new host.
$config['default_host'] = 'ssl://localhost';

// TCP port used for IMAP connections
$config['default_port'] = 993;

// IMAP socket context options
// See http://php.net/manual/en/context.ssl.php
// The example below enables server certificate validation
//$config['imap_conn_options'] = array(
//  'ssl'         => array(
//     'verify_peer'  => true,
//     'verify_depth' => 3,
//     'cafile'       => '/etc/openssl/certs/ca.crt',
//   ),
// );
// Note: These can be also specified as an array of options indexed by hostname
$config['imap_conn_options'] = array (
  'ssl' => 
  array (
    'verify_peer' => false,
    'verify_peer_name' => false,
    'verify_depth' => 3,
    'cafile' => '/etc/ssl/certs/ca-certificates.crt',
  ),
);

// SMTP socket context options
// See http://php.net/manual/en/context.ssl.php
// The example below enables server certificate validation, and
// requires 'smtp_timeout' to be non zero.
// $config['smtp_conn_options'] = array(
//   'ssl'         => array(
//     'verify_peer'  => true,
//     'verify_depth' => 3,
//     'cafile'       => '/etc/openssl/certs/ca.crt',
//   ),
// );
// Note: These can be also specified as an array of options indexed by hostname
$config['smtp_conn_options'] = array (
  'ssl' => 
  array (
    'verify_peer' => false,
    'verify_peer_name' => false,
    'verify_depth' => 3,
    'cafile' => '/etc/ssl/certs/ca-certificates.crt',
  ),
);

// provide an URL where a user can get support for this Roundcube installation
// PLEASE DO NOT LINK TO THE ROUNDCUBE.NET WEBSITE HERE!
$config['support_url'] = '';

// use this folder to store log files
// must be writeable for the user who runs PHP process (Apache user if mod_php is being used)
// This is used by the 'file' log driver.
$config['log_dir'] = '/var/log/roundcube/';

// This key is used for encrypting purposes, like storing of imap password
// in the session. For historical reasons it's called DES_key, but it's used
// with any configured cipher_method (see below).
// For the default cipher_method a required key length is 24 characters.
$config['des_key'] = '%des_key%';

// Maximum number of recipients per message (including To, Cc, Bcc).
// Default: 0 (no limit)
$config['max_recipients'] = 100;

// List of active plugins (in plugins/ directory)
$config['plugins'] = array('password', 'newmail_notifier', 'zipdownload', 'archive', 'managesieve');

$config['default_user'] = '%u';

$config['default_pass'] = '%p';

$config['smtp_host'] = 'ssl://localhost';

// Log session authentication errors to <log_dir>/session or to syslog
$config['log_session'] = true;
?>