<?php

// Init
//error_reporting(NULL);


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
    case 'cd':
        $dir = $_REQUEST['dir'];
        print json_encode($fm->ls($dir));
        break;

    case 'check_file_type':
        $dir = $_REQUEST['dir'];
        print json_encode($fm->checkFileType($dir));
        break;

    case 'rename_file':
        $dir = $_REQUEST['dir'];
        $item = $dir . '/' . $_REQUEST['item'];
        $target_name = $dir . '/' . $_REQUEST['target_name'];
        print json_encode($fm->renameFile($item, $target_name));
        break;

    case 'rename_directory':
        $dir = $_REQUEST['dir'];
        $item = $dir.$_REQUEST['item'];
        $target_name = $dir.$_REQUEST['target_name'];

        print json_encode($fm->renameDirectory($item, $target_name));
        break;

    case 'move_file':
        $item = $_REQUEST['item'];
        $target_name = $_REQUEST['target_name'];
        print json_encode($fm->renameFile($item, $target_name));
        break;

    case 'move_directory':
        $item = $_REQUEST['item'];
        $target_name = $_REQUEST['target_name'];
        print json_encode($fm->renameDirectory($item, $target_name));
        break;

    case 'delete_files':
        $dir = $_REQUEST['dir'];
        $item = $_REQUEST['item'];
        print json_encode($fm->deleteItem($dir, $item));
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

    case 'open_file':
        $dir = $_REQUEST['dir'];
        print json_encode($fm->open_file($dir));
        break;

    case 'copy_file':
        $dir = $_REQUEST['dir'];
        $target_dir = $_REQUEST['dir_target'];
        $filename   = $_REQUEST['filename'];
        $item       = $_REQUEST['item'];
        print json_encode($fm->copyFile($item, $dir, $target_dir, $filename));
        break;

    case 'copy_directory':
        $dir = $_REQUEST['dir'];
        $target_dir = $_REQUEST['dir_target'];
        $filename   = $_REQUEST['filename'];
        $item       = $_REQUEST['item'];
        print json_encode($fm->copyDirectory($item, $dir, $target_dir, $filename));
        break;

    case 'unpack_item':
        $dir = $_REQUEST['dir'];
        $target_dir = $_REQUEST['dir_target'];
        $filename   = $_REQUEST['filename'];
        $item       = $_REQUEST['item'];
        print json_encode($fm->unpackItem($item, $dir, $target_dir, $filename));
        break;

    case 'pack_item':
        $items      = $_REQUEST['items'];
        $dst_item   = $_REQUEST['dst_item'];
        print json_encode($fm->packItem($items, $dst_item));
        break;

    case 'backup':
        $path = $_REQUEST['path'];
        print json_encode($fm->backupItem($path));
        break;

    case 'chmod_item':
        $dir = $_REQUEST['dir'];
        $item = $_REQUEST['item'];
        $permissions = $_REQUEST['permissions'];
        print json_encode($fm->chmodItem($dir, $item, $permissions));
        break;

    default:
        //print json_encode($fm->init());
        break;
}
