<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

$TAB = "WEB";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Prepare values
if (!empty($_GET["domain"])) {
	$v_domain = $_GET["domain"];
} else {
	$v_domain = "example.tld";
}

$v_aliases = "";
$v_email = "";
$v_country = "US";
$v_state = "California";
$v_locality = "San Francisco";
$v_org = "MyCompany Inc.";
$v_org_unit = "IT";

// Back uri
$_SESSION["back"] = "";

// Check POST
if (!isset($_POST["generate"])) {
	render_page($user, $TAB, "generate_ssl");
	exit();
}

// Check token
verify_csrf($_POST);

// Check input
if (empty($_POST["v_domain"])) {
	$errors[] = _("Domain");
}
if (empty($_POST["v_country"])) {
	$errors[] = _("Country");
}
if (empty($_POST["v_state"])) {
	$errors[] = _("State");
}
if (empty($_POST["v_locality"])) {
	$errors[] = _("City");
}
if (empty($_POST["v_org"])) {
	$errors[] = _("Organization");
}
$v_domain = $_POST["v_domain"];
$v_aliases = $_POST["v_aliases"];
$v_email = $_POST["v_email"];
$v_country = $_POST["v_country"];
$v_state = $_POST["v_state"];
$v_locality = $_POST["v_locality"];
$v_org = $_POST["v_org"];

// Check for errors
if (!empty($errors[0])) {
	foreach ($errors as $i => $error) {
		if ($i == 0) {
			$error_msg = $error;
		} else {
			$error_msg = $error_msg . ", " . $error;
		}
	}
	$_SESSION["error_msg"] = sprintf(_('Field "%s" can not be blank.'), $error_msg);
	render_page($user, $TAB, "generate_ssl");
	unset($_SESSION["error_msg"]);
	exit();
}

// Protect input
$v_domain = quoteshellarg($_POST["v_domain"]);
$waliases = preg_replace("/\n/", " ", $_POST["v_aliases"]);
$waliases = preg_replace("/,/", " ", $waliases);
$waliases = preg_replace("/\s+/", " ", $waliases);
$waliases = trim($waliases);
$aliases = explode(" ", $waliases);
$v_aliases = quoteshellarg(str_replace(" ", "\n", $waliases));

$v_email = quoteshellarg($_POST["v_email"]);
$v_country = quoteshellarg($_POST["v_country"]);
$v_state = quoteshellarg($_POST["v_state"]);
$v_locality = quoteshellarg($_POST["v_locality"]);
$v_org = quoteshellarg($_POST["v_org"]);

exec(
	HESTIA_CMD .
		"v-generate-ssl-cert " .
		$v_domain .
		" " .
		$v_email .
		" " .
		$v_country .
		" " .
		$v_state .
		" " .
		$v_locality .
		" " .
		$v_org .
		" IT " .
		$v_aliases .
		" json",
	$output,
	$return_var,
);

// Revert to raw values
$v_domain = $_POST["v_domain"];
$v_email = $_POST["v_email"];
$v_country = $_POST["v_country"];
$v_state = $_POST["v_state"];
$v_locality = $_POST["v_locality"];
$v_org = $_POST["v_org"];

// Check return code
if ($return_var != 0) {
	$error = implode("<br>", $output);
	if (empty($error)) {
		$error = sprintf(_("Error code: %s"), $return_var);
	}
	$_SESSION["error_msg"] = $error;
	render_page($user, $TAB, "generate_ssl");
	unset($_SESSION["error_msg"]);
	exit();
}

// OK message
$_SESSION["ok_msg"] = _("Certificate has been generated successfully.");

// Parse output
$data = json_decode(implode("", $output), true);
unset($output);
$v_crt = $data[$v_domain]["CRT"];
$v_key = $data[$v_domain]["KEY"];
$v_csr = $data[$v_domain]["CSR"];

// Back uri
$_SESSION["back"] = $_SERVER["REQUEST_URI"];

// Render page
render_page($user, $TAB, "list_ssl");

unset($_SESSION["ok_msg"]);
