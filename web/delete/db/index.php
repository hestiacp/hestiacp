<?php
use function Divinity76\quoteshellarg\quoteshellarg;

ob_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

if (($_SESSION['userContext'] === 'admin') && (!empty($_GET['user']))) {
    $user=quoteshellarg($_GET['user']);
}

// Check token
verify_csrf($_GET);

if (!empty($_GET['database'])) {
    $v_database = quoteshellarg($_GET['database']);
    exec(HESTIA_CMD."v-delete-database ".$user." ".$v_database, $output, $return_var);
}
check_return_code($return_var, $output);
unset($output);

$back = $_SESSION['back'];
if (!empty($back)) {
    header("Location: ".$back);
    exit;
}

header("Location: /list/db/");
exit;
