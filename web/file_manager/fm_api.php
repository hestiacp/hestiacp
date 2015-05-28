<?php

// Init
//error_reporting(NULL);


session_start();

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");
include($_SERVER['DOCUMENT_ROOT']."/file_manager/fm_core.php");


// todo: set in session?
if (empty($panel)) {
    $command = VESTA_CMD."v-list-user '".$user."' 'json'";
    exec ($command, $output, $return_var);
    if ( $return_var > 0 ) {
        header("Location: /error/");
        exit;
    }
    $panel = json_decode(implode('', $output), true);
}

$fm = new FileManager($user);
$fm->setRootDir($panel[$user]['HOME']);

$_REQUEST['action'] = empty($_REQUEST['action']) ? '' : $_REQUEST['action'];

switch ($_REQUEST['action']) {
    case 'rename_file':
        $dir = $_REQUEST['dir'];
        $item = $_REQUEST['item'];
        $target_name = $_REQUEST['target_name'];

        print json_encode($fm->renameItem($dir, $item, $target_name));
        break;
    case 'delete_files':
        $dir = $_REQUEST['dir'];
        $item = $_REQUEST['item'];

        print json_encode($fm->deleteItems($dir, $item));
        break;
    case 'create_file':
        $dir = $_REQUEST['dir'];
        $filename = $_REQUEST['filename'];
        print json_encode($fm->createFile($dir, $filename));
        break;
    case 'create_dir':
        $dir = $_REQUEST['dir'];
        $dirname = $_REQUEST['dirname'];
        print json_encode($fm->createDir($dir, $dirname));
        break;
    case 'cd':
        $dir = $_REQUEST['dir'];
        print json_encode($fm->ls($dir));
        break;
    case 'open_file':
        $dir = $_REQUEST['dir'];
        print json_encode($fm->open_file($dir));
        break;
    case 'copy_files':
        $dir = $_REQUEST['dir'];
        $target_dir = $_REQUEST['dir_target'];
        $filename   = $_REQUEST['filename'];
        print json_encode($fm->copyFile($dir, $target_dir, $filename));
        break;
    default:
        //print json_encode($fm->init());
        break;
}
