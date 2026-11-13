<?php

$TAB = "TERMINAL";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

if ($read_only === true) {
	header("Location: /list/user/");
	exit();
}

if ($_SESSION["login_shell"] == "nologin") {
	header("Location: /list/user/");
	exit();
}

// Render page
render_page($user, $TAB, "list_terminal");
