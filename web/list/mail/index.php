<?php
error_reporting(NULL);
$TAB = 'MAIL';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Data & Render page
if (empty($_GET['domain'])){
    exec (VESTA_CMD."v-list-mail-domains $user json", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    $data = array_reverse($data, true);
    unset($output);

    render_page($user, $TAB, 'list_mail');
} else {
    exec (VESTA_CMD."v-list-mail-accounts '".$user."' '".escapeshellarg($_GET['domain'])."' json", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    $data = array_reverse($data, true);
    unset($output);

    render_page($user, $TAB, 'list_mail_acc');
}

// Back uri
$_SESSION['back'] = $_SERVER['REQUEST_URI'];
