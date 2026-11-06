<?php

$read_only = false;

if (
	$_SESSION["userContext"] === "user" &&
	($_SESSION["POLICY_USER_VIEW_SUSPENDED"] ?? "") === "yes"
) {
	$policy_data = get_user_data($_SESSION["user"]);
	if (($policy_data[$_SESSION["user"]]["SUSPENDED"] ?? "") === "yes") {
		$read_only = true;
	}
} elseif (
	$_SESSION["userContext"] === "admin" &&
	$_SESSION["look"] === $_SESSION["ROOT_USER"] &&
	($_SESSION["POLICY_SYSTEM_PROTECTED_ADMIN"] ?? "") === "yes"
) {
	$read_only = true;
}

$display_mode = $read_only ? "disabled" : "";
