<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
$TAB = "WEB";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Add as someone else?
$user_query = [];
$v_username = !empty($_SESSION["look"]) ? $_SESSION["look"] : $_SESSION["user"];
if ($_SESSION["userContext"] === "admin" && !empty($_GET["user"])) {
	$user = quoteshellarg($_GET["user"]);
	$user_plain = htmlentities($_GET["user"]);
	$user_query = ["user" => $_GET["user"]];
	$v_username = $_GET["user"];
}

$runtimes = ["node" => "Node.js", "python" => "Python", "custom" => _("Custom command")];

// Check POST request
if (!empty($_POST["ok"]) && $read_only !== true) {
	// Check token
	verify_csrf($_POST);

	$v_domain = trim($_POST["v_domain"] ?? "");
	$v_runtime = $_POST["v_runtime"] ?? "";
	$v_version = trim($_POST["v_version"] ?? "system");
	$v_cmd = trim($_POST["v_cmd"] ?? "");
	$v_dir = trim($_POST["v_dir"] ?? "");
	$v_install = trim($_POST["v_install"] ?? "");
	$v_env = $_POST["v_env"] ?? "";

	// Check empty fields
	$errors = [];
	if ($v_domain === "") {
		$errors[] = _("Domain");
	}
	if (!array_key_exists($v_runtime, $runtimes)) {
		$errors[] = _("Runtime");
	}
	if ($v_cmd === "" && $v_runtime !== "node") {
		$errors[] = _("Start Command");
	}
	if (!empty($errors)) {
		$_SESSION["error_msg"] = sprintf(_('Field "%s" can not be blank.'), implode(", ", $errors));
	}

	// Apply runtime defaults
	if ($v_runtime !== "node" || $v_version === "") {
		$v_version = "system";
	}
	if ($v_cmd === "" && $v_runtime === "node") {
		$v_cmd = "npm start";
	}
	if ($v_dir === "") {
		$v_dir = "app";
	}

	// Add app
	if (empty($_SESSION["error_msg"])) {
		exec(
			HESTIA_CMD .
				"v-add-web-domain-app " .
				$user .
				" " .
				quoteshellarg($v_domain) .
				" " .
				quoteshellarg($v_runtime) .
				" " .
				quoteshellarg($v_version) .
				" " .
				quoteshellarg($v_cmd) .
				" " .
				quoteshellarg($v_dir) .
				" " .
				quoteshellarg($v_install),
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($output);
	}

	// Set environment variables
	if (empty($_SESSION["error_msg"]) && trim($v_env) !== "") {
		[$return_var, $output] = save_web_app_env($user, $v_domain, $v_env);
		if ($return_var != 0) {
			$_SESSION["error_msg"] =
				_("The app has been created, but the environment variables could not be saved.") .
				" " .
				implode(" ", $output);
			unset($output);
			header(
				"Location: /edit/app/?" .
					http_build_query(array_merge(["domain" => $v_domain], $user_query)),
			);
			exit();
		}
		unset($output);
	}

	// Redirect to the app on success
	if (empty($_SESSION["error_msg"])) {
		$_SESSION["ok_msg"] = sprintf(
			_("App for %s has been created successfully."),
			htmlentities($v_domain),
		);
		header(
			"Location: /edit/app/?" .
				http_build_query(array_merge(["domain" => $v_domain], $user_query)),
		);
		exit();
	}
}

// List web domains
exec(HESTIA_CMD . "v-list-web-domains " . $user . " json", $output, $return_var);
$web_domains = json_decode(implode("", $output), true);
if (!is_array($web_domains)) {
	$web_domains = [];
}
unset($output);

// Exclude domains that already have an app
exec(HESTIA_CMD . "v-list-web-domain-apps " . $user . " json", $output, $return_var);
$apps = json_decode(implode("", $output), true);
if (!is_array($apps)) {
	$apps = [];
}
unset($output);

// Domain already has an app: go to its settings
if (empty($_POST["ok"]) && !empty($_GET["domain"]) && isset($apps[$_GET["domain"]])) {
	header(
		"Location: /edit/app/?" .
			http_build_query(array_merge(["domain" => $_GET["domain"]], $user_query)),
	);
	exit();
}

$domains = [];
foreach ($web_domains as $domain => $value) {
	if (!isset($apps[$domain]) && ($value["SUSPENDED"] ?? "no") !== "yes") {
		$domains[] = $domain;
	}
}
sort($domains);

// Node.js versions
$node_versions = list_web_app_node_versions();

// Default field values
if (empty($v_domain)) {
	$v_domain = $_GET["domain"] ?? "";
}
if (empty($v_runtime) || !array_key_exists($v_runtime, $runtimes)) {
	$v_runtime = "node";
}
if (empty($v_version)) {
	$v_version = "system";
}
if (!isset($v_cmd)) {
	$v_cmd = "";
}
if (empty($v_dir)) {
	$v_dir = "app";
}
if (!isset($v_install)) {
	$v_install = "";
}
if (!isset($v_env)) {
	$v_env = "";
}

// Render
render_page($user, $TAB, "add_app");

// Flush session messages
unset($_SESSION["error_msg"]);
unset($_SESSION["ok_msg"]);
