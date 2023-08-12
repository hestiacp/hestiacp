<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check token
verify_csrf($_POST);

if (empty($_POST["domain"])) {
	header("Location: /list/mail");
	exit();
}
if (empty($_POST["action"])) {
	header("Location: /list/mail");
	exit();
}

$domain = $_POST["domain"];
if (empty($_POST["account"])) {
	$account = "";
} else {
	$account = $_POST["account"];
}
$action = $_POST["action"];

if ($_SESSION["userContext"] === "admin") {
	if (empty($_POST["account"])) {
		switch ($action) {
			case "rebuild":
				$cmd = "v-rebuild-mail-domain";
				break;
			case "delete":
				$cmd = "v-delete-mail-domain";
				break;
			case "suspend":
				$cmd = "v-suspend-mail-domain";
				break;
			case "unsuspend":
				$cmd = "v-unsuspend-mail-domain";
				break;
			default:
				header("Location: /list/mail/");
				exit();
		}
	} else {
		switch ($_POST["account"]) {
			case "delete":
				$cmd = "v-delete-mail-account";
				break;
			case "suspend":
				$cmd = "v-suspend-mail-account";
				break;
			case "unsuspend":
				$cmd = "v-unsuspend-mail-account";
				break;
			default:
				header("Location: /list/mail/?domain=" . $domain);
				exit();
		}
	}
} else {
	if (empty($_POST["account"])) {
		switch ($action) {
			case "delete":
				$cmd = "v-delete-mail-domain";
				break;
			case "suspend":
				$cmd = "v-suspend-mail-domain";
				break;
			case "unsuspend":
				$cmd = "v-unsuspend-mail-domain";
				break;
			default:
				header("Location: /list/mail/");
				exit();
		}
	} else {
		switch ($_POST["account"]) {
			case "delete":
				$cmd = "v-delete-mail-account";
				break;
			case "suspend":
				$cmd = "v-suspend-mail-account";
				break;
			case "unsuspend":
				$cmd = "v-unsuspend-mail-account";
				break;
			default:
				header("Location: /list/mail/?domain=" . $domain);
				exit();
		}
	}
}

if (empty($_POST["account"])) {
	if (is_array($domain)) {
		foreach ($domain as $value) {
			// Mail
			$value = quoteshellarg($value);
			exec(HESTIA_CMD . $cmd . " " . $user . " " . $value, $output, $return_var);
			$restart = "yes";
		}
	} else {
		header("Location: /list/mail/?domain=" . $domain);
		exit();
	}
} else {
	foreach ($account as $value) {
		// Mail Account
		$value = quoteshellarg($value);
		$dom = quoteshellarg($domain);
		exec(HESTIA_CMD . $cmd . " " . $user . " " . $dom . " " . $value, $output, $return_var);
		$restart = "yes";
	}
}

if (empty($account)) {
	header("Location: /list/mail/");
	exit();
} else {
	header("Location: /list/mail/?domain=" . $domain);
	exit();
}
