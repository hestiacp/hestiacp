<?php
require_once("authentication.php"); // isAPIenabled() and authAPI() belong to authentication.php
//define('HESTIA_CMD', '/usr/bin/sudo /usr/local/hestia/bin/'); //HESTIA_CMD already gets set in authentication.php

if (isAPIenabled()) {
    echo '{"API": "Enabled"}';
}

?>