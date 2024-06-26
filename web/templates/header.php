<?php hst_do_action("init"); ?>
<!doctype html>
<html class="no-js" lang="<?= $_SESSION["LANGUAGE"] ?>">

<head>
<?php
require $_SERVER["HESTIA"] . "/web/templates/includes/title.php";
require $_SERVER["HESTIA"] . "/web/templates/includes/css.php";
require $_SERVER["HESTIA"] . "/web/templates/includes/js.php";

hst_do_action("head");
?>
</head>

<body class="body-<?= strtolower($TAB) ?> lang-<?= $_SESSION["language"] ?> <?php hst_do_action("body_class"); ?>">
