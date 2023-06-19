<?php
use function Hestiacp\quoteshellarg\quoteshellarg;
ob_start();
$TAB = "Access Key";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Checks if API access is enabled
$api_status =
	!empty($_SESSION["API_SYSTEM"]) && is_numeric($_SESSION["API_SYSTEM"])
		? $_SESSION["API_SYSTEM"]
		: 0;
if (($user_plain == "admin" && $api_status < 1) || ($user_plain != "admin" && $api_status < 2)) {
	header("Location: /edit/user/");
	exit();
}

// APIs available
exec(HESTIA_CMD . "v-list-apis json", $output, $return_var);
$apis = json_decode(implode("", $output), true);
$apis = array_filter($apis, function ($api) use ($user_plain) {
	return $user_plain == "admin" || $api["ROLE"] == "user";
});
ksort($apis);
unset($output);

// Check POST request
if (!empty($_POST["ok"])) {
	// Check token
	verify_csrf($_POST);

	// Validate apis
	$apis_selected = !empty($_POST["v_apis"]) && is_array($_POST["v_apis"]) ? $_POST["v_apis"] : [];
	$check_invalid_apis = array_filter($apis_selected, function ($selected) use ($apis) {
		return !array_key_exists($selected, $apis);
	});

	if (empty($apis_selected)) {
		$errors[] = _("Permissions");
	} elseif (count($check_invalid_apis) > 0) {
		//$errors[] = sprintf("%d apis not allowed", count($check_invalid_apis));
		foreach ($check_invalid_apis as $api_name) {
			$errors[] = sprintf("API %s not allowed", $api_name);
		}
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
	$v_apis = quoteshellarg(implode(",", $apis_selected));
	$v_comment = quoteshellarg(trim($_POST["v_comment"] ?? ""));

	// Add access key
	if (empty($_SESSION["error_msg"])) {
		exec(
			HESTIA_CMD . "v-add-access-key " . $user . " " . $v_apis . " " . $v_comment . " json",
			$output,
			$return_var,
		);
		$key_data = json_decode(implode("", $output), true);
		check_return_code($return_var, $output);
		unset($output);
	}

	// Flush field values on success
	if (empty($_SESSION["error_msg"])) {
		$_SESSION["ok_msg"] = htmlify_trans(
			sprintf(
				_("Access key {%s} has been created successfully."),
				htmlentities($key_data["ACCESS_KEY_ID"]),
			),
			"</code>",
			"<code>",
		);
		unset($apis_selected);
		unset($check_invalid_apis);
		unset($v_apis);
		unset($v_comment);
	}
}

// Render
if (empty($key_data)) {
	render_page($user, $TAB, "add_access_key");
} else {
	render_page($user, $TAB, "list_access_key");
}

// Flush session messages
unset($_SESSION["error_msg"]);
unset($_SESSION["ok_msg"]);
