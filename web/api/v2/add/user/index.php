<?php
require_once($_SERVER['DOCUMENT_ROOT']."/api/v2/authentication.php");
//HESTIA_CMD is set on authentication.php
$headers = getallheaders();
header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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
    if ($userinfo["role"] == "admin") {
        if (json_decode(file_get_contents('php://input'), true) != NULL){
            $data = json_decode(file_get_contents('php://input'), true);
        } else {
            echo('{"Error": "No valid data detected"}');
            exit();
        }
        if (isset($data["user"]) && isset($data["password"]) && isset($data["email"]) ) {
            $arg1 = escapeshellarg($data["user"]);
            $arg2 = escapeshellarg($data["password"]);
            $arg3 = escapeshellarg($data["email"]);
            if (isset($data["package"])) {
                $arg4 = escapeshellarg($data["package"]);
            }
            if (isset($data["name"])) {
                $arg5 = escapeshellarg($data["name"]);
            }
        } else {
            echo('{"Error": "Missing arguments"}');
            exit();
        }
        $cmd = HESTIA_CMD."v-add-user";
        $cmdquery = $cmd." ";
        if(!empty($arg1)){
             $cmdquery = $cmdquery.$arg1." "; }
        if(!empty($arg2)){
             $cmdquery = $cmdquery.$arg2." "; }
        if(!empty($arg3)){
             $cmdquery = $cmdquery.$arg3." "; }
        if(!empty($arg4)){
             $cmdquery = $cmdquery.$arg4." "; }
        if(!empty($arg5)){
                $cmdquery = $cmdquery.$arg5." "; }


        exec($cmdquery , $output, $return_var);
        unset($output);
        if ($return_var == 0) {
            echo('{"Sucess": "User added"}');
            exit();
        } else {
            echo('{"Error": "Return code '. $return_var .'"}');
            exit();
        }
        
    } else {
        echo('{"Error": "Unauthorized"}');
        exit();
    }
    
    
}
?>