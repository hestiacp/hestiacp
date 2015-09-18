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

App.Constants.FM_DIRECTORY_NOT_AVAILABLE        = '<?php echo __('Directory not available') ?>';
App.Constants.FM_DONE                           = '<?php echo __('Done') ?>';
App.Constants.FM_CLOSE                          = '<?php echo __('Close') ?>';
App.Constants.FM_COPY                           = '<?php echo __('Copy') ?>';
App.Constants.FM_CANCEL                         = '<?php echo __('Cancel') ?>';
App.Constants.FM_RENAME                         = '<?php echo __('Rename') ?>';
App.Constants.FM_DELETE                         = '<?php echo __('Delete') ?>';
App.Constants.FM_EXTRACT                        = '<?php echo __('Extract') ?>';
App.Constants.FM_CREATE                         = '<?php echo __('Create') ?>';
App.Constants.FM_PACK                           = '<?php echo __('Pack') ?>';
App.Constants.FM_OK                             = '<?php echo __('OK') ?>';
App.Constants.FM_YOU_ARE_COPYING                = '<?php echo __('YOU ARE COPYING') ?>';
App.Constants.FM_YOU_ARE_REMOVING               = '<?php echo __('YOU ARE REMOVING') ?>';

App.Constants.FM_CONFIRM_COPY                   = '<?php echo __('Are you sure you want to copy') ?>';
App.Constants.FM_CONFIRM_DELETE                 = '<?php echo __('Are you sure you want to delete') ?>';
App.Constants.FM_INTO_KEYWORD                   = '<?php echo __('into') ?>';
App.Constants.FM_EXISTING_FILES_WILL_BE_DELETED = '<?php echo __('existing files will be deleted') ?>';
App.Constants.FM_ORIGINAL_NAME                  = '<?php echo __('Original name') ?>';
App.Constants.FM_FILE                           = '<?php echo __('File') ?>';
App.Constants.FM_ALREADY_EXISTS                 = '<?php echo __('already exists') ?>';
App.Constants.FM_EXTRACT                        = '<?php echo __('extract archive') ?>';
App.Constants.FM_CREATE_FILE                    = '<?php echo __('Create file') ?>';



