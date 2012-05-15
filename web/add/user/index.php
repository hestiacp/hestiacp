<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
$TAB = 'USER';
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Data
if ($_SESSION['user'] == 'admin') {
    if (!empty($_POST['cancel'])) {
        echo $_POST['cancel'];
        header("Location: /list/user/");
    }
    if (!empty($_POST['ok'])) {
        echo $_POST['vusername'];
        echo $_POST['vpassword'];
    }

    exec (VESTA_CMD."v_list_user_packages json", $output, $return_var);
    check_error($return_var);
    $data = json_decode(implode('', $output), true);
    unset($output);

    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/menu_add_user.html');
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/add_user.html');
}

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
