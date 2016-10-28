<?php
session_start();

require_once($_SERVER['DOCUMENT_ROOT'].'/inc/i18n.php');

if (empty($_SESSION['language'])) {
    $_SESSION['language'] = detect_user_language();
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
App.Constants.FM_MOVE                           = '<?=__('Move') ?>';
App.Constants.FM_CANCEL                         = '<?=__('Cancel')?>';
App.Constants.FM_RENAME                         = '<?=__('Rename')?>';
App.Constants.FM_CHMOD                          = '<?=__('Change Rights')?>';
App.Constants.FM_DELETE                         = '<?=__('Delete')?>';
App.Constants.FM_CONFIRM_DELETE_BULK            = '<?=__('Delete items')?>';
App.Constants.FM_EXTRACT                        = '<?=__('Extract')?>';
App.Constants.FM_CREATE                         = '<?=__('Create')?>';
App.Constants.FM_PACK                           = '<?=__('Compress')?>';
App.Constants.FM_PACK_BUTTON                    = '<?=__('Compress')?>';
App.Constants.FM_OK                             = '<?=__('OK')?>';
App.Constants.FM_YOU_ARE_COPYING                = '<?=__('YOU ARE COPYING')?>';
App.Constants.FM_YOU_ARE_REMOVING               = '<?=__('YOU ARE REMOVING')?>';

App.Constants.FM_COPY_BULK                      = '<?=__('Copy files')?>';
App.Constants.FM_MOVE_BULK                      = '<?=__('Move files')?>';

App.Constants.FM_CONFIRM_COPY                   = '<?=__('Are you sure you want to copy')?>';
App.Constants.FM_CONFIRM_MOVE                   = '<?=__('Are you sure you want to move')?>';
App.Constants.FM_CONFIRM_DELETE                 = '<?=__('Are you sure you want to delete')?>';
App.Constants.FM_INTO_KEYWORD                   = '<?=__('into')?>';
App.Constants.FM_EXISTING_FILES_WILL_BE_REPLACED= '<?=__('existing files will be replaced')?>';
App.Constants.FM_ORIGINAL_NAME                  = '<?=__('Original name')?>';
App.Constants.FM_FILE                           = '<?=__('File')?>';
App.Constants.FM_ALREADY_EXISTS                 = '<?=__('already exists')?>';
App.Constants.FM_CREATE_FILE                    = '<?=__('Create file')?>';
App.Constants.FM_CREATE_DIRECTORY               = '<?=__('Create directory')?>';
App.Constants.FM_TRANSLATED_DATES               = {'Jan': '<?=__('Jan')?>', 'Feb': '<?=__('Feb')?>','Mar': '<?=__('Mar')?>','Apr': '<?=__('Apr')?>','May': '<?=__('May')?>','Jun': '<?=__('Jun')?>','Jul': '<?=__('Jul')?>','Aug': '<?=__('Aug')?>','Sep': '<?=__('Sep')?>','Oct': '<?=__('Oct')?>','Nov': '<?=__('Nov')?>','Dec': '<?=__('Dec')?>'};

App.Constants.FM_READ_BY_OWNER                  = '<?=__('read by owner')?>';
App.Constants.FM_WRITE_BY_OWNER                 = '<?=__('write by owner')?>';
App.Constants.FM_EXECUTE_BY_OWNER               = '<?=__('execute/search by owner')?>';
App.Constants.FM_READ_BY_GROUP                  = '<?=__('read by group')?>';
App.Constants.FM_WRITE_BY_GROUP                 = '<?=__('write by group')?>';
App.Constants.FM_EXECUTE_BY_GROUP               = '<?=__('execute/search by group')?>';
App.Constants.FM_READ_BY_OTHERS                 = '<?=__('read by others')?>';
App.Constants.FM_WRITE_BY_OTHERS                = '<?=__('write by others')?>';
App.Constants.FM_EXECUTE_BY_OTHERS              = '<?=__('execute/search by others')?>';

App.Constants.NOTIFICATIONS_EMPTY               = '<?=__('no notifications')?>';
