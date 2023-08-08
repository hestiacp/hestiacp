<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
$TAB = "USER";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check user argument
if (empty($_GET["user"])) {
	header("Location: /list/user/");
	exit();
}

// Edit as someone else?
if ($_SESSION["userContext"] === "admin" && !empty($_GET["user"])) {
	$user = $_GET["user"];
	$v_username = $_GET["user"];
} else {
	$user = $_SESSION["user"];
	$v_username = $_SESSION["user"];
}

// Prevent other users with admin privileges from editing properties of default 'admin' user
if (
	($_SESSION["userContext"] === "admin" && $_SESSION["look"] != "" && $user == "admin") ||
	($_SESSION["userContext"] === "admin" &&
		!isset($_SESSION["look"]) &&
		$user == "admin" &&
		$_SESSION["user"] != "admin")
) {
	header("Location: /list/user/");
	exit();
}

// Check token
verify_csrf($_GET);

// List user
exec(HESTIA_CMD . "v-list-user " . quoteshellarg($v_username) . " json", $output, $return_var);
check_return_code_redirect($return_var, $output, "/list/user/");

$data = json_decode(implode("", $output), true);
unset($output);

// Parse user
$v_password = "";
$v_email = $data[$v_username]["CONTACT"];
$v_package = $data[$v_username]["PACKAGE"];
$v_language = $data[$v_username]["LANGUAGE"];
$v_user_theme = $data[$v_username]["THEME"];
$v_sort_order = $data[$v_username]["PREF_UI_SORT"];
$v_name = $data[$v_username]["NAME"];
$v_shell = $data[$v_username]["SHELL"];
$v_twofa = $data[$v_username]["TWOFA"];
$v_qrcode = $data[$v_username]["QRCODE"];
$v_phpcli = $data[$v_username]["PHPCLI"];
$v_role = $data[$v_username]["ROLE"];
$v_login_disabled = $data[$v_username]["LOGIN_DISABLED"];
$v_login_use_iplist = $data[$v_username]["LOGIN_USE_IPLIST"];
$v_login_allowed_ips = $data[$v_username]["LOGIN_ALLOW_IPS"];
$v_ns = $data[$v_username]["NS"];
$nameservers = explode(",", $v_ns);
if (empty($nameservers[0])) {
	$v_ns1 = "";
} else {
	$v_ns1 = $nameservers[0];
}
if (empty($nameservers[1])) {
	$v_ns2 = "";
} else {
	$v_ns2 = $nameservers[1];
}
if (empty($nameservers[2])) {
	$v_ns3 = "";
} else {
	$v_ns3 = $nameservers[2];
}
if (empty($nameservers[3])) {
	$v_ns4 = "";
} else {
	$v_ns4 = $nameservers[3];
}
if (empty($nameservers[4])) {
	$v_ns5 = "";
} else {
	$v_ns5 = $nameservers[4];
}
if (empty($nameservers[5])) {
	$v_ns6 = "";
} else {
	$v_ns6 = $nameservers[5];
}
if (empty($nameservers[6])) {
	$v_ns7 = "";
} else {
	$v_ns7 = $nameservers[6];
}
if (empty($nameservers[7])) {
	$v_ns8 = "";
} else {
	$v_ns8 = $nameservers[7];
}

$v_suspended = $data[$v_username]["SUSPENDED"];
if ($v_suspended == "yes") {
	$v_status = "suspended";
} else {
	$v_status = "active";
}
$v_time = $data[$v_username]["TIME"];
$v_date = $data[$v_username]["DATE"];

if (empty($v_phpcli)) {
	$v_phpcli = substr(DEFAULT_PHP_VERSION, 4);
}

// List packages
exec(HESTIA_CMD . "v-list-user-packages json", $output, $return_var);
$packages = json_decode(implode("", $output), true);
unset($output);

// List languages
exec(HESTIA_CMD . "v-list-sys-languages json", $output, $return_var);
$language = json_decode(implode("", $output), true);
foreach ($language as $lang) {
	$languages[$lang] = translate_json($lang);
}
asort($languages);
unset($output);

// List themes
exec(HESTIA_CMD . "v-list-sys-themes json", $output, $return_var);
$themes = json_decode(implode("", $output), true);
unset($output);

// List shells
exec(HESTIA_CMD . "v-list-sys-shells json", $output, $return_var);
$shells = json_decode(implode("", $output), true);
unset($output);

//List PHP Versions
// List supported php versions
exec(HESTIA_CMD . "v-list-sys-php json", $output, $return_var);
$php_versions = json_decode(implode("", $output), true);
unset($output);

// Check POST request
if (!empty($_POST["save"])) {
	// Check token
	verify_csrf($_POST);

	// Change password
	if (!empty($_POST["v_password"]) && empty($_SESSION["error_msg"])) {
		// Check password length
		$pw_len = strlen($_POST["v_password"]);
		if (!validate_password($_POST["v_password"])) {
			$_SESSION["error_msg"] = _("Password does not match the minimum requirements.");
		}
		if (empty($_SESSION["error_msg"])) {
			$v_password = tempnam("/tmp", "vst");
			$fp = fopen($v_password, "w");
			fwrite($fp, $_POST["v_password"] . "\n");
			fclose($fp);
			exec(
				HESTIA_CMD .
					"v-change-user-password " .
					quoteshellarg($v_username) .
					" " .
					$v_password,
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
			unlink($v_password);
			$v_password = quoteshellarg($_POST["v_password"]);
		}
	}

	// Enable twofa
	if (!empty($_POST["v_twofa"]) && empty($v_twofa) && empty($_SESSION["error_msg"])) {
		exec(HESTIA_CMD . "v-add-user-2fa " . quoteshellarg($v_username), $output, $return_var);
		check_return_code($return_var, $output);
		unset($output);

		// List user
		exec(
			HESTIA_CMD . "v-list-user " . quoteshellarg($v_username) . " json",
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		$data = json_decode(implode("", $output), true);
		unset($output);

		// Parse user twofa
		$v_twofa = $data[$v_username]["TWOFA"];
		$v_qrcode = $data[$v_username]["QRCODE"];
	}

	// Disable twofa
	if (empty($_POST["v_twofa"]) && !empty($v_twofa) && empty($_SESSION["error_msg"])) {
		exec(HESTIA_CMD . "v-delete-user-2fa " . quoteshellarg($v_username), $output, $return_var);
		check_return_code($return_var, $output);
		unset($output);
		$v_twofa = "";
		$v_qrcode = "";
	}

	// Change default sort order
	if ($v_sort_order != $_POST["v_sort_order"] && empty($_SESSION["error_msg"])) {
		$v_sort_order = quoteshellarg($_POST["v_sort_order"]);
		exec(
			HESTIA_CMD .
				"v-change-user-sort-order " .
				quoteshellarg($v_username) .
				" " .
				$v_sort_order,
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		unset($_SESSION["userSortOrder"]);
		$_SESSION["userSortOrder"] = $v_sort_order;
		unset($output);
	}

	// Update Control Panel login disabled status (admin only)
	if (empty($_SESSION["error_msg"])) {
		if (empty($_POST["v_login_disabled"])) {
			$_POST["v_login_disabled"] = "";
		}
		if ($_POST["v_login_disabled"] != $v_login_disabled) {
			if ($_POST["v_login_disabled"] == "on") {
				$_POST["v_login_disabled"] = "yes";
			} else {
				$_POST["v_login_disabled"] = "no";
			}
			exec(
				HESTIA_CMD .
					"v-change-user-config-value " .
					quoteshellarg($v_username) .
					" LOGIN_DISABLED " .
					quoteshellarg($_POST["v_login_disabled"]),
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			$data[$user]["LOGIN_DISABLED"] = $_POST["v_login_disabled"];
			unset($output);
		}
	}

	// Update IP whitelist option
	if (empty($_SESSION["error_msg"])) {
		if (empty($_POST["v_login_use_iplist"])) {
			$_POST["v_login_use_iplist"] = "";
		}
		if ($_POST["v_login_use_iplist"] != $v_login_use_iplist) {
			if ($_POST["v_login_use_iplist"] == "on") {
				$_POST["v_login_use_iplist"] = "yes";
			} else {
				$_POST["v_login_use_iplist"] = "no";
			}
			exec(
				HESTIA_CMD .
					"v-change-user-config-value " .
					quoteshellarg($v_username) .
					" LOGIN_USE_IPLIST " .
					quoteshellarg($_POST["v_login_use_iplist"]),
				$output,
				$return_var,
			);
			if ($_POST["v_login_use_iplist"] === "no") {
				exec(
					HESTIA_CMD .
						"v-change-user-config-value " .
						quoteshellarg($v_username) .
						" LOGIN_ALLOW_IPS ''",
					$output,
					$return_var,
				);
				$v_login_allowed_ips = "";
			} else {
				exec(
					HESTIA_CMD .
						"v-change-user-config-value " .
						quoteshellarg($v_username) .
						" LOGIN_ALLOW_IPS " .
						quoteshellarg($_POST["v_login_allowed_ips"]),
					$output,
					$return_var,
				);
				unset($v_login_allowed_ips);
				$v_login_allowed_ips = $_POST["v_login_allowed_ips"];
			}
			check_return_code($return_var, $output);
			$data[$user]["LOGIN_USE_IPLIST"] = $_POST["v_login_use_iplist"];
			unset($output);
		}
	}

	if ($_SESSION["userContext"] === "admin") {
		// Change package (admin only)
		if (
			$v_package != $_POST["v_package"] &&
			$_SESSION["userContext"] === "admin" &&
			empty($_SESSION["error_msg"])
		) {
			$v_package = quoteshellarg($_POST["v_package"]);
			exec(
				HESTIA_CMD .
					"v-change-user-package " .
					quoteshellarg($v_username) .
					" " .
					$v_package,
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
		}

		// Change phpcli (admin only)
		if (
			$v_phpcli != $_POST["v_phpcli"] &&
			$_SESSION["userContext"] === "admin" &&
			empty($_SESSION["error_msg"])
		) {
			$v_phpcli = quoteshellarg($_POST["v_phpcli"]);
			exec(
				HESTIA_CMD .
					"v-change-user-php-cli " .
					quoteshellarg($v_username) .
					" " .
					$v_phpcli,
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
		}

		$_POST["v_role"] = $_POST["v_role"] ?? "";

		if (
			$v_role != $_POST["v_role"] &&
			$_SESSION["userContext"] === "admin" &&
			$v_username != "admin" &&
			empty($_SESSION["error_msg"])
		) {
			if (!empty($_POST["v_role"])) {
				$v_role = quoteshellarg($_POST["v_role"]);
				exec(
					HESTIA_CMD . "v-change-user-role " . quoteshellarg($v_username) . " " . $v_role,
					$output,
					$return_var,
				);
				check_return_code($return_var, $output);
				unset($output);
				$v_role = $_POST["v_role"];
			}
		}
		// Change shell (admin only)
		if (!empty($_POST["v_shell"])) {
			if (
				$v_shell != $_POST["v_shell"] &&
				$_SESSION["userContext"] === "admin" &&
				empty($_SESSION["error_msg"])
			) {
				$v_shell = quoteshellarg($_POST["v_shell"]);
				exec(
					HESTIA_CMD .
						"v-change-user-shell " .
						quoteshellarg($v_username) .
						" " .
						$v_shell,
					$output,
					$return_var,
				);
				check_return_code($return_var, $output);
				unset($output);
			}
		}
	}
	// Change language
	if ($v_language != $_POST["v_language"] && empty($_SESSION["error_msg"])) {
		$v_language = quoteshellarg($_POST["v_language"]);
		exec(
			HESTIA_CMD . "v-change-user-language " . quoteshellarg($v_username) . " " . $v_language,
			$output,
			$return_var,
		);
		check_return_code($return_var, $output);
		if (empty($_SESSION["error_msg"])) {
			if ($_GET["user"] == $_SESSION["user"]) {
				unset($_SESSION["language"]);
				$_SESSION["language"] = $_POST["v_language"];
				$refresh = $_SERVER["REQUEST_URI"];
				header("Location: $refresh");
			}
		}
		unset($output);
	}

	// Change contact email
	if ($v_email != $_POST["v_email"] && empty($_SESSION["error_msg"])) {
		if (!filter_var($_POST["v_email"], FILTER_VALIDATE_EMAIL)) {
			$_SESSION["error_msg"] = _("Please enter a valid email address.");
		} else {
			$v_email = quoteshellarg($_POST["v_email"]);
			exec(
				HESTIA_CMD . "v-change-user-contact " . quoteshellarg($v_username) . " " . $v_email,
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
		}
	}

	// Change full name
	if ($v_name != $_POST["v_name"]) {
		if (empty($_POST["v_name"])) {
			$_SESSION["error_msg"] = _("Please enter a valid contact name.");
		} else {
			$v_name = quoteshellarg($_POST["v_name"]);
			exec(
				HESTIA_CMD . "v-change-user-name " . quoteshellarg($v_username) . " " . $v_name,
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
			$v_name = $_POST["v_name"];
		}
	}

	// Update theme
	if (empty($_SESSION["error_msg"])) {
		if (empty($_SESSION["userTheme"])) {
			$_SESSION["userTheme"] = "";
		}
		if ($_POST["v_user_theme"] != $_SESSION["userTheme"]) {
			exec(
				HESTIA_CMD .
					"v-change-user-theme " .
					quoteshellarg($v_username) .
					" " .
					quoteshellarg($_POST["v_user_theme"]),
				$output,
				$return_var,
			);
			check_return_code($return_var, $output);
			unset($output);
			$v_user_theme = $_POST["v_user_theme"];
			if ($_SESSION["user"] === $v_username) {
				unset($_SESSION["userTheme"]);
				$_SESSION["userTheme"] = $v_user_theme;
			}
		}
	}

	if (!empty($_SESSION["DNS_SYSTEM"])) {
		if ($_SESSION["userContext"] === "admin") {
			// Change NameServers
			if (empty($_POST["v_ns1"])) {
				$_POST["v_ns1"] = "";
			}
			if (empty($_POST["v_ns2"])) {
				$_POST["v_ns2"] = "";
			}
			if (empty($_POST["v_ns3"])) {
				$_POST["v_ns3"] = "";
			}
			if (empty($_POST["v_ns4"])) {
				$_POST["v_ns4"] = "";
			}
			if (empty($_POST["v_ns5"])) {
				$_POST["v_ns5"] = "";
			}
			if (empty($_POST["v_ns6"])) {
				$_POST["v_ns6"] = "";
			}
			if (empty($_POST["v_ns7"])) {
				$_POST["v_ns7"] = "";
			}
			if (empty($_POST["v_ns8"])) {
				$_POST["v_ns8"] = "";
			}

			if (
				$v_ns1 != $_POST["v_ns1"] ||
				$v_ns2 != $_POST["v_ns2"] ||
				$v_ns3 != $_POST["v_ns3"] ||
				$v_ns4 != $_POST["v_ns4"] ||
				$v_ns5 != $_POST["v_ns5"] ||
				$v_ns6 != $_POST["v_ns6"] ||
				$v_ns7 != $_POST["v_ns7"] ||
				($v_ns8 != $_POST["v_ns8"] &&
					empty($_SESSION["error_msg"] && !empty($_POST["v_ns1"]) && $_POST["v_ns2"]))
			) {
				$v_ns1 = quoteshellarg($_POST["v_ns1"]);
				$v_ns2 = quoteshellarg($_POST["v_ns2"]);
				$v_ns3 = quoteshellarg($_POST["v_ns3"]);
				$v_ns4 = quoteshellarg($_POST["v_ns4"]);
				$v_ns5 = quoteshellarg($_POST["v_ns5"]);
				$v_ns6 = quoteshellarg($_POST["v_ns6"]);
				$v_ns7 = quoteshellarg($_POST["v_ns7"]);
				$v_ns8 = quoteshellarg($_POST["v_ns8"]);

				$ns_cmd =
					HESTIA_CMD .
					"v-change-user-ns " .
					quoteshellarg($v_username) .
					" " .
					$v_ns1 .
					" " .
					$v_ns2;
				if (!empty($_POST["v_ns3"])) {
					$ns_cmd = $ns_cmd . " " . $v_ns3;
				}
				if (!empty($_POST["v_ns4"])) {
					$ns_cmd = $ns_cmd . " " . $v_ns4;
				}
				if (!empty($_POST["v_ns5"])) {
					$ns_cmd = $ns_cmd . " " . $v_ns5;
				}
				if (!empty($_POST["v_ns6"])) {
					$ns_cmd = $ns_cmd . " " . $v_ns6;
				}
				if (!empty($_POST["v_ns7"])) {
					$ns_cmd = $ns_cmd . " " . $v_ns7;
				}
				if (!empty($_POST["v_ns8"])) {
					$ns_cmd = $ns_cmd . " " . $v_ns8;
				}
				exec($ns_cmd, $output, $return_var);
				check_return_code($return_var, $output);
				unset($output);

				$v_ns1 = str_replace("'", "", $v_ns1);
				$v_ns2 = str_replace("'", "", $v_ns2);
				$v_ns3 = str_replace("'", "", $v_ns3);
				$v_ns4 = str_replace("'", "", $v_ns4);
				$v_ns5 = str_replace("'", "", $v_ns5);
				$v_ns6 = str_replace("'", "", $v_ns6);
				$v_ns7 = str_replace("'", "", $v_ns7);
				$v_ns8 = str_replace("'", "", $v_ns8);
			}
		}
	}

	// Set success message
	if (empty($_SESSION["error_msg"])) {
		$_SESSION["ok_msg"] = _("Changes have been saved.");
	}
}

// Render page
render_page($user, $TAB, "edit_user");

// Flush session messages
unset($_SESSION["error_msg"]);
unset($_SESSION["ok_msg"]);
