<?php
define('HESTIA_CMD', '/usr/bin/sudo /usr/local/hestia/bin/');

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


function authAPI() {
    //will return a JSON with user info including the user role
}
?>