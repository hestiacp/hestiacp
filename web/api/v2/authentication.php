<?php
define('HESTIA_CMD', '/usr/bin/sudo /usr/local/hestia/bin/');
define('HESTIA', '/usr/local/hestia');

function isAPIenabled(){
    exec(HESTIA_CMD."v-list-sys-config json" , $output, $return_var);
    $settings = json_decode(implode('', $output), true);
    unset($output);

    if ($settings["config"]["API"] == "yes"){
        return true; //API enabled
    } else {
        return false; //API disabled
    }
}


function authAPI($apikey) {
    //will return false if API key is invalid
    //will return a JSON if valid with user info including the user role
    if(!file_exists(HESTIA."/data/keys/".$apikey)) {
        return false;
    } else {
        $apifile = file_get_contents(HESTIA."/data/keys/".$apikey);
        $apitoken = array();
        $apitoken["user"] = $apifile;
        if ($apitoken["user"] == "admin") {
            $apitoken["role"] = "admin";
        } else {
            $apitoken["role"] = "user";
        }
        $json = json_encode($apitoken);
        return $json;
    }
}
?>