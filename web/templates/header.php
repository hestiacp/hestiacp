<!doctype html>
<html class="no-js" lang="<?=$_SESSION['LANGUAGE']?>">

<head>
	<?php
		require $_SERVER['HESTIA'] . '/web/templates/includes/title.php';
		require $_SERVER['HESTIA'] . '/web/templates/includes/css.php';
		require $_SERVER['HESTIA'] . '/web/templates/includes/top_js.php';
	?>
	<script>
		<?php
			// GLOBAL SETTINGS
		?>
		var GLOBAL = {};
		GLOBAL.FTP_USER_PREFIX = '';
		GLOBAL.DB_USER_PREFIX = '';
		GLOBAL.DB_DBNAME_PREFIX = '';
		GLOBAL.AJAX_URL = '';
	</script>
</head>

<body class="body-<?=strtolower($TAB)?> lang-<?=$_SESSION['language']?>">
<?php
	if (($_SESSION['DEBUG_MODE']) == "true" ) {
		require $_SERVER['HESTIA'] . '/web/templates/pages/debug_panel.php';
	}
