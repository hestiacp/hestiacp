<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
$TAB = "WEB";

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

if (empty($_GET["domain"])) {
	header("Location: /list/web/");
	exit();
}

if ($_SESSION["userContext"] === "admin" && !empty($_GET["user"])) {
	$user = quoteshellarg($_GET["user"]);
	$user_plain = htmlentities($_GET["user"]);
}

$v_domain = $_GET["domain"];
$v_domain_arg = quoteshellarg($v_domain);
$v_laravel_url_query = ["domain" => $v_domain];
if ($_SESSION["userContext"] === "admin" && !empty($_GET["user"])) {
	$v_laravel_url_query["user"] = $_GET["user"];
}

exec(
	HESTIA_CMD . "v-list-web-domain " . $user . " " . $v_domain_arg . " json",
	$output,
	$return_var,
);
check_return_code_redirect($return_var, $output, "/list/web/");
$web_domain_data = json_decode(implode("", $output), true);
$v_laravel_site_scheme = ($web_domain_data[$v_domain]["SSL"] ?? "") === "yes" ? "https" : "http";
unset($output);

function laravel_env_value(string $value): string {
	$value = trim($value);
	$first = substr($value, 0, 1);
	$last = substr($value, -1);
	if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
		return substr($value, 1, -1);
	}

	return $value;
}

function laravel_exec_command(
	string $user,
	string $domain,
	string $type,
	string $command,
	string $packageManager = "npm",
): array {
	$parts = str_getcsv(trim($command), " ");
	$parts = array_values(array_filter($parts, fn($part) => $part !== ""));
	if (empty($parts)) {
		return [1, [_("Command is empty.")]];
	}

	$cmd = match ($type) {
		"composer" => "v-run-laravel-composer",
		"node" => "v-run-laravel-node",
		default => "v-run-laravel-artisan",
	};

	$exec = HESTIA_CMD . $cmd . " " . $user . " " . quoteshellarg($domain);
	if ($type === "node") {
		$exec .= " " . quoteshellarg($packageManager);
	}
	foreach ($parts as $part) {
		$exec .= " " . quoteshellarg($part);
	}

	$output = [];
	exec($exec, $output, $return_var);

	return [$return_var, $output];
}

function laravel_update_from_textarea(
	string $command,
	string $user,
	string $domain,
	string $contents,
): array {
	$tmpFile = tempnam("/tmp", "hst-laravel.");
	if ($tmpFile === false) {
		return [1, [_("Unable to create temporary file.")]];
	}
	file_put_contents($tmpFile, $contents);

	$output = [];
	exec(
		HESTIA_CMD .
			$command .
			" " .
			$user .
			" " .
			quoteshellarg($domain) .
			" " .
			quoteshellarg($tmpFile),
		$output,
		$return_var,
	);
	unlink($tmpFile);

	return [$return_var, $output];
}

exec(
	HESTIA_CMD . "v-list-laravel-app " . $user . " " . $v_domain_arg . " json",
	$output,
	$return_var,
);
if ($return_var !== 0) {
	unset($output);
	exec(
		HESTIA_CMD . "v-scan-laravel-app " . $user . " " . $v_domain_arg . " json",
		$output,
		$return_var,
	);
}
check_return_code_redirect(
	$return_var,
	$output,
	"/edit/web/?" . http_build_query($v_laravel_url_query),
);
unset($output);

if (!empty($_POST["action"])) {
	verify_csrf($_POST);

	$action = $_POST["action"];
	$redirect_tab = match ($action) {
		"deploy", "deploy_script" => "deployment",
		"env" => "environment",
		"scheduler" => "scheduler",
		"queue", "retry_failed", "flush_failed" => "queue",
		default => "dashboard",
	};
	$output = [];
	$return_var = 0;

	switch ($action) {
		case "deploy":
			exec(
				HESTIA_CMD . "v-deploy-laravel-app " . $user . " " . $v_domain_arg . " manual",
				$output,
				$return_var,
			);
			break;
		case "env":
			[$return_var, $output] = laravel_update_from_textarea(
				"v-change-laravel-env",
				$user,
				$v_domain,
				$_POST["env"] ?? "",
			);
			break;
		case "deploy_script":
			[$return_var, $output] = laravel_update_from_textarea(
				"v-change-laravel-deploy-script",
				$user,
				$v_domain,
				$_POST["deploy_script"] ?? "",
			);
			break;
		case "command":
			$command_type = $_POST["command_type"] ?? "artisan";
			if (!in_array($command_type, ["artisan", "composer", "node"], true)) {
				$command_type = "artisan";
			}
			$redirect_tab = $command_type;
			[$return_var, $output] = laravel_exec_command(
				$user,
				$v_domain,
				$command_type,
				$_POST["command"] ?? "",
				$_POST["package_manager"] ?? "npm",
			);
			$_SESSION["laravel_command_output"] = implode("\n", $output);
			$_SESSION["laravel_command_status"] = $return_var === 0 ? "success" : "failed";
			$_SESSION["laravel_command_type"] = $command_type;
			$_SESSION["laravel_command_text"] = $_POST["command"] ?? "";
			$_SESSION["laravel_command_time"] = date("H:i:s");
			break;
		case "maintenance":
			$state = !empty($_POST["maintenance"]) ? "yes" : "no";
			exec(
				HESTIA_CMD .
					"v-change-laravel-maintenance " .
					$user .
					" " .
					$v_domain_arg .
					" " .
					$state,
				$output,
				$return_var,
			);
			break;
		case "scheduler":
			$state = !empty($_POST["scheduler"]) ? "yes" : "no";
			exec(
				HESTIA_CMD .
					"v-change-laravel-scheduler " .
					$user .
					" " .
					$v_domain_arg .
					" " .
					$state,
				$output,
				$return_var,
			);
			break;
		case "queue":
			$state = !empty($_POST["queue"]) ? "yes" : "no";
			$connection = quoteshellarg($_POST["queue_connection"] ?? "database");
			$timeout = quoteshellarg($_POST["queue_timeout"] ?? "60");
			$maxJobs = quoteshellarg($_POST["queue_max_jobs"] ?? "0");
			$maxTime = quoteshellarg($_POST["queue_max_time"] ?? "0");
			$stopWhenEmpty = !empty($_POST["queue_stop_when_empty"]) ? "yes" : "no";
			exec(
				HESTIA_CMD .
					"v-change-laravel-queue " .
					$user .
					" " .
					$v_domain_arg .
					" " .
					$state .
					" " .
					$connection .
					" " .
					$timeout .
					" " .
					$maxJobs .
					" " .
					$maxTime .
					" " .
					$stopWhenEmpty,
				$output,
				$return_var,
			);
			break;
		case "retry_failed":
			exec(
				HESTIA_CMD . "v-retry-laravel-failed-job " . $user . " " . $v_domain_arg . " all",
				$output,
				$return_var,
			);
			break;
		case "flush_failed":
			exec(
				HESTIA_CMD . "v-delete-laravel-failed-job " . $user . " " . $v_domain_arg . " all",
				$output,
				$return_var,
			);
			break;
		default:
			$return_var = 1;
			$output = [_("Invalid Laravel action.")];
			break;
	}

	if ($return_var === 0) {
		$_SESSION["ok_msg"] = _("Laravel action completed successfully.");
	} else {
		$_SESSION["error_msg"] = implode("<br>", $output);
	}

	header(
		"Location: /edit/laravel/?" .
			http_build_query($v_laravel_url_query) .
			"#tab-laravel-" .
			rawurlencode($redirect_tab),
	);
	exit();
}

exec(
	HESTIA_CMD . "v-list-laravel-app " . $user . " " . $v_domain_arg . " json",
	$output,
	$return_var,
);
check_return_code_redirect(
	$return_var,
	$output,
	"/edit/web/?" . http_build_query($v_laravel_url_query),
);
$laravel_data = json_decode(implode("", $output), true);
$v_laravel = $laravel_data[$v_domain] ?? [];
unset($output);

exec(HESTIA_CMD . "v-open-laravel-env " . $user . " " . $v_domain_arg, $output, $return_var);
$v_laravel_env = $return_var === 0 ? implode("\n", $output) : "";
unset($output);

exec(
	HESTIA_CMD . "v-list-laravel-log " . $user . " " . $v_domain_arg . " 200",
	$output,
	$return_var,
);
$v_laravel_log = $return_var === 0 ? implode("\n", $output) : "";
unset($output);

exec(
	HESTIA_CMD . "v-list-laravel-failed-jobs " . $user . " " . $v_domain_arg,
	$output,
	$return_var,
);
$v_laravel_failed_jobs = $return_var === 0 ? implode("\n", $output) : "";
unset($output);

$v_laravel_env_summary = [
	"APP_ENV" => "",
	"APP_DEBUG" => "",
];
foreach (explode("\n", $v_laravel_env) as $line) {
	foreach ($v_laravel_env_summary as $key => $value) {
		if (str_starts_with($line, $key . "=")) {
			$v_laravel_env_summary[$key] = laravel_env_value(substr($line, strlen($key) + 1));
		}
	}
}

$v_laravel_status_cards = [
	[
		"label" => _("PHP"),
		"value" => $v_laravel["PHP_VERSION"] ?? "",
		"state" => _("Runtime"),
		"icon" => "fa-code",
		"tone" => "blue",
	],
	[
		"label" => _("Application root"),
		"value" => $v_laravel["APP_ROOT"] ?? "",
		"state" => _("Project"),
		"icon" => "fa-folder-tree",
		"tone" => "purple",
	],
	[
		"label" => _("Public path"),
		"value" => ($v_laravel["APP_ROOT"] ?? "") . "/public",
		"state" => _("Web root"),
		"icon" => "fa-globe",
		"tone" => "green",
	],
	[
		"label" => _("Environment"),
		"value" => $v_laravel_env_summary["APP_ENV"] ?: _("Not set"),
		"state" => _("APP_ENV"),
		"icon" => "fa-sliders",
		"tone" => "blue",
	],
	[
		"label" => _("Debug mode"),
		"value" => $v_laravel_env_summary["APP_DEBUG"] ?: _("Not set"),
		"state" => _("APP_DEBUG"),
		"icon" => "fa-bug",
		"tone" =>
			strtolower($v_laravel_env_summary["APP_DEBUG"] ?? "") === "true" ? "orange" : "green",
	],
	[
		"label" => _("Maintenance"),
		"value" => $v_laravel["MAINTENANCE"] ?? "no",
		"state" => _("Mode"),
		"icon" => "fa-power-off",
		"tone" => ($v_laravel["MAINTENANCE"] ?? "") === "yes" ? "orange" : "green",
	],
	[
		"label" => _("Scheduler"),
		"value" => $v_laravel["SCHEDULER"] ?? "no",
		"state" => _("Cron"),
		"icon" => "fa-clock",
		"tone" => ($v_laravel["SCHEDULER"] ?? "") === "yes" ? "green" : "muted",
	],
	[
		"label" => _("Queue"),
		"value" => $v_laravel["QUEUE"] ?? "no",
		"state" => _("Worker"),
		"icon" => "fa-gears",
		"tone" => ($v_laravel["QUEUE"] ?? "") === "yes" ? "green" : "muted",
	],
];

$v_laravel_recommendations = [];
if (strtolower($v_laravel_env_summary["APP_DEBUG"] ?? "") === "true") {
	$v_laravel_recommendations[] = _("APP_DEBUG is enabled. Disable it before production traffic.");
}
if (empty($v_laravel["GIT_BRANCH"] ?? "") && empty($v_laravel["BRANCH"] ?? "")) {
	$v_laravel_recommendations[] = _(
		"No Git branch is registered. Deployment will use local files only.",
	);
}
if (($v_laravel["QUEUE"] ?? "") !== "yes") {
	$v_laravel_recommendations[] = _(
		"Queue worker is disabled. Enable it if this application processes background jobs.",
	);
}
if (($v_laravel["SCHEDULER"] ?? "") !== "yes") {
	$v_laravel_recommendations[] = _(
		"Scheduler is disabled. Enable it if this application uses Laravel scheduled tasks.",
	);
}

$v_laravel_command_presets = [
	"artisan" => ["about", "migrate --force", "optimize", "config:clear", "queue:restart"],
	"composer" => ["install --no-dev --optimize-autoloader", "update", "dump-autoload"],
	"node" => ["install", "run build", "run dev"],
];

$v_laravel_deploy_summary = [
	"mode" => empty($v_laravel["REPO_URL"] ?? "") ? _("Manual") : _("Manual / Webhook"),
	"source" => $v_laravel["SOURCE_TYPE"] ?? "",
	"repository" => $v_laravel["REPO_URL"] ?? "",
	"branch" => $v_laravel["GIT_BRANCH"] ?? ($v_laravel["BRANCH"] ?? ""),
	"commit" => $v_laravel["GIT_COMMIT"] ?? "",
	"scenario" => [
		_("Pull latest Git changes when a repository is present"),
		_("Install Composer dependencies for production"),
		_("Install and build Node assets when package.json exists"),
		_("Run database migrations with --force"),
		_("Rebuild Laravel caches and restart queues"),
	],
];

$v_laravel_log_lines = $v_laravel_log === "" ? 0 : substr_count($v_laravel_log, "\n") + 1;
$v_laravel_log_summary = [
	"name" => _("Laravel log"),
	"lines" => $v_laravel_log_lines,
	"empty" => $v_laravel_log === "",
];

$v_laravel_failed_jobs_summary = [
	"empty" =>
		trim($v_laravel_failed_jobs) === "" ||
		str_contains($v_laravel_failed_jobs, "No failed jobs found"),
	"output" => $v_laravel_failed_jobs,
];

$v_laravel_command_result = [
	"status" => $_SESSION["laravel_command_status"] ?? "",
	"type" => $_SESSION["laravel_command_type"] ?? "",
	"command" => $_SESSION["laravel_command_text"] ?? "",
	"time" => $_SESSION["laravel_command_time"] ?? "",
	"output" => $_SESSION["laravel_command_output"] ?? "",
];

if (!in_array($v_laravel_command_result["type"], ["artisan", "composer", "node"], true)) {
	$v_laravel_command_result["type"] = "";
}

$deployScriptPath =
	"/home/" . trim($user, "'") . "/web/" . $v_domain . "/private/laravel/deploy.sh";
$v_laravel_deploy_script = is_readable($deployScriptPath)
	? file_get_contents($deployScriptPath)
	: "";
$v_laravel_scheme = !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off" ? "https" : "http";
$v_laravel_host = $_SERVER["HTTP_HOST"] ?? "";
$v_laravel_site_url = $v_laravel_site_scheme . "://" . $v_domain;
$v_laravel_webhook_url =
	($v_laravel_host === "" ? "" : $v_laravel_scheme . "://" . $v_laravel_host) .
	"/deploy/laravel/?" .
	http_build_query([
		"domain" => $v_domain,
		"secret" => $v_laravel["WEBHOOK_SECRET"] ?? "",
	]);

render_page($user, $TAB, "edit_laravel");

unset(
	$_SESSION["error_msg"],
	$_SESSION["ok_msg"],
	$_SESSION["laravel_command_output"],
	$_SESSION["laravel_command_status"],
	$_SESSION["laravel_command_type"],
	$_SESSION["laravel_command_text"],
	$_SESSION["laravel_command_time"],
);
