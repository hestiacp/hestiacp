<?php

$arg1 = escapeshellarg($_GET['arg1']);
$arg2 = escapeshellarg($_GET['arg2']);

echo "/root/bin/test.sh ".$arg1." ".$arg2."\n";

