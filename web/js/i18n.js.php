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
App.Constants.UNLIM_TRANSLATED_VALUE = '<?php echo __('unlimited') ?>';

App.Constants.FM_DIRECTORY_NAME_CANNOT_BE_EMPTY = '<?php echo __('Directory name cannot be empty') ?>';
App.Constants.FM_FILE_NAME_CANNOT_BE_EMPTY      = '<?php echo __('File name cannot be empty') ?>';
App.Constants.FM_NO_FILE_SELECTED               = '<?php echo __('No file selected') ?>';
App.Constants.FM_NO_FILE_OR_DIRECTORY_SELECTED  = '<?php echo __('No file or folder selected') ?>';
App.Constants.FM_FILE_TYPE_NOT_SUPPORTED        = '<?php echo __('File type not supported') ?>';

