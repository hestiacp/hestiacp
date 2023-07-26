<?php

$TAB = "TERMINAL";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check user
if ($_SESSION["userContext"] != "admin") {
	header("Location: /list/user");
	exit();
}

// Render page
render_page($user, $TAB, "list_terminal");

// Back uri
$_SESSION["back"] = $_SERVER["REQUEST_URI"];
