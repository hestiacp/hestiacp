<?php
$TAB = 'USER';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

if (($_SESSION['userContext'] === 'admin') && (!empty($_GET['user']))) {
    $user = quoteshellarg($_GET['user']);
}

exec (HESTIA_CMD . "v-list-user-ssh-key ".$user." json", $output, $return_var);
if($return_var > 0){
    check_return_code_redirect($return_var,$output,'/');
}
$data = json_decode(implode('', $output), true);

// Render page\
render_page($user, $TAB, 'list_key');

// Back uri
$_SESSION['back'] = $_SERVER['REQUEST_URI'];
?>