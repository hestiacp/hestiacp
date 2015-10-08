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

App.i18n.ARE_YOU_SURE                           = '<?=__('Are you sure?')?>';
App.Constants.UNLIM_TRANSLATED_VALUE            = '<?=__('unlimited')?>';

App.Constants.FM_HIT                            = '<?=__('Hit')?>';
App.Constants.FM_TO_RELOAD_THE_PAGE             = '<?=__('to reload the page')?>'
App.Constants.FM_DIRECTORY_NAME_CANNOT_BE_EMPTY = '<?=__('Directory name cannot be empty')?>';
App.Constants.FM_FILE_NAME_CANNOT_BE_EMPTY      = '<?=__('File name cannot be empty')?>';
App.Constants.FM_NO_FILE_SELECTED               = '<?=__('No file selected')?>';
App.Constants.FM_NO_FILE_OR_DIRECTORY_SELECTED  = '<?=__('No file or folder selected')?>';
App.Constants.FM_FILE_TYPE_NOT_SUPPORTED        = '<?=__('File type not supported')?>';
App.Constants.FM_DIRECTORY_DOWNLOAD_NOT_READY   = '<?=__('Directory download not available in current version')?>';

App.Constants.FM_DIRECTORY_NOT_AVAILABLE        = '<?=__('Directory not available')?>';
App.Constants.FM_DONE                           = '<?=__('Done')?>';
App.Constants.FM_CLOSE                          = '<?=__('Close')?>';
App.Constants.FM_COPY                           = '<?=__('Copy') ?>';
App.Constants.FM_CANCEL                         = '<?=__('Cancel')?>';
App.Constants.FM_RENAME                         = '<?=__('Rename')?>';
App.Constants.FM_DELETE                         = '<?=__('Delete')?>';
App.Constants.FM_EXTRACT                        = '<?=__('Extract')?>';
App.Constants.FM_CREATE                         = '<?=__('Create')?>';
App.Constants.FM_PACK                           = '<?=__('Compress')?>';
App.Constants.FM_PACK_BUTTON                    = '<?=__('Compress')?>';
App.Constants.FM_OK                             = '<?=__('OK')?>';
App.Constants.FM_YOU_ARE_COPYING                = '<?=__('YOU ARE COPYING')?>';
App.Constants.FM_YOU_ARE_REMOVING               = '<?=__('YOU ARE REMOVING')?>';

App.Constants.FM_CONFIRM_COPY                   = '<?=__('Are you sure you want to copy')?>';
App.Constants.FM_CONFIRM_DELETE                 = '<?=__('Are you sure you want to delete')?>';
App.Constants.FM_INTO_KEYWORD                   = '<?=__('into')?>';
App.Constants.FM_EXISTING_FILES_WILL_BE_REPLACED= '<?=__('existing files will be replaced')?>';
App.Constants.FM_ORIGINAL_NAME                  = '<?=__('Original name')?>';
App.Constants.FM_FILE                           = '<?=__('File')?>';
App.Constants.FM_ALREADY_EXISTS                 = '<?=__('already exists')?>';
App.Constants.FM_CREATE_FILE                    = '<?=__('Create file')?>';
App.Constants.FM_CREATE_DIRECTORY               = '<?=__('Create directory')?>';



