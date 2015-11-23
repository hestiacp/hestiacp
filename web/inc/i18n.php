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
