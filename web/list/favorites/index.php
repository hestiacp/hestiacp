<?php
error_reporting(NULL);
    include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

    echo '<br> Favorites: <br>';

    // Data
    exec (VESTA_CMD."v-list-user-favourites ".$_SESSION['user']." json", $output, $return_var);


//    print_r(implode('', $output));
//    $json = '{ "Favourites": { "USER": "", "WEB": "bulletfarm.com", "DNS": "", "MAIL": "", "DB": "", "CRON": "", "BACKUP": "", "IP": "", "PACKAGE": "", "FIREWALL": ""}}';
//    $data = json_decode($json, true);


    $data = json_decode(implode('', $output).'}', true);
    $data = array_reverse($data,true);

    print_r($data);
//    $data = array_reverse($data,true);

//    $data = json_decode(implode('', $output), true);

?>