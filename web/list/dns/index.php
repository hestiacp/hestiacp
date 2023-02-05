<?php
use function Hestiacp\quoteshellarg\quoteshellarg;
$TAB = "DNS";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Data & Render page

if (empty($_GET["domain"])) {
	exec(HESTIA_CMD . "v-list-dns-domains " . $user . " 'json'", $output, $return_var);
	$data = json_decode(implode("", $output), true);
	if ($_SESSION["userSortOrder"] == "name") {
		ksort($data);
	} else {
		$data = array_reverse($data, true);
	}
	unset($output);

	render_page($user, $TAB, "list_dns");
} elseif (!empty($_GET["action"])) {
	exec(
		HESTIA_CMD .
			"v-list-dnssec-public-key " .
			$user .
			" " .
			quoteshellarg($_GET["domain"]) .
			" 'json'",
		$output,
		$return_var,
	);
	$data = json_decode(implode("", $output), true);
	$domain = $_GET["domain"];

	switch ($data[$domain]["FLAG"]) {
		case 257:
			$flag = "KSK (257)";
			break;
		case 256:
			$flag = "ZSK (256)";
			break;
	}

	switch ($data[$domain]["ALGORITHM"]) {
		case 3:
			$algorithm = "3 - DSA";
			break;
		case 5:
			$algorithm = "5 - RSA/SHA1";
			break;
		case 6:
			$algorithm = "6 - DSA-NSEC3-SHA1";
			break;
		case 7:
			$algorithm = "7 - RSA/SHA1-NSEC3-SHA1";
			break;
		case 8:
			$algorithm = "8 - RSA/SHA256";
			break;
		case 10:
			$algorithm = "10 - RSA/SHA512";
			break;
		case 12:
			$algorithm = "12 - ECC-GOST";
			break;
		case 13:
			$algorithm = "13 - ECDSAP256/SHA256";
			break;
		case 14:
			$algorithm = "14 - ECDSAP384/SHA384";
			break;
		case 15:
			$algorithm = "15 - ED25519/SHA512";
			break;
		case 16:
			$algorithm = "16 - ED448/SHA912";
			break;
		default:
			$algorithm = "Unknown";
	}

	unset($output);

	render_page($user, $TAB, "list_dns_public");
} else {
	exec(
		HESTIA_CMD .
			"v-list-dns-records " .
			$user .
			" " .
			quoteshellarg($_GET["domain"]) .
			" 'json'",
		$output,
		$return_var,
	);
	$data = json_decode(implode("", $output), true);
	if ($_SESSION["userSortOrder"] == "name") {
		ksort($data);
	} else {
		$data = array_reverse($data, true);
	}
	unset($output);

	render_page($user, $TAB, "list_dns_rec");
}

// Back uri
$_SESSION["back"] = $_SERVER["REQUEST_URI"];
