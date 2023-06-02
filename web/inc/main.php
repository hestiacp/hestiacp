<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use function Hestiacp\quoteshellarg\quoteshellarg;

try {
	require_once "vendor/autoload.php";
} catch (Throwable $ex) {
	$errstr =
		"Unable to load required libraries. Please run v-add-sys-dependencies in command line. Error: " .
		$ex->getMessage();
	trigger_error($errstr);
	echo $errstr;
	exit(1);
}

define("HESTIA_DIR", "/usr/local/hestia/");
define("HESTIA_DIR_WEB", "/usr/local/hestia/web/");
define("HESTIA_DIR_BIN", "/usr/local/hestia/bin/");
define("HESTIA_CMD", "/usr/bin/sudo /usr/local/hestia/bin/");
define("DEFAULT_PHP_VERSION", "php-" . exec('php -r "echo substr(phpversion(),0,3);"'));

// Load Hestia Config directly
load_hestia_config();
require_once dirname(__FILE__) . "/prevent_csrf.php";
require_once dirname(__FILE__) . "/helpers.php";
$root_directory = dirname(__FILE__) . "/../../";

function destroy_sessions() {
	unset($_SESSION);
	session_unset();
	session_destroy();
	session_start();
}

$i = 0;

// Saving user IPs to the session for preventing session hijacking
$user_combined_ip = "";
if (isset($_SERVER["REMOTE_ADDR"])) {
	$user_combined_ip = $_SERVER["REMOTE_ADDR"];
}
if (isset($_SERVER["HTTP_CLIENT_IP"])) {
	$user_combined_ip .= "|" . $_SERVER["HTTP_CLIENT_IP"];
}
if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
	$user_combined_ip .= "|" . $_SERVER["HTTP_X_FORWARDED_FOR"];
}
if (isset($_SERVER["HTTP_FORWARDED_FOR"])) {
	$user_combined_ip .= "|" . $_SERVER["HTTP_FORWARDED_FOR"];
}
if (isset($_SERVER["HTTP_X_FORWARDED"])) {
	$user_combined_ip .= "|" . $_SERVER["HTTP_X_FORWARDED"];
}
if (isset($_SERVER["HTTP_FORWARDED"])) {
	$user_combined_ip .= "|" . $_SERVER["HTTP_FORWARDED"];
}
if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
	if (!empty($_SERVER["HTTP_CF_CONNECTING_IP"])) {
		$user_combined_ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
	}
}

if (!isset($_SESSION["user_combined_ip"])) {
	$_SESSION["user_combined_ip"] = $user_combined_ip;
}

// Checking user to use session from the same IP he has been logged in
if (
	$_SESSION["user_combined_ip"] != $user_combined_ip &&
	isset($_SESSION["user"]) &&
	$_SESSION["DISABLE_IP_CHECK"] != "yes"
) {
	$v_user = quoteshellarg($_SESSION["user"]);
	$v_session_id = quoteshellarg($_SESSION["token"]);
	exec(HESTIA_CMD . "v-log-user-logout " . $v_user . " " . $v_session_id, $output, $return_var);
	destroy_sessions();
	header("Location: /login/");
	exit();
}

// Check system settings
if (!isset($_SESSION["VERSION"]) && !defined("NO_AUTH_REQUIRED")) {
	destroy_sessions();
	header("Location: /login/");
	exit();
}

// Check user session
if (!isset($_SESSION["user"]) && !defined("NO_AUTH_REQUIRED")) {
	destroy_sessions();
	header("Location: /login/");
	exit();
}

// Generate CSRF Token and set user shell variable
if (isset($_SESSION["user"])) {
	if (!isset($_SESSION["token"])) {
		$token = bin2hex(random_bytes(16));
		$_SESSION["token"] = $token;
	}
	$username = $_SESSION["user"];
	if ($_SESSION["look"] != "") {
		$username = $_SESSION["look"];
	}

	exec(HESTIA_CMD . "v-list-user " . quoteshellarg($username) . " json", $output, $return_var);
	$data = json_decode(implode("", $output), true);
	unset($output, $return_var);
	$_SESSION["login_shell"] = $data[$username]["SHELL"];
	$_SESSION["role"] = $data[$username]["ROLE"];
	unset($data, $username);
}

if ($_SESSION["RELEASE_BRANCH"] == "release" && $_SESSION["DEBUG_MODE"] == "false") {
	define("JS_LATEST_UPDATE", "v=" . $_SESSION["VERSION"]);
} else {
	define("JS_LATEST_UPDATE", "r=" . time());
}

if (!defined("NO_AUTH_REQUIRED")) {
	if (empty($_SESSION["LAST_ACTIVITY"]) || empty($_SESSION["INACTIVE_SESSION_TIMEOUT"])) {
		destroy_sessions();
		header("Location: /login/");
	} elseif ($_SESSION["INACTIVE_SESSION_TIMEOUT"] * 60 + $_SESSION["LAST_ACTIVITY"] < time()) {
		$v_user = quoteshellarg($_SESSION["user"]);
		$v_session_id = quoteshellarg($_SESSION["token"]);
		exec(
			HESTIA_CMD . "v-log-user-logout " . $v_user . " " . $v_session_id,
			$output,
			$return_var,
		);
		destroy_sessions();
		header("Location: /login/");
		exit();
	} else {
		$_SESSION["LAST_ACTIVITY"] = time();
	}
}

function ipUsed() {
	[$http_host, $port] = explode(":", $_SERVER["HTTP_HOST"] . ":");
	if (filter_var($http_host, FILTER_VALIDATE_IP)) {
		return true;
	} else {
		return false;
	}
}

if (isset($_SESSION["user"])) {
	$user = quoteshellarg($_SESSION["user"]);
	$user_plain = htmlentities($_SESSION["user"]);
}

if (isset($_SESSION["look"]) && $_SESSION["look"] != "" && $_SESSION["userContext"] === "admin") {
	$user = quoteshellarg($_SESSION["look"]);
	$user_plain = htmlentities($_SESSION["look"]);
}
if (empty($user_plain)) {
	$user_plain = "";
}
if (empty($_SESSION["look"])) {
	$_SESSION["look"] = "";
}

global $hst_plugins, $hst_filters, $hst_actions;
$hst_plugins = isset($hst_plugins) ? $hst_plugins : [];
$hst_filters = isset($hst_filters) ? $hst_filters : [];
$hst_actions = isset($hst_actions) ? $hst_actions : [];

require_once dirname(__FILE__) . "/i18n.php";

function check_error($return_var) {
	if ($return_var > 0) {
		header("Location: /error/");
		exit();
	}
}

function check_return_code($return_var, $output) {
	if ($return_var != 0) {
		$error = implode("<br>", $output);
		if (empty($error)) {
			$error = sprintf(_("Error code: %s"), $return_var);
		}
		$_SESSION["error_msg"] = $error;
	}
}
function check_return_code_redirect($return_var, $output, $location) {
	if ($return_var != 0) {
		$error = implode("<br>", $output);
		if (empty($error)) {
			$error = sprintf(_("Error code: %s"), $return_var);
		}
		$_SESSION["error_msg"] = $error;
		header("Location:" . $location);
	}
}

function render_page($user, $TAB, $page) {
	$__template_dir = dirname(__DIR__) . "/templates/";

	// Extract global variables
	// I think those variables should be passed via arguments
	extract($GLOBALS, EXTR_SKIP);

	// Header
	include $__template_dir . "header.php";

	// Panel
	$panel = top_panel(empty($_SESSION["look"]) ? $_SESSION["user"] : $_SESSION["look"], $TAB);

	// Policies controller
	@include_once dirname(__DIR__) . "/inc/policies.php";

	// Body
	include $__template_dir . "pages/" . $page . ".php";

	// Footer
	include $__template_dir . "footer.php";
}

// Match $_SESSION['token'] against $_GET['token'] or $_POST['token']
// Usage: verify_csrf($_POST) or verify_csrf($_GET); Use verify_csrf($_POST,true) to return on failure instead of redirect
function verify_csrf($method, $return = false) {
	if (
		$method["token"] !== $_SESSION["token"] ||
		empty($method["token"]) ||
		empty($_SESSION["token"])
	) {
		if ($return === true) {
			return false;
		} else {
			header("Location: /login/");
			die();
		}
	} else {
		return true;
	}
}

function show_alert_message($data) {
	$msgIcon = "";
	$msgText = "";
	$msgClass = "";
	if (!empty($data["error_msg"])) {
		$msgIcon = "fa-circle-exclamation";
		$msgText = htmlentities($data["error_msg"]);
		$msgClass = "inline-alert-danger";
	} elseif (!empty($data["ok_msg"])) {
		$msgIcon = "fa-circle-check";
		$msgText = $data["ok_msg"];
		$msgClass = "inline-alert-success";
	}

	if (!empty($msgText)) {
		printf(
			'<div class="inline-alert %s u-mb20" role="alert"><i class="fas %s"></i><p>%s</p></div>',
			$msgClass,
			$msgIcon,
			$msgText,
		);
	}
}

function top_panel($user, $TAB) {
	$command = HESTIA_CMD . "v-list-user " . $user . " 'json'";
	exec($command, $output, $return_var);
	if ($return_var > 0) {
		destroy_sessions();
		$_SESSION["error_msg"] = _("You are logged out, please log in again.");
		header("Location: /login/");
		exit();
	}
	$panel = json_decode(implode("", $output), true);
	unset($output);

	// Log out active sessions for suspended users
	if ($panel[$user]["SUSPENDED"] === "yes" && $_SESSION["POLICY_USER_VIEW_SUSPENDED"] !== "yes") {
		if (empty($_SESSION["look"])) {
			destroy_sessions();
			$_SESSION["error_msg"] = _("You are logged out, please log in again.");
			header("Location: /login/");
		}
	}

	// Reset user permissions if changed while logged in
	if ($panel[$user]["ROLE"] !== $_SESSION["userContext"] && !isset($_SESSION["look"])) {
		unset($_SESSION["userContext"]);
		$_SESSION["userContext"] = $panel[$user]["ROLE"];
	}

	// Load user's selected theme and do not change it when impersonting user
	if (isset($panel[$user]["THEME"]) && !isset($_SESSION["look"])) {
		$_SESSION["userTheme"] = $panel[$user]["THEME"];
	}

	// Unset userTheme override variable if POLICY_USER_CHANGE_THEME is set to no
	if ($_SESSION["POLICY_USER_CHANGE_THEME"] === "no") {
		unset($_SESSION["userTheme"]);
	}

	// Set preferred sort order
	if (!isset($_SESSION["look"])) {
		$_SESSION["userSortOrder"] = $panel[$user]["PREF_UI_SORT"];
	}

	// Set home location URLs
	if ($_SESSION["userContext"] === "admin" && empty($_SESSION["look"])) {
		// Display users list for administrators unless they are impersonating a user account
		$home_url = "/list/user/";
	} else {
		// Set home location URL based on available package features from account
		if ($panel[$user]["WEB_DOMAINS"] != "0") {
			$home_url = "/list/web/";
		} elseif ($panel[$user]["DNS_DOMAINS"] != "0") {
			$home_url = "/list/dns/";
		} elseif ($panel[$user]["MAIL_DOMAINS"] != "0") {
			$home_url = "/list/mail/";
		} elseif ($panel[$user]["DATABASES"] != "0") {
			$home_url = "/list/db/";
		} elseif ($panel[$user]["CRON_JOBS"] != "0") {
			$home_url = "/list/cron/";
		} elseif ($panel[$user]["BACKUPS"] != "0") {
			$home_url = "/list/backups/";
		}
	}

	include dirname(__FILE__) . "/../templates/includes/panel.php";
	return $panel;
}

function translate_date($date) {
	$date = new DateTime($date);
	return $date->format("d") . " " . _($date->format("M")) . " " . $date->format("Y");
}

function humanize_time($usage) {
	if ($usage > 60) {
		$usage = $usage / 60;
		if ($usage > 24) {
			$usage = $usage / 24;
			$usage = number_format($usage);
			return sprintf(ngettext("%d day", "%d days", $usage), $usage);
		} else {
			$usage = round($usage);
			return sprintf(ngettext("%d hour", "%d hours", $usage), $usage);
		}
	} else {
		$usage = round($usage);
		return sprintf(ngettext("%d minute", "%d minutes", $usage), $usage);
	}
}

function humanize_usage_size($usage, $round = 2) {
	if ($usage == "unlimited") {
		return "∞";
	}
	if ($usage < 1) {
		$usage = "0";
	}
	$display_usage = $usage;
	if ($usage > 1024) {
		$usage = $usage / 1024;
		if ($usage > 1024) {
			$usage = $usage / 1024;
			if ($usage > 1024) {
				$usage = $usage / 1024;
				$display_usage = number_format($usage, $round);
			} else {
				if ($usage > 999) {
					$usage = $usage / 1024;
				}
				$display_usage = number_format($usage, $round);
			}
		} else {
			if ($usage > 999) {
				$usage = $usage / 1024;
			}
			$display_usage = number_format($usage, $round);
		}
	} else {
		if ($usage > 999) {
			$usage = $usage / 1024;
		}
		$display_usage = number_format($usage, $round);
	}
	return $display_usage;
}

function humanize_usage_measure($usage) {
	if ($usage == "unlimited") {
		return;
	}

	$measure = "kb";
	if ($usage > 1024) {
		$usage = $usage / 1024;
		if ($usage > 1024) {
			$usage = $usage / 1024;
			$measure = $usage < 1024 ? "tb" : "pb";
			if ($usage > 999) {
				$usage = $usage / 1024;
				$measure = "pb";
			}
		} else {
			$measure = $usage < 1024 ? "gb" : "tb";
			if ($usage > 999) {
				$usage = $usage / 1024;
				$measure = "tb";
			}
		}
	} else {
		$measure = $usage < 1024 ? "mb" : "gb";
		if ($usage > 999) {
			$measure = "gb";
		}
	}
	return $measure;
}

function get_percentage($used, $total) {
	if ($total = "unlimited") {
		//return 0 if unlimited
		return 0;
	}
	if (!isset($total)) {
		$total = 0;
	}
	if (!isset($used)) {
		$used = 0;
	}
	if ($total == 0) {
		$percent = 0;
	} else {
		$percent = $used / $total;
		$percent = $percent * 100;
		$percent = number_format($percent, 0, "", "");
		if ($percent < 0) {
			$percent = 0;
		} elseif ($percent > 100) {
			$percent = 100;
		}
	}
	return $percent;
}

function send_email($to, $subject, $mailtext, $from, $from_name, $to_name = "") {
	$mail = new PHPMailer();

	if (isset($_SESSION["USE_SERVER_SMTP"]) && $_SESSION["USE_SERVER_SMTP"] == "true") {
		if (!empty($_SESSION["SERVER_SMTP_ADDR"]) && $_SESSION["SERVER_SMTP_ADDR"] != "") {
			if (filter_var($_SESSION["SERVER_SMTP_ADDR"], FILTER_VALIDATE_EMAIL)) {
				$from = $_SESSION["SERVER_SMTP_ADDR"];
			}
		}

		$mail->IsSMTP();
		$mail->Mailer = "smtp";
		$mail->SMTPDebug = 0;
		$mail->SMTPAuth = true;
		$mail->SMTPSecure = $_SESSION["SERVER_SMTP_SECURITY"];
		$mail->Port = $_SESSION["SERVER_SMTP_PORT"];
		$mail->Host = $_SESSION["SERVER_SMTP_HOST"];
		$mail->Username = $_SESSION["SERVER_SMTP_USER"];
		$mail->Password = $_SESSION["SERVER_SMTP_PASSWD"];
	}

	$mail->IsHTML(true);
	$mail->ClearReplyTos();
	if (empty($to_name)) {
		$mail->AddAddress($to);
	} else {
		$mail->AddAddress($to, $to_name);
	}
	$mail->SetFrom($from, $from_name);

	$mail->CharSet = "utf-8";
	$mail->Subject = $subject;
	$content = $mailtext;
	$content = nl2br($content);
	$mail->MsgHTML($content);
	$mail->Send();
}

function list_timezones() {
	foreach (
		["AKST", "AKDT", "PST", "PDT", "MST", "MDT", "CST", "CDT", "EST", "EDT", "AST", "ADT"]
		as $timezone
	) {
		$tz = new DateTimeZone($timezone);
		$timezone_offsets[$timezone] = $tz->getOffset(new DateTime());
	}

	foreach (DateTimeZone::listIdentifiers() as $timezone) {
		$tz = new DateTimeZone($timezone);
		$timezone_offsets[$timezone] = $tz->getOffset(new DateTime());
	}

	foreach ($timezone_offsets as $timezone => $offset) {
		$offset_prefix = $offset < 0 ? "-" : "+";
		$offset_formatted = gmdate("H:i", abs($offset));
		$pretty_offset = "UTC{$offset_prefix}{$offset_formatted}";
		$c = new DateTime(gmdate("Y-M-d H:i:s"), new DateTimeZone("UTC"));
		$c->setTimezone(new DateTimeZone($timezone));
		$current_time = $c->format("H:i:s");
		$timezone_list[$timezone] = "$timezone [ $current_time ] {$pretty_offset}";
		#$timezone_list[$timezone] = "$timezone ${pretty_offset}";
	}
	return $timezone_list;
}

/**
 * A function that tells is it MySQL installed on the system, or it is MariaDB.
 *
 * Explanation:
 * $_SESSION['DB_SYSTEM'] has 'mysql' value even if MariaDB is installed, so you can't figure out is it really MySQL or it's MariaDB.
 * So, this function will make it clear.
 *
 * If MySQL is installed, function will return 'mysql' as a string.
 * If MariaDB is installed, function will return 'mariadb' as a string.
 *
 * Hint: if you want to check if PostgreSQL is installed - check value of $_SESSION['DB_SYSTEM']
 *
 * @return string
 */
function is_it_mysql_or_mariadb() {
	exec(HESTIA_CMD . "v-list-sys-services json", $output, $return_var);
	$data = json_decode(implode("", $output), true);
	unset($output);
	$mysqltype = "mysql";
	if (isset($data["mariadb"])) {
		$mysqltype = "mariadb";
	}
	return $mysqltype;
}

function load_hestia_config() {
	// Check system configuration
	exec(HESTIA_CMD . "v-list-sys-config json", $output, $return_var);
	$data = json_decode(implode("", $output), true);
	$sys_arr = $data["config"];
	foreach ($sys_arr as $key => $value) {
		$_SESSION[$key] = $value;
	}
}

/**
 * Returns the list of all web domains from all users grouped by Backend Template used and owner
 *
 * @return array
 */
function backendtpl_with_webdomains() {
	exec(HESTIA_CMD . "v-list-users json", $output, $return_var);
	$users = json_decode(implode("", $output), true);
	unset($output);

	$backend_list = [];
	foreach ($users as $user => $user_details) {
		exec(
			HESTIA_CMD . "v-list-web-domains " . quoteshellarg($user) . " json",
			$output,
			$return_var,
		);
		$domains = json_decode(implode("", $output), true);
		unset($output);
		foreach ($domains as $domain => $domain_details) {
			if (!empty($domain_details["BACKEND"])) {
				$backend = $domain_details["BACKEND"];
				$backend_list[$backend][$user][] = $domain;
			}
		}
	}
	return $backend_list;
}
/**
 * Check if password is valid
 *
 * @return int; 1 / 0
 */
function validate_password($password) {
	return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(.){8,}$/', $password);
}

function unset_alerts() {
	if (!empty($_SESSION["unset_alerts"])) {
		if (!empty($_SESSION["error_msg"])) {
			unset($_SESSION["error_msg"]);
		}
		if (!empty($_SESSION["ok_msg"])) {
			unset($_SESSION["ok_msg"]);
		}
		unset($_SESSION["unset_alerts"]);
	}
}
register_shutdown_function("unset_alerts");

/**
 * Renders a page with the possibility to use templates outside the hestia default templates directory.
 *
 * @param string $template HTML or full path to the template file.
 * @param array $__args <p>
 *  Arguments.
 *
 *  * string tab                - Tab name to top_panel function. If not defined use global $TAB.
 *  * string|false template_dir - (Default: /templates/pages/) Path to templates directory from web root. If false, print "$template" value as HTML.
 *
 *  The rest of the arguments will be extracted
 * </p>
 */
function hst_render($template, $__args = []) {
	$user = $GLOBALS["user"];
	$user_plain = $GLOBALS["user_plain"];
	$TAB = !empty($__args["tab"]) ? $__args["tab"] : $GLOBALS["TAB"];
	$__html = isset($__args["template_dir"]) && $__args["template_dir"] === false;
	$__template_dir = null;

	if (!$__html) {
		// Path to custom template directory from web root
		$__args["template_dir"] = !empty($__args["template_dir"])
			? $__args["template_dir"]
			: "/templates/pages/";
		$__template_dir = dirname(__DIR__) . "/" . trim($__args["template_dir"], "/") . "/";

		// Add .php extension if not exists
		if (!preg_match("/\.(html|php)$/", $template)) {
			$template .= ".php";
		}
	}

	// Remove the arguments from the method and extract the rest
	if (isset($__args["template_dir"])) {
		unset($__args["template_dir"]);
	}
	if (isset($__args["tab"])) {
		unset($__args["tab"]);
	}

	if (is_array($__args) && count($__args) > 0) {
		extract($__args, EXTR_SKIP);
	}

	// Header
	include dirname(__DIR__) . "/templates/header.php";

	// Panel
	$panel = top_panel(empty($_SESSION["look"]) ? $_SESSION["user"] : $_SESSION["look"], $TAB);

	// Policies controller
	@include_once dirname(__DIR__) . "/inc/policies.php";

	// Body
	if ($__html) {
		// Show HTML
		echo $template;
	} elseif (file_exists($__template_dir . ltrim($template))) {
		// Include template
		@include_once $__template_dir . ltrim($template);
	} else {
		// Show error
		echo "<div class=\"container\">\n";
		echo "<h1>Template not found</h1>\n";
		echo "<p>Template <strong>$template</strong> not found in <strong>$__template_dir</strong></p>\n";
		echo "</div>\n";
	}

	// Footer
	include dirname(__DIR__) . "/templates/footer.php";
}

/**
 * Render an HTML string.
 *
 * @param string $template
 * @param array $__args
 * @return void
 */
function hst_render_html($template, $__args = []) {
	$__args["template_dir"] = false;
	hst_render($template, $__args);
}

/**
 * Render plugin page.
 *
 * @param string $plugin_name Plugin name
 * @param string $template
 * @param array $__args
 * @return void
 */
function hst_render_plugin_page($plugin_name, $template, $__args = []) {
	$__args["template_dir"] = "/plugin/" . trim($plugin_name, "/") . "/templates/";
	hst_render($template, $__args);
}

/**
 * Layout to show an output from cmd
 *
 * @param string $output
 * @param string|null $title
 * @param string|null $backbutton
 */
function hst_render_cmd_output($output, $title = null, $backbutton = null) {
	$backbutton = !empty($backbutton) ? $backbutton : "/list/user/";

	$tpl =
		"<div class=\"toolbar\">
        <div class=\"toolbar-inner\">
            <div class=\"toolbar-buttons\">
                <a class=\"button button-secondary button-back js-button-back\" href=\"" .
		$backbutton .
		"\">
                    <i class=\"fas fa-arrow-left icon-blue\"></i>" .
		_("Back") .
		"
                </a>
            </div>
        </div>
    </div>";

	$tpl .= "<div class=\"container command-output\">\n";
	if (!empty($title)) {
		$tpl .= "<h1 class=\"form-title\">$title</h1>\n";
	}

	$tpl .= "<div class=\"output_content\">\n";
	$tpl .= "<pre>$output</pre>\n";
	$tpl .= "</div>\n";

	$tpl .= "</div>";

	hst_render_html($tpl);
}

/**
 * Hook to modify a filter
 *
 * @param string $tag
 * @param callable $callback
 * @param int $priority
 */
function hst_add_filter($tag, $callback, $priority = null) {
	global $hst_filters;

	if (!is_string($tag)) {
		return;
	}
	$priority = is_int($priority) && $priority > 0 ? $priority : 10;

	if (!isset($hst_filters[$tag])) {
		$hst_filters[$tag] = [];
	}
	if (!isset($hst_filters[$tag][$priority])) {
		$hst_filters[$tag][$priority] = [];
	}

	if (is_callable($callback)) {
		$hst_filters[$tag][$priority][] = $callback;
	}
}

/**
 * Filter a value
 *
 * @param string $tag Name of the filter
 * @param mixed ...$init_value Value to filter and optional args
 * @return mixed
 */
function hst_apply_filters($tag, ...$init_value) {
	global $hst_filters;

	if (isset($hst_filters[$tag])) {
		$tag_filters = $hst_filters[$tag];
		ksort($tag_filters);

		foreach ($tag_filters as $priority => $list) {
			foreach ($list as $i => $callback) {
				$init_value[0] = call_user_func_array($callback, $init_value);
			}
		}
	}

	return $init_value[0];
}

/**
 * Add action to be called in specific point during execution
 *
 * @param string $tag
 * @param callable $callback
 * @param int $priority
 */
function hst_add_action($tag, $callback, $priority = null) {
	global $hst_actions;

	if (!is_string($tag)) {
		return;
	}
	$priority = is_int($priority) && $priority > 0 ? $priority : 10;

	if (!isset($hst_actions[$tag])) {
		$hst_actions[$tag] = [];
	}
	if (!isset($hst_actions[$tag][$priority])) {
		$hst_actions[$tag][$priority] = [];
	}

	if (is_callable($callback)) {
		$hst_actions[$tag][$priority][] = $callback;
	}
}

/**
 * Execute an action
 *
 * @param string $tag
 * @param mixed ...$args
 */
function hst_do_action($tag, ...$args) {
	global $hst_actions;

	$args = is_array($args) ? $args : [];

	if (isset($hst_actions[$tag])) {
		$tag_actions = $hst_actions[$tag];
		ksort($tag_actions);

		foreach ($tag_actions as $priority => $list) {
			foreach ($list as $i => $callback) {
				call_user_func_array($callback, $args);
			}
		}
	}
}

/**
 * Add CSS on head
 *
 * @param string $link
 * @param int $priority
 */
function hst_add_css($link, $priority = 10) {
	if (!is_string($link)) {
		return;
	}

	hst_add_filter(
		"css",
		function ($list) use ($link) {
			if (is_array($list)) {
				$list[] = $link;
			}
			return $list;
		},
		$priority,
	);
}

/**
 * Add JS on head
 *
 * @param string $link
 * @param int $priority
 */
function hst_add_js($link, $priority = 10) {
	if (!is_string($link)) {
		return;
	}

	hst_add_filter(
		"js",
		function ($list) use ($link) {
			if (is_array($list)) {
				$list[] = $link;
			}
			return $list;
		},
		$priority,
	);
}

/**
 * Add item on header menu
 *
 * @param string $name Name to display.
 * @param string|array{link: string, external?: bool} $link
 * @param string $icon
 * @param string|string[] $page_tab Used to marquee menu as active if the link is from a hestia page. Name will be used if not defined.
 * @param int $priority
 */
function hst_add_header_menu($name, $link, $icon = null, $page_tab = null, $priority = 10) {
	if (!is_string($name) || empty($name)) {
		return;
	}

	$item = [];

	$item["name"] = $name;
	if (is_string($link)) {
		$item["link"] = $link;
		$item["external"] = false;
	} elseif (is_array($link)) {
		$item["link"] = $link["link"] ?? null;
		$item["external"] = $link["external"] ?? false;
	}

	if (is_string($icon) && !empty($icon)) {
		$item["icon"] = $icon;
	}

	if (!empty($page_tab)) {
		if (is_array($page_tab)) {
			$item["page_tab"] = array_map("strtoupper", $page_tab);
		} elseif (is_string($page_tab)) {
			$item["page_tab"] = strtoupper($page_tab);
		}
	}

	hst_add_filter(
		"header_menu",
		function ($items) use ($item) {
			$items[] = $item;
			return $items;
		},
		$priority,
	);
}

/**
 * Add item on left menu(l-stat)
 *
 * Not displayed in default hestia theme.
 *
 * @param string $name Name to display.
 * @param string $link
 * @param array $sub_items <p>
 *  Can be used to add a submenu or display an information.
 *  * name  - Name to display
 *  * value - (optional)
 *  * link  - (optional)
 * </p>
 * @param string $icon
 * @param string|string[] $page_tab Name will be used if not defined.
 * @param int $priority
 */
function hst_add_menu(
	$name,
	$link,
	$sub_items = [],
	$icon = null,
	$page_tab = null,
	$priority = 10,
) {
	if (!is_string($name) || empty($name)) {
		return;
	}
	if (!is_string($link) || empty($link)) {
		return;
	}

	$item = [];

	$item["name"] = $name;
	$item["link"] = $link;

	if (is_string($icon) && !empty($icon)) {
		$item["icon"] = $icon;
	}

	if (!empty($page_tab)) {
		if (is_array($page_tab)) {
			$item["page_tab"] = array_map("strtoupper", $page_tab);
		} elseif (is_string($page_tab)) {
			$item["page_tab"] = strtoupper($page_tab);
		}
	}

	if (is_array($sub_items) && !empty($sub_items)) {
		$item["sub_items"] = $sub_items;
	}

	hst_add_filter(
		"menu",
		function ($items) use ($item) {
			$items[] = $item;
			return $items;
		},
		$priority,
	);
}

/**
 * Return the place where the script is running.
 *
 * @return string
 */
function hst_current_panel() {
	if (
		isset($_SESSION["user"]) &&
		($_SESSION["user"] != "admin" || (isset($_SESSION["look"]) && !empty($_SESSION["look"])))
	) {
		return "user_panel";
	} elseif (isset($_SESSION["user"]) && $_SESSION["user"] == "admin") {
		return "admin_panel";
	} else {
		return "external";
	}
}

/**
 * Exec command
 *
 * @param string $cmd
 * @param string[]|string ...$args
 * @return string|array
 * @throws \Exception
 */
function hst_exec(string $cmd, ...$args) {
	if (empty($cmd)) {
		throw new \Exception("Command not defined");
	} elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $cmd)) {
		throw new \Exception("Invalid command: " . htmlentities($cmd));
	}

	if (file_exists(HESTIA_DIR_BIN . $cmd)) {
		$cmd = HESTIA_CMD . $cmd;
	}

	$final_args = [];
	foreach ($args as $arg) {
		if (!empty($arg) && (is_string($arg) || is_numeric($arg))) {
			$final_args[] = quoteshellarg($arg);
		}
	}

	$cmd = escapeshellcmd($cmd);
	$result = shell_exec($cmd . " " . implode(" ", $final_args));
	if (trim(end($final_args), "\"'") == "json") {
		return json_decode($result, true);
	}

	return $result;
}

/**
 * Checks if it's a plugin page
 *
 * @param string $plugin_name
 * @return bool
 */
function hst_is_plugin_page($plugin_name) {
	return preg_match("/^\/plugin\/$plugin_name($|\/*)/", $_SERVER["REQUEST_URI"]) != false;
}

/**
 * Include each plugin functions in an isolated scope
 *
 * @param $plugin_name
 * @return void
 */
function load_plugin($plugin_name) {
	if (file_exists(HESTIA_DIR_WEB . "plugin/$plugin_name/functions.php")) {
		try {
			include_once HESTIA_DIR_WEB . "plugin/$plugin_name/functions.php";
		} catch (\Exception $e) {
			hst_add_history_log("$plugin_name - {$e->getMessage()}", "Plugin", "Error");
		}
	}
}

/**
 * Get plugin data
 *
 * @param bool $force_reload Force reload plugins data
 * @return array
 */
function get_plugins(bool $force_reload = false) {
	global $hst_plugins;

	if ($force_reload || empty($hst_plugins)) {
		$plugins = hst_exec("v-list-plugins", "json");
		if (is_array($plugins)) {
			$plugins = array_map(function ($plugin) {
				$plugin["plugin_dir"] = HESTIA_DIR . "plugins/{$plugin["name"]}/";

				if (is_dir(HESTIA_DIR_WEB . "plugin/{$plugin["name"]}/")) {
					$plugin["has_web_ui"] = true;
					$plugin["web_uri"] = "/plugin/{$plugin["name"]}/";
					$plugin["web_dir"] = HESTIA_DIR_WEB . "plugin/{$plugin["name"]}/";
				} else {
					$plugin["has_web_ui"] = false;
				}

				return $plugin;
			}, $plugins);

			// Save cache
			$hst_plugins = $plugins;
		}
	}

	return $hst_plugins;
}

/**
 * Get plugin data
 *
 * @param string $plugin_name
 * @param bool $force_reload Force reload plugins data
 * @return array|null
 */
function get_plugin_data(string $plugin_name, bool $force_reload = false) {
	$plugins = get_plugins($force_reload);

	// Search for plugin
	foreach ($plugins as $plugin) {
		if ($plugin["name"] == $plugin_name) {
			return $plugin;
		}
	}

	// Plugin not found
	return null;
}

// Show items from filter "header_menu"
hst_add_action("render_header_menu", function () {
	global $TAB;

	$list_header_menu = hst_apply_filters("header_menu", []);
	foreach ($list_header_menu as $item) {
		if (is_array($item)) {

			if (empty($item["name"]) || !is_string($item["name"])) {
				continue;
			}

			$name = $item["name"];
			$link =
				!empty($item["link"]) && is_string($item["link"])
					? $item["link"]
					: "javascript:void(0);";
			$external = ($item["external"] ?? false) === true;

			$classes = "top-bar-menu-item";
			$classes .=
				!empty($item["classes"]) && is_string($item["classes"])
					? " " . $item["classes"]
					: "";
			$is_active =
				!empty($item["page_tab"]) &&
				((is_string($item["page_tab"]) && $TAB == $item["page_tab"]) ||
					(is_array($item["page_tab"]) && in_array($TAB, $item["page_tab"])))
					? "active"
					: "";

			$icon = !empty($item["icon"]) && is_string($item["icon"]) ? $item["icon"] : "ghost";
			?>
			<li class="<?= $classes ?>">
				<a title="<?= $name ?>" <?php if ($external) {
	echo 'target="_blank" rel="noopener"';
} ?>
				   class="top-bar-menu-link <?= $is_active ?>" href="<?= $link ?>">
					<i class="fas fa-<?= $icon ?>"></i>
					<span class="top-bar-menu-link-label u-hide-desktop"><?= $name ?></span>
				</a>
			</li>
			<?php
		}
	}
});

// Show items from filter "menu"
hst_add_action("render_menu", function () {
	global $TAB;

	$list_menu = hst_apply_filters("menu", []);

	foreach ($list_menu as $item) {
		if (is_array($item)) {

			if (!isset($item["name"]) || !is_string($item["name"]) || empty($item["name"])) {
				continue;
			}

			$name = $item["name"];
			$link =
				isset($item["link"]) && is_string($item["link"]) && !empty($item["link"])
					? $item["link"]
					: "javascript:void(0);";

			$classes = "main-menu-item";
			$classes .=
				!empty($item["classes"]) && is_string($item["classes"])
					? " " . $item["classes"]
					: "";

			$is_active =
				!empty($item["page_tab"]) &&
				((is_string($item["page_tab"]) && $TAB == $item["page_tab"]) ||
					(is_array($item["page_tab"]) && in_array($TAB, $item["page_tab"])))
					? "active"
					: "";

			$icon = !empty($item["icon"]) && is_string($item["icon"]) ? $item["icon"] : "ghost";
			?>
		<li class="<?= $classes ?>">
		<a href="<?= $link ?>" class="main-menu-item-link <?= $is_active ?>">
			<p class="main-menu-item-label"><?= $name ?><i class="fas fa-<?= $icon ?>"></i></p>
			<?php
   if (isset($item["sub_items"]) && is_array($item["sub_items"])) {
   	echo "<ul class=\"main-menu-stats\">";

   	foreach ($item["sub_items"] as $sub_item) {
   		if (
   			isset($sub_item["name"]) &&
   			is_string($sub_item["name"]) &&
   			!empty($sub_item["name"])
   		) {
   			$sub_item_value = isset($sub_item["value"])
   				? ": <span>{$sub_item["value"]}</span>"
   				: "";

   			if (
   				isset($sub_item["link"]) &&
   				is_string($sub_item["link"]) &&
   				!empty($sub_item["link"])
   			) {
   				echo "<li><a href=\"{$sub_item["link"]}\">" .
   					$sub_item["name"] .
   					"$sub_item_value</a></li>";
   			} else {
   				echo "<li>" . $sub_item["name"] . "$sub_item_value</li>";
   			}
   		} elseif (is_string($sub_item) && !empty($sub_item)) {
   			echo "<li>" . $sub_item . "</li>";
   		}
   	}

   	echo "</ul>";
   }

   echo "</a></li>\n";

		}
	}
});

// Run before panels "panel.php"
hst_add_action(
	"panel_init",
	function ($user, $panel) {
		/*
		 * Add items to header menu
		 */
		// File Manager
		if (
			!empty($_SESSION["FILE_MANAGER"]) &&
			$_SESSION["FILE_MANAGER"] == "true" &&
			!(
				$_SESSION["userContext"] === "admin" &&
				(isset($_SESSION["look"]) &&
					$_SESSION["look"] === "admin" &&
					$_SESSION["POLICY_SYSTEM_PROTECTED_ADMIN"] == "yes")
			)
		) {
			hst_add_header_menu(_("File manager"), "/fm/", "folder-open", "FM", 5);
		}

		// Plugin Manager
		//if ($_SESSION["userContext"] === "admin" && $_SESSION["user"] === "admin" && empty($_SESSION["look"])) {
		//	hst_add_header_menu(_('Plugins'), '/list/plugin/', 'puzzle-piece', 'PLUGINS', 5);
		//}

		// Server Settings
		if (
			(($_SESSION["userContext"] === "admin" &&
				$_SESSION["POLICY_SYSTEM_HIDE_SERVICES"] !== "yes") ||
				$_SESSION["user"] === "admin") &&
			!($_SESSION["userContext"] === "admin" && !empty($_SESSION["look"]))
		) {
			hst_add_header_menu(
				_("Server settings"),
				"/list/server/",
				"gear",
				["SERVER", "IP", "RRD", "FIREWALL"],
				5,
			);
		}

		// Edit User
		if (
			$_SESSION["userContext"] === "admin" &&
			(isset($_SESSION["look"]) && $user == "admin")
		) {
			hst_add_header_menu(_("Logs"), "/list/log/", "clock-rotate-left", "LOG", 5);
		} elseif ($panel[$user]["SUSPENDED"] === "no") {
			hst_add_header_menu(
				htmlspecialchars($user) . " (" . htmlspecialchars($panel[$user]["NAME"]) . ")",
				"/edit/user/?user=" . $user . "&token=" . $_SESSION["token"],
				"circle-user",
				"USER",
				5,
			);
		}

		// Statistics
		hst_add_header_menu(_("Statistics"), "/list/stats/", "chart-line", "STATS", 5);

		// Help / Documentation
		if (isset($_SESSION["HIDE_DOCS"]) && $_SESSION["HIDE_DOCS"] != "yes") {
			$doc_link = [
				"link",
				"https://hestiacp.com/docs/server-administration/troubleshooting.html",
				"external" => true,
			];
			hst_add_header_menu(_("Help"), $doc_link, "circle-question", null, 5);
		}

		// Logout
		$logout_icon = !empty($_SESSION["look"]) ? "circle-up" : "right-from-bracket";
		hst_add_header_menu(
			_("Log out") . " ($user)",
			"/logout/?token=" . $_SESSION["token"],
			$logout_icon,
			null,
			15,
		);

		/*
		 * Add items to main menu
		 */
		// Users tab
		if ($_SESSION["userContext"] == "admin" && empty($_SESSION["look"])) {
			if ($_SESSION["user"] !== "admin" && $_SESSION["POLICY_SYSTEM_HIDE_ADMIN"] === "yes") {
				$user_count = $panel[$user]["U_USERS"] - 1;
			} else {
				$user_count = $panel[$user]["U_USERS"];
			}

			$sub_items = [
				["name" => _("Users"), "value" => htmlspecialchars($user_count)],
				["name" => _("Suspended"), "value" => $panel[$user]["SUSPENDED_USERS"]],
			];
			hst_add_menu(_("USER"), "/list/user/", $sub_items, "users", "USER", 5);
		}

		// Web tab
		if (!empty($_SESSION["WEB_SYSTEM"]) && $panel[$user]["WEB_DOMAINS"] != "0") {
			$domains_count =
				$panel[$user]["U_WEB_DOMAINS"] .
				" / " .
				($panel[$user]["WEB_DOMAINS"] == "unlimited"
					? "<b>∞</b>"
					: $panel[$user]["WEB_DOMAINS"] . " (" . $panel[$user]["SUSPENDED_WEB"] . ")");
			$domain_alias_count =
				$panel[$user]["U_WEB_ALIASES"] .
				" / " .
				($panel[$user]["WEB_ALIASES"] == "unlimited" ||
				$panel[$user]["WEB_DOMAINS"] == "unlimited"
					? "<b>∞</b>"
					: $panel[$user]["WEB_ALIASES"] * $panel[$user]["WEB_DOMAINS"]);
			$sub_items = [
				["name" => _("Domains"), "value" => $domains_count],
				["name" => _("Aliases"), "value" => $domain_alias_count],
			];

			hst_add_menu(_("WEB"), "/list/web/", $sub_items, "earth-americas", "WEB", 5);
		}

		// DNS tab
		if (!empty($_SESSION["DNS_SYSTEM"]) && $panel[$user]["DNS_DOMAINS"] != "0") {
			$dns_count =
				$panel[$user]["U_DNS_DOMAINS"] .
				" / " .
				($panel[$user]["DNS_DOMAINS"] == "unlimited"
					? "<b>∞</b>"
					: $panel[$user]["DNS_DOMAINS"] . " (" . $panel[$user]["SUSPENDED_DNS"] . ")");
			$dns_records_count =
				$panel[$user]["U_DNS_RECORDS"] .
				" / " .
				($panel[$user]["DNS_RECORDS"] == "unlimited" ||
				$panel[$user]["DNS_DOMAINS"] == "unlimited"
					? "<b>∞</b>"
					: $panel[$user]["DNS_RECORDS"] * $panel[$user]["DNS_DOMAINS"]);
			$sub_items = [
				["name" => _("Zones"), "value" => $dns_count],
				["name" => _("Records"), "value" => $dns_records_count],
			];

			hst_add_menu(_("DNS"), "/list/dns/", $sub_items, "book-atlas", "DNS", 5);
		}

		// Mail tab
		if (!empty($_SESSION["MAIL_SYSTEM"]) && $panel[$user]["MAIL_DOMAINS"] != "0") {
			$mail_count =
				$panel[$user]["U_MAIL_DOMAINS"] .
				" / " .
				($panel[$user]["MAIL_DOMAINS"] == "unlimited"
					? "<b>∞</b>"
					: $panel[$user]["MAIL_DOMAINS"] . " (" . $panel[$user]["SUSPENDED_MAIL"] . ")");
			$mail_accounts_count =
				$panel[$user]["U_MAIL_ACCOUNTS"] .
				" / " .
				($panel[$user]["MAIL_ACCOUNTS"] == "unlimited" ||
				$panel[$user]["MAIL_DOMAINS"] == "unlimited"
					? "<b>∞</b>"
					: $panel[$user]["MAIL_ACCOUNTS"] * $panel[$user]["MAIL_DOMAINS"]);
			$sub_items = [
				["name" => _("Domains"), "value" => $mail_count],
				["name" => _("Accounts"), "value" => $mail_accounts_count],
			];

			hst_add_menu(_("MAIL"), "/list/mail/", $sub_items, "envelopes-bulk", "MAIL", 5);
		}

		// Databases tab
		if (!empty($_SESSION["DB_SYSTEM"]) && $panel[$user]["DATABASES"] != "0") {
			$db_count =
				$panel[$user]["U_DATABASES"] .
				" / " .
				($panel[$user]["DATABASES"] == "unlimited"
					? "<b>∞</b>"
					: $panel[$user]["DATABASES"] . " (" . $panel[$user]["SUSPENDED_DB"] . ")");
			$sub_items = [["name" => _("Databases"), "value" => $db_count]];

			hst_add_menu(_("DB"), "/list/db/", $sub_items, "database", "DB", 5);
		}

		// Cron tab
		if (!empty($_SESSION["CRON_SYSTEM"]) && $panel[$user]["CRON_JOBS"] != "0") {
			$cron_count =
				$panel[$user]["U_CRON_JOBS"] .
				" / " .
				($panel[$user]["CRON_JOBS"] == "unlimited"
					? "<b>∞</b>"
					: $panel[$user]["CRON_JOBS"] . " (" . $panel[$user]["SUSPENDED_CRON"] . ")");
			$sub_items = [["name" => _("Jobs"), "value" => $cron_count]];

			hst_add_menu(_("CRON"), "/list/cron/", $sub_items, "clock", "CRON", 5);
		}

		// Backups tab
		if ($panel[$user]["BACKUPS"] != "0") {
			$backups_count =
				$panel[$user]["U_BACKUPS"] .
				" / " .
				($panel[$user]["BACKUPS"] == "unlimited" ? "<b>∞</b>" : $panel[$user]["BACKUPS"]);
			$sub_items = [["name" => "Backups", "value" => $backups_count]];

			hst_add_menu(_("BACKUP"), "/list/backup/", $sub_items, "file-zipper", "BACKUP", 5);
		}

		// Plugins tab
		if ($_SESSION["userContext"] == "admin" && empty($_SESSION["look"])) {
			$plugins_list = hst_exec("v-list-plugins", "json");
			$plugins_enabled = array_filter($plugins_list, function ($plugin) {
				return ($plugin["enabled"] ?? false) === true;
			});

			hst_add_menu(
				_("PLUGINS"),
				"/list/plugin/",
				[
					["name" => _("Installed"), "value" => count($plugins_list)],
					["name" => _("Enabled"), "value" => count($plugins_enabled)],
				],
				"puzzle-piece",
				"PLUGINS",
				5,
			);
		}
	},
	5,
);

// Load plugins
$plugins = get_plugins();
foreach ($plugins as $plugin) {
	load_plugin($plugin["name"]);
}
