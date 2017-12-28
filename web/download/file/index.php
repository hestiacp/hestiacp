<?php
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

if ((!isset($_SESSION['FILEMANAGER_KEY'])) || (empty($_SESSION['FILEMANAGER_KEY']))) {
    header("Location: /login/");
    exit;
}

$user = $_SESSION['user'];
if (($_SESSION['user'] == 'admin') && (!empty($_SESSION['look']))) {
    $user = $_SESSION['look'];
}

$path = $_REQUEST['path'];
if (!empty($path)) {
    set_time_limit(0);
	if (ob_get_level()) {
	  ob_end_clean();
	}	
    header("Content-type: application/octet-stream");
    header("Content-Transfer-Encoding: binary");
    header("Content-disposition: attachment;filename=".basename($path));
	$output = '';
	exec(VESTA_CMD . "v-check-fs-permission " . $user . " " . escapeshellarg($path), $output, $return_var);
	if ($return_var != 0) {
	  print 'Error while opening file'; // todo: handle this more styled
	  exit;
	}
	readfile($path);
    exit;
} else {
    die('File not found');
}
