<?php
echo "<pre>\n";
exec('sudo /usr/local/vesta/bin/v_list_users json', $out);
print_r($out);
