<?php

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

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


// Define a destination
//$targetFolder = '/home/admin/'; // Relative to the root
$targetFolder = $panel[$user]['HOME']; // Relative to the root

$verifyToken = md5('unique_salt' . $_POST['timestamp']);

if (!empty($_FILES) && $_POST['token'] == $verifyToken) {
    $tempFile = $_FILES['Filedata']['tmp_name'];
    $targetPath = $targetFolder;
    $targetFile = rtrim($targetPath,'/') . '/' . $_FILES['Filedata']['name'];

    exec (VESTA_CMD . "v-copy-fs-file {$user} {$tempFile} {$targetFile}", $output, $return_var);

    $error = check_return_code($return_var, $output);
    if ($return_var != 0) {
        echo '0';
    } else {
        echo '1';
    }
}

?>
