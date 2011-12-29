<?php
echo "<title>Testing Vesta Control Panel</title>\n";
echo "<pre>\n";
$cmd='/usr/bin/sudo /usr/local/vesta/bin/v_list_users json';
echo "Command: $cmd\n\n";
exec ($cmd,$output,$return);
if ($return > 0) {
    echo "Error $return: something is wrong\n";
    foreach ($output as $row) {
        echo "$row\n";
    }
} else {
    foreach ($output as $row) {
        echo "$row\n";
    }
}
echo "</pre>\n";
?>
