<?php

// Password Plugin options
// -----------------------
// A driver to use for password change. Default: "sql".
// See README file for list of supported driver names.
$config['password_driver'] = 'hestia';

// Require the new password to be a certain length.
// set to blank to allow passwords of any length
$config['password_minimum_length'] = 8;

// Require the new password to contain a letter and punctuation character
// Change to false to remove this check.
$config['password_require_nonalpha'] = false;

// Enables logging of password changes into logs/password
$config['password_log'] = false;

// Comma-separated list of login exceptions for which password change
// will be not available (no Password tab in Settings)
$config['password_login_exceptions'] = null;

// By default domains in variables are using unicode.
// Enable this option to use punycoded names
$config['password_idn_ascii'] = false;

// Hestia Driver options
// -----------------------
// Control Panel host
$config['password_hestia_host'] = 'localhost';
$config['password_hestia_port'] = '8083';
