<?php
// Init
error_reporting(NULL);

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_weblog.html');

$v_domain = $_GET['domain'];
if ($_GET['type'] == 'access') $type = 'access';
if ($_GET['type'] == 'error') $type = 'error';

$return_var = v_exec("v-list-web-domain-{$type}log", [$user, $v_domain], false, $output);

if ($return_var == 0) {
    print $output . "\n";
}

echo "    </pre>\n</body>\n</html>\n";