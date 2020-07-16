<?php
// Functions for internationalization
// I18N support information here
$language = 'nl_NL';

putenv("LANGUAGE=$language");
setlocale( LC_ALL, 'C.UTF-8' );

$domain = 'messages';
$localedir = '/usr/local/hestia/web/locale';
bindtextdomain($domain, $localedir);
textdomain($domain);



/**
 * Translates string to given language in first parameter, key given in second parameter (dynamically loads required language). Works like spritf from second parameter
 * @global array $LANG Associative array of language pharses
 * @return string Translated string
 */
function _translate() {

}

/**
 * Translates string by a given key in first parameter to current session language. Works like sprintf
 * @global array $LANG Associative array of language pharses
 * @return string Translated string
 * @see _translate()
 */


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
