<?php
echo "<pre>";
$start_time = microtime(true);
exec('sudo /usr/local/vesta/bin/v_list_web_domains vesta json', $out);
$exec_time = microtime(true) - $start_time;
echo "$exec_time\n\n";
print_r($out);

