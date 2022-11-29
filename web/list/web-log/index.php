<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

$TAB = "WEB";

$v_domain = quoteshellarg($_GET["domain"]);
$type = "access";
if ($_GET["type"] == "access") {
	$type = "access";
}
if ($_GET["type"] == "error") {
	$type = "error";
}
// Header
include $_SERVER["DOCUMENT_ROOT"] . "/templates/pages/list_weblog.php";

exec(HESTIA_CMD . "v-list-web-domain-" . $type . "log $user " . $v_domain, $output, $return_var);

if ($return_var == 0) {
	foreach ($output as $file) {
		echo htmlentities($file) . "\n";
	}
}
echo "    </pre>\n</body>\n</html>\n";
