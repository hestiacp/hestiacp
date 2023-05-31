<?php
$TAB = "PLUGINS";

// Main include
include($_SERVER['DOCUMENT_ROOT'] . "/inc/main.php");

// Check user
if ($_SESSION["userContext"] != "admin") {
    header("Location: /list/user/");
    exit;
}

// Render page
hst_render("list_plugin");
