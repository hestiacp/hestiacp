<?php
session_start();
if (empty($_SESSION['language'])) {
    $_SESSION['language'] = 'en';
}
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/i18n/'.$_SESSION['language'].'.php');

if (!function_exists('_translate')) {
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
}

if (!function_exists('__')) {
    function __() {
        $args = func_get_args();
        array_unshift($args,$_SESSION['language']);
        return call_user_func_array("_translate",$args);
    }
}
?>

App.i18n.ARE_YOU_SURE     = '<?php echo __('Are you sure?') ?>';
App.Constants.UNLIM_VALUE = '<?php echo __('unlimited') ?>';
