<?php

// Password Plugin options
// -----------------------
// A driver to use for password change. Default: "sql".
// See README file for list of supported driver names.
$rcmail_config['password_driver'] = 'vesta';

// Require the new password to be a certain length.
// set to blank to allow passwords of any length
$rcmail_config['password_minimum_length'] = 6;

// Require the new password to contain a letter and punctuation character
// Change to false to remove this check.
$rcmail_config['password_require_nonalpha'] = false;

// Enables logging of password changes into logs/password
$rcmail_config['password_log'] = false;

// Comma-separated list of login exceptions for which password change
// will be not available (no Password tab in Settings)
$rcmail_config['password_login_exceptions'] = null;


// By default domains in variables are using unicode.
// Enable this option to use punycoded names
$rcmail_config['password_idn_ascii'] = false;

// Vesta Driver options
// -----------------------
// Control Panel host
$rcmail_config['password_vesta_host'] = 'localhost';
$rcmail_config['password_vesta_port'] = '8083';
