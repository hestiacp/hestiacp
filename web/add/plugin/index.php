<?php
use function Hestiacp\quoteshellarg\quoteshellarg;
ob_start();
$TAB = "PLUGINS";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check user
if ($_SESSION["userContext"] != "admin") {
	header("Location: /list/user/");
	exit();
}

// Check POST request
if (!empty($_POST["ok"])) {
	// Check token
	verify_csrf($_POST);

	// Check empty fields
	if (empty($_POST["v_plugin_url"])) {
		$errors[] = _("Plugin URL");
	}

	if (!empty($errors[0])) {
		foreach ($errors as $i => $error) {
			if ($i == 0) {
				$error_msg = $error;
			} else {
				$error_msg = $error_msg . ", " . $error;
			}
		}
		$_SESSION["error_msg"] = sprintf(_('Field "%s" can not be blank.'), $error_msg);
	}

	// Protect input
	$v_plugin_url = quoteshellarg($_POST["v_plugin_url"]);

	// Install plugin
	if (empty($_SESSION["error_msg"])) {
		exec(HESTIA_CMD . "v-add-plugin " . $v_plugin_url . " json", $output, $return_var);
		$plugin_data = json_decode(implode("", $output), true);
		check_return_code($return_var, $output);
		unset($output);
	}

	// Flush field values on success
	if (empty($_SESSION["error_msg"])) {
		if (is_dir(HESTIA_DIR_WEB . "plugin/{$plugin_data["name"]}/")) {
			$closing_tag = "</b></a>";
			$open_tag = '<a href="/plugin/' . htmlentities($plugin_data["name"]) . '/"><b>';
		} else {
			$closing_tag = "</b>";
			$open_tag = "<b>";
		}

		$_SESSION["ok_msg"] = htmlify_trans(
			sprintf(
				_("Plugin {%s} has been installed successfully."),
				htmlentities($plugin_data["display-name"] ?? $plugin_data["name"]),
			),
			$closing_tag,
			$open_tag,
		);

		unset($plugin_data);
		unset($v_plugin_url);
	}
}

hst_render("add_plugin");

// Flush session messages
unset($_SESSION["error_msg"]);
unset($_SESSION["ok_msg"]);
