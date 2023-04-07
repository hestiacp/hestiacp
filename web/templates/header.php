<!doctype html>
<html class="no-js" lang="<?= $_SESSION["LANGUAGE"] ?>">

<head>
<?php
	require $_SERVER["HESTIA"] . "/web/templates/includes/title.php";
	require $_SERVER["HESTIA"] . "/web/templates/includes/css.php";
	require $_SERVER["HESTIA"] . "/web/templates/includes/js.php";
?>
</head>

<?php
	$selected_theme = !empty($_SESSION["userTheme"]) ? $_SESSION["userTheme"] : $_SESSION["THEME"];
?>

<body
	class="body-<?= strtolower($TAB) ?> lang-<?= $_SESSION["language"] ?>"
	data-theme="<?= $selected_theme ?>"
	data-confirm-leave-page="<?= _("LEAVE_PAGE_CONFIRMATION") ?>"
>
