<?php

    error_reporting(NULL);
    session_start();

    include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

    $v_section = $_REQUEST['v_section'];
    $v_unit_id = $_REQUEST['v_unit_id'];

    unset($_SESSION['favourites'][strtoupper((string)$v_section)][(string)$v_unit_id]);

    v_exec('v-delete-user-favourites', [$_SESSION['user'], $v_section, $v_unit_id], false/*true*/);
?>