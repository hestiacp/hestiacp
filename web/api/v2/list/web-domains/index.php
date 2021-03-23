<?php
require_once($_SERVER['DOCUMENT_ROOT']."/api/v2/authentication.php");
//HESTIA_CMD is set on authentication.php
$headers = getallheaders();
header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo('{"Error": "Method not allowed"}');
    exit();
}

if (!isAPIenabled()) {
    echo('{"Error": "API not enabled"}');
    exit();
}


if (!isset($headers["Auth"])){
    echo('{"Error": "Missing Authentication"}');
    exit();
}

$auth = authAPI($headers["Auth"]);
if (!$auth){
    echo('{"Error": "No valid API key"}');
    exit();
} else {
    $userinfo = json_decode($auth, true); //decode json into array

    exec(HESTIA_CMD."v-list-web-domains ". $userinfo["user"] ." json" , $output, $return_var);
    echo(implode('', $output));
    unset($output);
    exit();
    
}
?>