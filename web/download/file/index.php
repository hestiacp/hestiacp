<?php
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

if ((!isset($_SESSION['FILEMANAGER_KEY'])) || (empty($_SESSION['FILEMANAGER_KEY']))) {
    header("Location: /login/");
    exit;
}

$user = $_SESSION['user'];
if (($_SESSION['user'] == 'admin') && (!empty($_SESSION['look']))) {
    $user=$_SESSION['look'];
}

if (!empty($_REQUEST['path'])) {
    $path = $_REQUEST['path'];
    header("Content-type: application/octet-stream");
    header("Content-Transfer-Encoding: binary");
    header("Content-disposition: attachment;filename=".basename($path));
    passthru (VESTA_CMD . "v-open-fs-file " . $user . " " . escapeshellarg($path));
    exit;
}
else {
    die('File not found');
}


?>
