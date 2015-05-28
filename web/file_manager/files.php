<?php

//define(MAX_FILES_TO_SORT, 5);
//define(LISTING_TIMEOUT, 0.000001);
define(LISTING_TIMEOUT, 5);




//echo 'files: ';
//$files = scandir(__DIR__);


//echo '<pre>';
//print_r($files);


//$_REQUEST['sort_field'] = 'size';
$_REQUEST['sort_field'] = 'name';
//$_REQUEST['sort_field'] = 'atime';
//$_REQUEST['sort_field'] = 'mtime';
$_REQUEST['sort_desc'] = 1;



/*
+-  copy file / dir [ recursive ]
+-  rename(move) file / dir
+-  delete file / dir [ recursive ]
+-  chmod file / dir
+-  chown file / dir
+-  create file
+-  create dir
*/

switch($_REQUEST['action']){
    case 'copy': fm_copy($_REQUEST['source'], $_REQUEST['dest']); break;
    case 'rename': fm_rename($_REQUEST['source'], $_REQUEST['dest']); break;
    case 'delete': fm_delete($_REQUEST['source']); break;
    case 'chmod': fm_chmod($_REQUEST['source'], $_REQUEST['mode']); break;
    case 'chown': fm_chown($_REQUEST['source'], $_REQUEST['uid'], $_REQUEST['gid']); break;
    case 'create_file': fm_create_file($_REQUEST['source'], $_REQUEST['mode']  || FALSE); break;
    case 'create_dir': fm_create_dir($_REQUEST['source'], $_REQUEST['mode'] || FALSE); break;

    default: 
	$pwd = $_REQUEST['path'] ? $_REQUEST['path'] : __DIR__;
	$listing = dir_list($pwd, $_REQUEST['sort_field']); 
	$writable = is_writable($pwd);

	$pwd = array_merge(array('/'), explode('/', trim($pwd, '/'))); 

	include('templates/filemanager.php'); 
    break;
}






//echo $_GET['sort_field'];

//    if(in_array($_GET['sort_field'], $available_sort_fields)){
//	echo '1';
//    }	








/*
  upload_file

+  list_dir
+-  copy file / dir [ recursive ]
+-  rename(move) file / dir
+-  delete file / dir [ recursive ]
+-  chmod file / dir
+-  chown file / dir
+-  create file
+-  create dir

  view file / image
  download file / image   
*/



function fm_create_file($filename){
    if(is_file($filename))
        return array('error' => 'file exists', 'code' => 1);

    return !!fopen($filename, 'w');
}


function fm_create_dir($dirname){
    if(is_dir($filename))
        return array('error' => 'directory exists', 'code' => 1);

    // TODO set parent directory mode
    return mkdir($dirname);
}


function fm_chown($filename, $recursive = 0, $uid = FALSE, $gid = FALSE){
    if(is_dir($filename) && $recursive){
        $dir_handle  = opendir($dir);
	while ($item = readdir($dir_handle)){
    	    if (!in_array($item, array('.','..'))){
		$new_item = $filename.'/'.$item;

	        if($uid !== FALSE) chown($new_item, (int)$uid);
		if($gid !== FALSE) chgrp($new_item, (int)$gid); 

		if(is_dir($new_item)){
		    fm_chown($new_item, $recursive, $uid, $gid);
		}
	    }
	}
    }else{
        if($uid !== FALSE) chown($filename, (int)$uid);
	if($gid !== FALSE) chgrp($filename, (int)$gid); 
    }
}


function fm_chmod($filename, $recursive = 0, $mode){
    if(is_dir($filename) && $recursive){
        $dir_handle  = opendir($dir);
	while ($item = readdir($dir_handle)){
    	    if (!in_array($item, array('.','..'))){
		$new_item = $filename.'/'.$item;
	        chmod($new_item, octdec($mode));

		if(is_dir($new_item)){
		    fm_chmod($new_item, $recursive, $mode);
		}
	    }
	}
    }else{
	chmod($filename, octdec($mode));
    }
}


function fm_delete($filename){
    if(is_dir($filename)){
	foreach (
	    $iterator = new RecursiveIteratorIterator(
	    new RecursiveDirectoryIterator($filename, RecursiveDirectoryIterator::SKIP_DOTS),
	    RecursiveIteratorIterator::SELF_FIRST) as $item
	) {
	  if ($item->isDir()) {
	    rmdir($item);
//	    mkdir($dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName(), decoct(fileperms($item->getPerms())));
	  } else {
	    unlink($item);
//	    copy($item, $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
	  }
	}
    }else{
	return unlink($filename);
    }
}


function fm_rename($source, $dest){
    return rename($source, $dest);
}


function fm_copy($source, $dest){
    if(is_dir($source)){
	foreach (
	    $iterator = new RecursiveIteratorIterator(
	    new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
	    RecursiveIteratorIterator::SELF_FIRST) as $item
	) {
	  if ($item->isDir()) {
	    // TODO set dir perms 
	    mkdir($dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName(), decoct(fileperms($item->getPerms())));
	  } else {
	    copy($item, $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
	  }
	}
    
    }else{
	return copy($source, $dest);
    }
}


function list_dir(){
    $dir_iterator = new RecursiveDirectoryIterator("/path");
    $iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
    // could use CHILD_FIRST if you so wish

    foreach ($iterator as $file) {
	echo $file, "\n";
    }

    $size = 0;
    foreach ($iterator as $file) {
	if ($file->isFile()) {
    	    echo substr($file->getPathname(), 27) . ": " . $file->getSize() . " B; modified " . date("Y-m-d", $file->getMTime()) . "\n";
            $size += $file->getSize();
	}
    }

    echo "\nTotal file size: ", $size, " bytes\n";
}




/// fast removing directory 
function rmrf($dir) {
    
    foreach (glob($dir) as $file) {
        if (is_dir($file)) {
            rmrf("$file/*");
            rmdir($file);
        } else {
            unlink($file);
        }
    }
}




function dir_list($dir, $sort = 0)
{
    $sort_order_for_filename = SORT_ASC;
    //$available_sort_fields = array('size, type', 'mtime', 'atime', 'owner', 'group');
    $available_sort_fields = array('name', 'size', 'type', 'mtime', 'atime', 'owner', 'group');
    $sort_order = SORT_ASC;

    if ($dir[strlen($dir)-1] != '/') $dir .= '/';
    if (!is_dir($dir)) return array();

    $start = microtime(TRUE);

    $listing = array('dirs' => array(), 'files' => array(), 'dir_names' => array(), 'file_names' => array() ,'count' => 0, 'timeout_exeeded' => 0, 'time' => 0);
    $dir_handle  = opendir($dir);
    $dir_objects = array();
    while ($object = readdir($dir_handle)){
        if (!in_array($object, array('.','..'))){
            $filename    = $dir . $object;
	    $time = microtime(true) - $start;
	    if($time <= LISTING_TIMEOUT){
    		$stats = stat($filename);
    		$mode = explain_mode($stats['mode']);
		$perms = decoct(fileperms($filename));
	        $item = array(
            	    'name' => $object,
		    'size' => $stats['size'],
            	    'mode' => array('owner' => $mode['owner'], 'group' => $mode['owner'], 'other' => $mode['owner']),
            	    'perms' => decoct($stats['mode']),
            	    'type' => $mode['type'],
            	    'mtime' => $stats['mtime'],
            	    'atime' => $stats['atime'],
            	    'mdate_human' => date("Y F d", $stats['mtime']),
            	    'mtime_human' => date("H:i:s", $stats['mtime']),
            	    'adate_human' => date("Y F d", $stats['atime']),
            	    'atime_human' => date("H:i:s", $stats['atime']),
	    	    'nlink' => $stats['nlink'],
	    	    'owner' => posix_getpwuid($stats['uid'])['name'],
	    	    'group' => posix_getgrgid($stats['gid'])['name']
            	);
	    }else{
		$listing['timeout_exeeded'] = TRUE;
		if(is_dir($filename)){   $type = 'd';
		}else{   $type = '-'; }

	        $item = array(
            	    'name' => $object,
		    'size' => FALSE,
            	    'mode' => array('owner' => FALSE, 'group' => FALSE, 'other' => FALSE),
            	    'type' => $type,
            	    'mtime' => FALSE,
            	    'atime' => FALSE,
            	    'mdate_human' => FALSE,
            	    'mtime_human' => FALSE,
            	    'adate_human' => FALSE,
            	    'atime_human' => FALSE,
	    	    'nlink' => FALSE,
	    	    'owner' => FALSE,
	    	    'group' => FALSE
            	);
	    }


	    $listing['count']++;

	    if($item['type'] == 'd'){
		$listing['dirs'][] = $item;
		$listing['dir_names'][] = $item['name'];
	    }else{
		if($sort && !$listing['timeout_exeeded']){
		    $listing[$sort][] = $item[$sort];
		}
		$listing['files'][] = $item;
		$listing['file_names'][] = $item['name'];
	    }
        }
    }
    $listing['time'] = microtime(TRUE) - $start;


    if(!$listing['timeout_exeeded']){
	if(in_array($_REQUEST['sort_field'], $available_sort_fields)){
	    if($_REQUEST['sort_desc']){
		$sort_order = SORT_DESC;
	    }
	    array_multisort($listing[$_REQUEST['sort_field']], $sort_order, $listing['file_names'], $sort_order_for_filename, $listing['files']);
	}
	array_multisort($listing['dir_names'], $sort_order_for_filename, $listing['dirs']);
    }

    return $listing;
}

	
function explain_mode($mode)
{
    $info = array();
    
    if     (($mode & 0xC000) == 0xC000) { $info['type'] = 's'; }
    elseif (($mode & 0xA000) == 0xA000) { $info['type'] = 'l'; }
    elseif (($mode & 0x8000) == 0x8000) { $info['type'] = '-'; }
    elseif (($mode & 0x6000) == 0x6000) { $info['type'] = 'b'; }
    elseif (($mode & 0x4000) == 0x4000) { $info['type'] = 'd'; }
    elseif (($mode & 0x2000) == 0x2000) { $info['type'] = 'c'; }
    elseif (($mode & 0x1000) == 0x1000) { $info['type'] = 'p'; }
    else                                { $info['type'] = 'u'; }

    $info['owner'] = (($mode & 0x0100) ? 'r' : '-');
    $info['owner'] .= (($mode & 0x0080) ? 'w' : '-');
    $info['owner'] .= (($mode & 0x0040) ? (($mode & 0x0800) ? 's' : 'x' ) : (($mode & 0x0800) ? 'S' : '-'));

    // group
    $info['group'] = (($mode & 0x0020) ? 'r' : '-');
    $info['group'] .= (($mode & 0x0010) ? 'w' : '-');
    $info['group'] .= (($mode & 0x0008) ? (($mode & 0x0400) ? 's' : 'x' ) : (($mode & 0x0400) ? 'S' : '-'));

    // other
    $info['other'] = (($mode & 0x0004) ? 'r' : '-');
    $info['other'] .= (($mode & 0x0002) ? 'w' : '-');
    $info['other'] .= (($mode & 0x0001) ? (($mode & 0x0200) ? 't' : 'x' ) : (($mode & 0x0200) ? 'T' : '-'));

    return $info;
}

?>
