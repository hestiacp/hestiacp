<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
$TAB = "WEB";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/src/init.php";

// Check domain argument
if (empty($_GET["domain"])) {
	header("Location: /list/web/");
	exit();
}

// Edit as someone else?
if ($_SESSION["user"] == "admin" && !empty($_GET["user"])) {
	$user = quoteshellarg($_GET["user"]);
}

// Check if domain belongs to the user
$v_domain = $_GET["domain"];
exec(
	HESTIA_CMD . "v-list-web-domain " . $user . " " . quoteshellarg($v_domain) . " json",
	$output,
	$return_var,
);
if ($return_var > 0) {
	check_return_code_redirect($return_var, $output, "/list/web/");
}
unset($output);
exec(HESTIA_CMD . "v-list-sys-php json", $output, $return_var);
$php_versions = json_decode(implode("", $output), true);
unset($output);

// Check GET request
if (!empty($_GET["app"])) {
	$app = basename($_GET["app"]);

	$hestia = new \Hestia\System\HestiaApp();
	$app_installer_class = "\Hestia\WebApp\Installers\\" . $app . "\\" . $app . "Setup";
	if (class_exists($app_installer_class)) {
		try {
			$app_installer = new $app_installer_class($hestia);
			$info = $app_installer->getInfo();

			if (!$info->isInstallable()) {
				$_SESSION["error_msg"] = sprintf(
					_("Unable to install %s, required php version is not available."),
					$app,
				);
			} else {
				$installer = new \Hestia\WebApp\AppWizard($app_installer, $v_domain, $hestia);
				$GLOBALS["WebappInstaller"] = $installer;
			}
		} catch (Exception $e) {
			$_SESSION["error_msg"] = $e->getMessage();
			header("Location: /add/webapp/?domain=" . $v_domain);
			exit();
		}
	} else {
		$_SESSION["error_msg"] = sprintf(_("%s installer missing."), $app);
	}
}

// Check POST request
if (!empty($_POST["ok"]) && !empty($app)) {
	// Check token
	verify_csrf($_POST);

	if ($installer) {
		try {
			$installer->execute($_POST);

			$_SESSION["ok_msg"] = sprintf(_("%s installed successfully."), htmlspecialchars($app));

			header("Location: /add/webapp/?domain=" . $v_domain);
			exit();
		} catch (Exception $e) {
			$_SESSION["error_msg"] = $e->getMessage();
			header("Location: /add/webapp/?app=" . rawurlencode($app) . "&domain=" . $v_domain);
			exit();
		}
	}
}

if (!empty($installer)) {
	render_page($user, $TAB, "setup_webapp");
} else {
	$hestia = new \Hestia\System\HestiaApp();
	$appInstallers = glob(__DIR__ . "/../../src/app/WebApp/Installers/*/*.php");

	$v_web_apps = [];
	foreach ($appInstallers as $app) {
		$pattern = "/Installers\/([a-zA-Z][a-zA-Z0,9].*)\/([a-zA-Z][a-zA-Z0,9].*)Setup\.php/";
		$class = "\Hestia\WebApp\Installers\%s\%sSetup";

		if (preg_match($pattern, $app, $matches)) {
			$app_installer_class = sprintf($class, $matches[1], $matches[1]);

			$v_web_apps[] = (new $app_installer_class($hestia))->getInfo();
		}
	}

	render_page($user, $TAB, "list_webapps");
}

// Flush session messages
unset($_SESSION["error_msg"]);
unset($_SESSION["ok_msg"]);
