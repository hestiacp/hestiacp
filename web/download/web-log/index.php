<?php
// Init
error_reporting(NULL);
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");
$v_domain = $_GET['domain'];
$v_domain = escapeshellarg($_GET['domain']);
if ($_GET['type'] == 'access') $type = 'access';
if ($_GET['type'] == 'error') $type = 'error';

header("Cache-Control: public");
header("Content-Description: File Transfer");
header("Content-Disposition: attachment; filename=".$_GET['domain'].".".$type."-log.txt");
header("Content-Type: application/octet-stream; "); 
header("Content-Transfer-Encoding: binary");

$v_domain = escapeshellarg($_GET['domain']);
if ($_GET['type'] == 'access') $type = 'access';
if ($_GET['type'] == 'error') $type = 'error';

exec (VESTA_CMD."v-list-web-domain-".$type."log $user ".$v_domain." 5000", $output, $return_var);
if ($return_var == 0 ) {
    foreach($output as $file) {
        echo $file . "\n";
    }
}

?>
