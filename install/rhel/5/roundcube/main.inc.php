<?php

$config = array();
$config['db_dsnw'] = 'mysql://roundcube:%password%@localhost/roundcube';
$config['default_host'] = 'localhost';

$config['smtp_server'] = '';
$config['smtp_port'] = 25;
$config['smtp_user'] = '';
$config['smtp_pass'] = '';
$config['support_url'] = '';

$rcmail_config['log_dir'] = '/var/log/roundcubemail/';
$rcmail_config['temp_dir'] = '/tmp';
$rcmail_config['force_https'] = false;
$rcmail_config['use_https'] = false;
$rcmail_config['login_autocomplete'] = 0;
$rcmail_config['drafts_mbox'] = 'Drafts';
$rcmail_config['junk_mbox'] = 'Spam';
$rcmail_config['sent_mbox'] = 'Sent';
$rcmail_config['trash_mbox'] = 'Trash';
$rcmail_config['default_folders'] = array('INBOX', 'Drafts', 'Sent', 'Spam', 'Trash');
$rcmail_config['create_default_folders'] = true;
$rcmail_config['protect_default_folders'] = true;
$rcmail_config['enable_spellcheck'] = true;
$rcmail_config['spellcheck_dictionary'] = false;
$rcmail_config['spellcheck_engine'] = 'googie';
$rcmail_config['default_charset'] = 'UTF-8';
$rcmail_config['delete_junk'] = true;

$config['product_name'] = 'Roundcube Webmail';
$config['des_key'] = 'rcmail-!24ByteDESkey*Str';

$config['plugins'] = array(
    'archive',
    'zipdownload',
    'password',
);

$config['skin'] = 'larry';
