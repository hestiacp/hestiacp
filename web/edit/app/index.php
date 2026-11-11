<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
$TAB = "WEB";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Edit as someone else?
$user_query = [];
$v_username = !empty($_SESSION["look"]) ? $_SESSION["look"] : $_SESSION["user"];
if ($_SESSION["userContext"] === "admin" && !empty($_GET["user"])) {
	$user = quoteshellarg($_GET["user"]);
	$user_plain = htmlentities($_GET["user"]);
	$user_query = ["user" => $_GET["user"]];
	$v_username = $_GET["user"];
}

// Check domain argument
if (empty($_GET["domain"])) {
	header("Location: /list/app/?" . http_build_query($user_query));
	exit();
}

$v_domain = $_GET["domain"];
$domain_query = array_merge(["domain" => $v_domain], $user_query);
$runtimes = ["node" => "Node.js", "python" => "Python", "custom" => _("Custom command")];

// Load app
$load_app = function () use ($user, $v_domain, $user_query) {
	exec(
		HESTIA_CMD . "v-list-web-domain-app " . $user . " " . quoteshellarg($v_domain) . " json",
		$output,
		$return_var,
	);
	check_return_code_redirect($return_var, $output, "/list/app/?" . http_build_query($user_query));
	$data = json_decode(implode("", $output), true);
	if (!is_array($data) || !isset($data[$v_domain])) {
		header("Location: /list/app/?" . http_build_query($user_query));
		exit();
	}
	return $data[$v_domain];
};
$app = $load_app();

$v_runtime = $app["RUNTIME"] ?? "node";
$v_version = $app["VERSION"] ?? "system";
$v_cmd = $app["CMD"] ?? "";
$v_dir = $app["DIR"] ?? "app";
$v_install = $app["INSTALL"] ?? "";

// Save settings
if (!empty($_POST["save"]) && $read_only !== true) {
	// Check token
	verify_csrf($_POST);

	$v_runtime = $_POST["v_runtime"] ?? "";
	$v_version = trim($_POST["v_version"] ?? "system");
	$v_cmd = trim($_POST["v_cmd"] ?? "");
	$v_dir = trim($_POST["v_dir"] ?? "");
	$v_install = trim($_POST["v_install"] ?? "");

	// Check empty fields
	$errors = [];
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

	// Save changes
	if (empty($_SESSION["error_msg"])) {
		exec(
			HESTIA_CMD .
				"v-change-web-domain-app " .
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

	// Set success message
	if (empty($_SESSION["error_msg"])) {
		$_SESSION["ok_msg"] = _("Changes have been saved.");
		$app = $load_app();
	}
}

// Save environment variables
if (!empty($_POST["save_env"]) && $read_only !== true) {
	// Check token
	verify_csrf($_POST);

	$v_env = $_POST["v_env"] ?? "";
	[$return_var, $output] = save_web_app_env($user, $v_domain, $v_env);
	check_return_code($return_var, $output);
	unset($output);

	// Set success message
	if (empty($_SESSION["error_msg"])) {
		$_SESSION["ok_msg"] = _("Environment variables have been saved.");
		unset($v_env);
		$app = $load_app();
	}
}

// Live status
$v_state = $app["STATE"] ?? "";
$v_status = $app["STATUS"] ?? "";
$v_state_info = get_web_app_state($v_state);
$v_is_up = in_array($v_state, ["active", "activating", "reloading"], true);
$v_suspended = ($app["SUSPENDED"] ?? "no") === "yes";
$v_pid = $app["PID"] ?? "";
$v_cpu = is_numeric($app["CPU"] ?? null) ? (float) $app["CPU"] : 0;
$v_memory = is_numeric($app["MEMORY"] ?? null) ? (int) $app["MEMORY"] : 0;
$v_uptime = is_numeric($app["UPTIME"] ?? null) ? (int) $app["UPTIME"] : 0;
$v_restarts = $app["RESTARTS"] ?? "0";
$v_port = $app["PORT"] ?? "";

// Environment variables
if (!isset($v_env)) {
	exec(
		HESTIA_CMD .
			"v-list-web-domain-app-env " .
			$user .
			" " .
			quoteshellarg($v_domain) .
			" plain",
		$output,
		$return_var,
	);
	$v_env = $return_var == 0 ? implode("\n", $output) : "";
	unset($output);
}

// Logs
exec(
	HESTIA_CMD . "v-list-web-domain-app-log " . $user . " " . quoteshellarg($v_domain) . " 200",
	$output,
	$return_var,
);
$v_log = implode("\n", $output);
unset($output);

// Output of the last dependency install / build
$v_deps_output = null;
if (isset($_SESSION["app_deps_output"][$v_domain])) {
	$v_deps_output = $_SESSION["app_deps_output"][$v_domain];
	unset($_SESSION["app_deps_output"][$v_domain]);
}

// Node.js versions
$node_versions = list_web_app_node_versions();
if ($v_runtime === "node" && !isset($node_versions[$v_version])) {
	$node_versions[$v_version] = "";
}

// Render page
render_page($user, $TAB, "edit_app");

// Back uri for start / stop / restart / install actions
$_SESSION["back"] = "/edit/app/?" . http_build_query($domain_query);

// Flush session messages
unset($_SESSION["error_msg"]);
unset($_SESSION["ok_msg"]);
