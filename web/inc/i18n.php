<?php
// Functions for internationalization
// I18N support information here

putenv("LANGUAGE=".$_SESSION['language']);
setlocale( LC_ALL, 'C.UTF-8' );

$domain = 'hestiacp';
$localedir = '/usr/local/hestia/web/locale';
bindtextdomain($domain, $localedir);
textdomain($domain);


/**
 * Detects user language from Accept-Language HTTP header.
 * @param string Fallback language (default: 'en')
 * @return string Language code (such as 'en' and 'ja')
 */
function detect_user_language($fallback='en') {
    
}

/**
 * Detects user language .
 * @param string Fallback language (default: 'en')
 * @return string Language code (such as 'en' and 'ja')
 */

function detect_login_language(){

}
