<?php
// Example usage 
// change_password.php "admin_account admin_password mysql_password"
$_ENV['RAINLOOP_INCLUDE_AS_API'] = true;
include '/var/lib/rainloop/index.php';

$oConfig = \RainLoop\Api::Config();
// Change default login data / key
$oConfig->Set('security', 'admin_login', $argv[1]);
$oConfig->Set('security', 'admin_panel_key', $argv[1]);
$oConfig->SetPassword($argv[2]);
// Allow Contacts to be saved in database
$oConfig->Set('contacts', 'enable', 'On');
$oConfig->Set('contacts', 'allow_sync', 'On');
$oConfig->Set('contacts', 'type', 'mysql');
$oConfig->Set('contacts', 'pdo_dsn', 'mysql:host=127.0.0.1;port=3306;dbname=rainloop');
$oConfig->Set('contacts', 'pdo_user', 'rainloop');
$oConfig->Set('contacts', 'pdo_password', $argv[3]);
// Plugins
$oConfig->Set('plugins', 'enable', 'On');
$oConfig->Set('plugins', 'enabled_list', 'add-x-originating-ip-header,hestia-change-password');
$oConfig->Save();
?>