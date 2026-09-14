<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
$TAB = "WEB";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Set user
$user_plain = $_SESSION["user"];
if (!empty($_SESSION["look"])) {
	$user_plain = $_SESSION["look"];
}
if ($_SESSION["userContext"] === "admin" && !empty($_GET["user"])) {
	$user_plain = $_GET["user"];
}
if ($_SESSION["userContext"] === "admin" && !empty($_POST["v_user"])) {
	$user_plain = $_POST["v_user"];
}

// Check selected domain
$v_domain = !empty($_GET["domain"]) ? trim($_GET["domain"]) : (!empty($_POST["v_domain"]) ? trim($_POST["v_domain"]) : "");

// If admin and domain is given, auto-resolve owner user to ensure correct permissions
if (!empty($v_domain) && $_SESSION["userContext"] === "admin") {
	exec(HESTIA_CMD . "v-search-domain-owner " . quoteshellarg($v_domain) . " web", $owner_out, $owner_res);
	if ($owner_res === 0 && !empty($owner_out[0])) {
		$user_plain = trim($owner_out[0]);
	}
}

$user = quoteshellarg($user_plain);

function parse_pm2_json_output($raw) {
	if (empty($raw)) return [];
	$str = is_array($raw) ? implode("\n", $raw) : $raw;
	$decoded = json_decode($str, true);
	if (is_array($decoded)) return $decoded;

	if (preg_match('/(\[\s*\{.*\}\s*\]|\[\s*\])/s', $str, $matches)) {
		$decoded = json_decode($matches[1], true);
		if (is_array($decoded)) return $decoded;
	}
	return [];
}

// AJAX Endpoints
if (!empty($_GET["ajax"])) {
	header("Content-Type: application/json");
	$ajax_type = $_GET["ajax"];

	if ($ajax_type === "create_app") {
		$sel_domain = $_GET["domain"] ?? "";
		if (empty($sel_domain) || $read_only === true) {
			echo json_encode(["success" => false]);
			exit();
		}
		exec(HESTIA_CMD . "v-node-manager create_app " . $user . " " . quoteshellarg($sel_domain), $out, $res);
		echo json_encode([
			"success" => ($res === 0),
			"path" => "/home/" . $user_plain . "/web/" . $sel_domain . "/public_html/app"
		]);
		exit();
	}

	if ($ajax_type === "get_stats") {
		exec(HESTIA_CMD . "v-node-manager list " . $user, $pm2_raw);
		$raw_apps = parse_pm2_json_output($pm2_raw);
		$formatted = [];
		if (is_array($raw_apps)) {
			foreach ($raw_apps as $a) {
				$uptime_min = isset($a["pm2_env"]["pm_uptime"]) ? round((time() - ($a["pm2_env"]["pm_uptime"] / 1000)) / 60) : 0;
				$uptime_str = ($uptime_min >= 60) ? (round($uptime_min / 60, 1) . " hrs") : ($uptime_min . " min");
				$formatted[] = [
					"name" => $a["name"] ?? "",
					"status" => $a["pm2_env"]["status"] ?? "unknown",
					"cpu" => ($a["monit"]["cpu"] ?? 0) . "%",
					"memory" => round(($a["monit"]["memory"] ?? 0) / 1024 / 1024, 1) . " MB",
					"uptime" => $uptime_str,
					"pid" => $a["pid"] ?? "-"
				];
			}
		}
		echo json_encode(["success" => true, "apps" => $formatted]);
		exit();
	}

	if ($ajax_type === "get_logs") {
		$log_domain = $_GET["domain"] ?? "";
		$lines = intval($_GET["lines"] ?? 100);
		$type = in_array($_GET["type"] ?? "all", ["all", "out", "err"]) ? $_GET["type"] : "all";
		if (empty($log_domain)) {
			echo json_encode(["success" => false, "logs" => "Domain is required"]);
			exit();
		}
		exec(HESTIA_CMD . "v-node-manager logs " . $user . " " . quoteshellarg($log_domain) . " " . quoteshellarg($lines) . " " . quoteshellarg($type), $out, $res);
		echo json_encode(["success" => ($res === 0), "logs" => implode("\n", $out)]);
		exit();
	}

	if ($ajax_type === "ajax_action" && $read_only !== true) {
		$token = $_GET["token"] ?? "";
		if (empty($token) || $token !== $_SESSION["token"]) {
			echo json_encode(["success" => false, "message" => _("Invalid security token.")]);
			exit();
		}
		$action = $_GET["action"] ?? "";
		$act_domain = $_GET["domain"] ?? "";
		if (in_array($action, ["restart", "stop", "delete"])) {
			exec(HESTIA_CMD . "v-node-manager " . quoteshellarg($action) . " " . $user . " " . quoteshellarg($act_domain), $out, $res);
			echo json_encode([
				"success" => ($res === 0),
				"message" => ($res === 0) ? sprintf(_("Action '%s' completed successfully."), htmlspecialchars($action)) : implode(" ", $out)
			]);
			exit();
		}
		echo json_encode(["success" => false, "message" => _("Invalid action.")]);
		exit();
	}

	if ($ajax_type === "npm_install" && $read_only !== true) {
		$inst_domain = $_GET["domain"] ?? "";
		$inst_path = $_GET["path"] ?? "";
		if (empty($inst_domain)) {
			echo json_encode(["success" => false, "output" => _("Domain is required.")]);
			exit();
		}
		exec(HESTIA_CMD . "v-node-manager npm_install " . $user . " " . quoteshellarg($inst_domain) . " 0 " . quoteshellarg($inst_path), $out, $res);
		echo json_encode([
			"success" => ($res === 0),
			"output" => !empty($out) ? implode("\n", $out) : "npm install completed."
		]);
		exit();
	}

	if ($ajax_type === "inspect_app") {
		$insp_domain = $_GET["domain"] ?? "";
		$insp_path = $_GET["path"] ?? "";
		if (empty($insp_domain)) {
			echo json_encode(["exists" => false, "dir_exists" => false]);
			exit();
		}
		exec(HESTIA_CMD . "v-node-manager inspect_app " . $user . " " . quoteshellarg($insp_domain) . " 0 " . quoteshellarg($insp_path), $out, $res);
		$parsed = !empty($out) ? json_decode(implode("", $out), true) : ["exists" => false];
		if (!is_array($parsed)) {
			$parsed = ["exists" => false];
		}
		if (!isset($parsed["dir_exists"])) {
			$check_p = $insp_path ?: "/home/" . $user_plain . "/web/" . $insp_domain . "/public_html/app";
			$parsed["dir_exists"] = is_dir($check_p);
		}
		echo json_encode($parsed);
		exit();
	}
}

// Handle fallback application control actions (restart, stop, delete)
if (!empty($_GET["action"]) && !empty($_GET["domain"]) && $read_only !== true) {
	check_csrf_token();
	$act_domain = $_GET["domain"];
	$action = $_GET["action"];

	if (in_array($action, ["restart", "stop", "delete"])) {
		exec(HESTIA_CMD . "v-node-manager " . quoteshellarg($action) . " " . $user . " " . quoteshellarg($act_domain), $out, $res);
		if ($res === 0) {
			if ($action === "delete") {
				$_SESSION["ok_msg"] = sprintf(_("Node.js application '%s' was deleted successfully."), htmlspecialchars($act_domain));
			} elseif ($action === "restart") {
				$_SESSION["ok_msg"] = sprintf(_("Node.js application '%s' was restarted successfully."), htmlspecialchars($act_domain));
			} elseif ($action === "stop") {
				$_SESSION["ok_msg"] = sprintf(_("Node.js application '%s' was stopped successfully."), htmlspecialchars($act_domain));
			}
		} else {
			$_SESSION["error_msg"] = !empty($out) ? implode(" ", $out) : _("An error occurred executing the action.");
		}
	}
	header("Location: /add/node/" . (!empty($v_domain) ? "?domain=" . urlencode($v_domain) : ""));
	exit();
}

// Handle add application form submission
if (($_SERVER["REQUEST_METHOD"] === "POST" || !empty($_POST["btn_add"])) && $read_only !== true) {
	verify_csrf($_POST);

	$v_domain = $_POST["v_domain"] ?? "";
	$v_port = intval($_POST["v_port"] ?? 3000);
	$v_path = trim($_POST["v_path"] ?? "");
	$v_entry = trim($_POST["v_entry"] ?? "app.js");

	if (empty($v_domain) || empty($v_port) || empty($v_path) || empty($v_entry)) {
		$_SESSION["error_msg"] = _("All fields are required.");
	} else {
		exec(
			HESTIA_CMD . "v-node-manager add " .
			$user . " " .
			quoteshellarg($v_domain) . " " .
			quoteshellarg($v_port) . " " .
			quoteshellarg($v_path) . " " .
			quoteshellarg($v_entry),
			$out,
			$res
		);

		if ($res === 0) {
			$_SESSION["ok_msg"] = sprintf(_("Node.js application '%s' deployed and started on port %s."), htmlspecialchars($v_domain), htmlspecialchars($v_port));
			header("Location: /add/node/?domain=" . urlencode($v_domain));
			exit();
		} else {
			$_SESSION["error_msg"] = !empty($out) ? implode(" ", $out) : _("Failed to start application.");
		}
	}
}

// Check Node.js and PM2 environment
exec(HESTIA_CMD . "v-node-manager check_env " . $user, $check_out, $check_res);
$node_installed = ($check_res === 0);
$env_data = !empty($check_out) ? json_decode(implode("", $check_out), true) : [];

// Get versions
$node_v = $env_data["node"] ?? "N/A";
$npm_v = $env_data["npm"] ?? "N/A";
$pm2_v = $env_data["pm2"] ?? "N/A";

// Clean up versions if needed
if ($pm2_v !== "N/A" && preg_match('/(\d+\.\d+\.\d+)/', $pm2_v, $m)) {
	$pm2_v = $m[1];
}
if ($node_v !== "N/A" && preg_match('/v?(\d+\.\d+\.\d+)/', $node_v, $m)) {
	$node_v = 'v' . $m[1];
}
if ($npm_v !== "N/A" && preg_match('/(\d+\.\d+\.\d+)/', $npm_v, $m)) {
	$npm_v = $m[1];
}

// Fallback if any version remained N/A but node is installed
if ($node_installed && ($node_v === "N/A" || $pm2_v === "N/A" || $npm_v === "N/A")) {
	if ($node_v === "N/A") {
		$nv = @shell_exec("PATH=\$PATH:/usr/local/bin:/usr/bin node -v 2>/dev/null");
		if ($nv && preg_match('/v?(\d+\.\d+\.\d+)/', $nv, $m)) $node_v = 'v' . $m[1];
	}
	if ($npm_v === "N/A") {
		$npv = @shell_exec("PATH=\$PATH:/usr/local/bin:/usr/bin npm -v 2>/dev/null");
		if ($npv && preg_match('/(\d+\.\d+\.\d+)/', $npv, $m)) $npm_v = $m[1];
	}
	if ($pm2_v === "N/A") {
		$pv = @shell_exec("PATH=\$PATH:/usr/local/bin:/usr/bin pm2 -v 2>/dev/null");
		if ($pv && preg_match('/(\d+\.\d+\.\d+)/', $pv, $m)) {
			$pm2_v = $m[1];
		} else {
			// Try reading pm2 package.json directly
			$pm2_pkg = @file_get_contents('/usr/lib/node_modules/pm2/package.json');
			if (!$pm2_pkg) $pm2_pkg = @file_get_contents('/usr/local/lib/node_modules/pm2/package.json');
			if ($pm2_pkg) {
				$pkg_json = json_decode($pm2_pkg, true);
				if (!empty($pkg_json['version'])) $pm2_v = $pkg_json['version'];
			}
		}
	}
}

// Get user domains
exec(HESTIA_CMD . "v-list-web-domains " . $user . " json", $dom_raw);
$user_domains = !empty($dom_raw) ? json_decode(implode("", $dom_raw), true) : [];
if (!empty($v_domain) && !isset($user_domains[$v_domain])) {
	$user_domains[$v_domain] = ["DOMAIN" => $v_domain];
}

// Check if default app directory already exists
$app_dir_exists = false;
if (!empty($v_domain)) {
	$default_app_dir = "/home/" . $user_plain . "/web/" . $v_domain . "/public_html/app";
	$app_dir_exists = is_dir($default_app_dir);
}

// Get PM2 processes list
$pm2_apps = [];
if ($node_installed) {
	exec(HESTIA_CMD . "v-node-manager list " . $user, $pm2_raw);
	$pm2_apps = parse_pm2_json_output($pm2_raw);
	if (!is_array($pm2_apps)) {
		$pm2_apps = [];
	}
}

// Suggested Port discovery (3000+)
$used_ports = [];
foreach ($pm2_apps as $app) {
	if (isset($app["pm2_env"]["PORT"])) {
		$used_ports[] = intval($app["pm2_env"]["PORT"]);
	}
}
$suggested_port = 3000;
while (in_array($suggested_port, $used_ports)) {
	$suggested_port++;
}

// Render Page
render_page($user, $TAB, "setup_node");

// Flush session messages
unset($_SESSION["error_msg"]);
unset($_SESSION["ok_msg"]);

