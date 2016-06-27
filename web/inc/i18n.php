<?php
// Functions for internationalization

/**
 * Translates string to given language in first parameter, key given in second parameter (dynamically loads required language). Works like spritf from second parameter
 * @global array $LANG Associative array of language pharses
 * @return string Translated string
 */
function _translate() {
    global $LANG;

    $args = func_get_args();
    $l = $args[0];

    if (!$l) return 'NO LANGUAGE DEFINED';
    $key = $args[1];

    if (!isset($LANG[$l])) {
        require_once($_SERVER['DOCUMENT_ROOT'].'/inc/i18n/'.$l.'.php');
    }

    if (!isset($LANG[$l][$key])) {
        $text=$key;
    } else {
        $text=$LANG[$l][$key];
    }

    array_shift($args);
    if (count($args)>1) {
        $args[0] = $text;
        return call_user_func_array("sprintf",$args);
    } else {
        return $text;
    }
}

/**
 * Translates string by a given key in first parameter to current session language. Works like sprintf
 * @global array $LANG Associative array of language pharses
 * @return string Translated string
 * @see _translate()
 */
function __() {
    $args = func_get_args();
    array_unshift($args,$_SESSION['language']);
    return call_user_func_array("_translate",$args);
}

/**
 * Detects user language from Accept-Language HTTP header.
 * @param string Fallback language (default: 'en')
 * @return string Language code (such as 'en' and 'ja')
 */
function detect_user_language($fallback='en') {
    static $user_lang = '';

    // Already detected
    if (!empty($user_lang)) return $user_lang;

    // Check if Accept-Language header is available
    if (!isset($_SERVER) ||
        !isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ||
        !is_string($_SERVER['HTTP_ACCEPT_LANGUAGE'])
    ) {
        // Store result for reusing
        $user_lang = $fallback;
        return $user_lang;
    }


    // Sort Accept-Language by `q` value
    $accept_langs = explode(',', preg_replace('/\s/', '', strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE'])));
    $accept_langs_sorted = array() ;
    foreach ($accept_langs as $lang) {
        $div = explode(';q=', $lang, 2);
        if (count($div) < 2) {
            // `q` value was not specfied
            // -> Set default `q` value (1)
            $div[] = '1';
        }
        list($code, $q) = $div;
        if (preg_match('/^[\w\-]+$/', $code)) {
            // Acceptable language code
            $accept_langs_sorted[$code] = (double)$q;
        }
    }
    arsort($accept_langs_sorted);

    // List languages
    exec (VESTA_CMD."v-list-sys-languages json", $output, $return_var);
    $languages = json_decode(implode('', $output), true);
    unset($output);

    // Find best matching language
    foreach ($accept_langs_sorted as $user_lang => $dummy) {
        $decision = '';
        foreach ($languages as $prov_lang) {
            if (strlen($decision) > strlen($prov_lang)) continue;
            if (strpos($user_lang, $prov_lang) !== false) {
                $decision = $prov_lang;
            }
        }
        if (!empty($decision)) {
            // Store result for reusing
            $user_lang = $decision;
            return $user_lang;
        }
    }

    // Store result for reusing
    $user_lang = $fallback;
    return $user_lang;
}

/**
 * Detects user language .
 * @param string Fallback language (default: 'en')
 * @return string Language code (such as 'en' and 'ja')
 */

function detect_login_language(){

}
