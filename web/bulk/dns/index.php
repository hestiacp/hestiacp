<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check token
verify_csrf($_POST);

if (empty($_POST["domain"])) {
	header("Location: /list/dns/");
	exit();
}

if (empty($_POST["action"])) {
	header("Location: /list/dns/");
	exit();
}

$domain = $_POST["domain"];
if (empty($_POST["record"])) {
	$record = "";
} else {
	$record = $_POST["record"];
}
$action = $_POST["action"];

if ($_SESSION["userContext"] === "admin") {
	if (empty($_POST["record"])) {
		switch ($action) {
			case "rebuild":
				$cmd = "v-rebuild-dns-domain";
				break;
			case "delete":
				$cmd = "v-delete-dns-domain";
				break;
			case "suspend":
				$cmd = "v-suspend-dns-domain";
				break;
			case "unsuspend":
				$cmd = "v-unsuspend-dns-domain";
				break;
			default:
				header("Location: /list/dns/");
				exit();
		}
	} else {
		switch ($action) {
			case "delete":
				$cmd = "v-delete-dns-record";
				break;
			case "suspend":
				$cmd = "v-suspend-dns-record";
				break;
			case "unsuspend":
				$cmd = "v-unsuspend-dns-record";
				break;
			default:
				header("Location: /list/dns/?domain=" . $domain);
				exit();
		}
	}
} else {
	if (empty($_POST["record"])) {
		switch ($action) {
			case "delete":
				$cmd = "v-delete-dns-domain";
				break;
			default:
				header("Location: /list/dns/");
				exit();
		}
	} else {
		switch ($action) {
			case "delete":
				$cmd = "v-delete-dns-record";
				break;
			default:
				header("Location: /list/dns/?domain=" . $domain);
				exit();
		}
	}
}

if (empty($_POST["record"])) {
	if (is_array($_POST["domain"])) {
		foreach ($domain as $value) {
			// DNS
			$value = quoteshellarg($value);
			exec(HESTIA_CMD . $cmd . " " . $user . " " . $value . " no", $output, $return_var);
			$restart = "yes";
		}
	} else {
		header("Location: /list/dns/?domain=" . $domain);
	}
} else {
	foreach ($record as $value) {
		// DNS Record
		$value = quoteshellarg($value);
		$dom = quoteshellarg($domain);
		exec(
			HESTIA_CMD . $cmd . " " . $user . " " . $dom . " " . $value . " no",
			$output,
			$return_var,
		);
		$restart = "yes";
	}
}

if (!empty($restart)) {
	exec(HESTIA_CMD . "v-restart-dns", $output, $return_var);
}

if (empty($_POST["record"])) {
	header("Location: /list/dns/");
	exit();
} else {
	header("Location: /list/dns/?domain=" . $domain);
	exit();
}
