<?php
if (!empty($_REQUEST['path'])) {
    $path = $_REQUEST['path'];
    if (is_readable($path)) {
        header("Content-disposition: attachment;filename=".basename($path));
        readfile($path);
        exit;
    }
}
else {
    die('File not found');
}


?>
