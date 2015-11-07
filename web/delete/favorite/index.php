<?php

    error_reporting(NULL);
    session_start();

    include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

    unset($_SESSION['favourites'][strtoupper($_REQUEST['v_section'])][$_REQUEST['v_unit_id']]);

    $v_section = escapeshellarg($_REQUEST['v_section']);
    $v_unit_id = escapeshellarg($_REQUEST['v_unit_id']);

    exec (VESTA_CMD."v-delete-user-favourites ".$_SESSION['user']." ".$v_section." ".$v_unit_id, $output, $return_var);
//    check_return_code($return_var,$output);
?>