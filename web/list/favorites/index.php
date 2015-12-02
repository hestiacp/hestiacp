<?php
error_reporting(NULL);
    include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

    echo '<br> Favorites: <br>';

    // Data
    v_exec('v-list-user-favourites', [$_SESSION['user'], 'json'], false, $output);


//    print_r($output);
//    $json = '{ "Favourites": { "USER": "", "WEB": "bulletfarm.com", "DNS": "", "MAIL": "", "DB": "", "CRON": "", "BACKUP": "", "IP": "", "PACKAGE": "", "FIREWALL": ""}}';
//    $data = json_decode($json, true);


    $data = json_decode($output.'}', true);
    $data = array_reverse($data, true);

    print_r($data);
//    $data = array_reverse($data,true);

//    $data = json_decode($output, true);

?>