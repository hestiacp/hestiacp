<script src="/js/vendor/jquery-3.6.1.min.js<?=JS_LATEST_UPDATE?>"></script>
<script defer src="/js/vendor/jquery.cookie.js?<?=JS_LATEST_UPDATE?>"></script>
<script defer src="/js/vendor/jquery-ui.min.js?<?=JS_LATEST_UPDATE?>"></script>
<script defer src="/js/vendor/jquery.finder.js?<?=JS_LATEST_UPDATE?>"></script>
<script defer src="/js/vendor/chart.min.js?<?=JS_LATEST_UPDATE?>"></script>
<script defer src="/js/shortcuts.js?<?=JS_LATEST_UPDATE?>"></script>
<script defer src="/js/events.js?<?=JS_LATEST_UPDATE?>"></script>
<script defer src="/js/app.js?<?=JS_LATEST_UPDATE?>"></script>
<script defer src="/js/init.js?<?=JS_LATEST_UPDATE?>"></script>
<script defer src="/js/i18n.js.php?<?=JS_LATEST_UPDATE?>"></script>
<script>
	var GLOBAL = {};
	GLOBAL.FTP_USER_PREFIX = '';
	GLOBAL.DB_USER_PREFIX = '';
	GLOBAL.DB_DBNAME_PREFIX = '';
	GLOBAL.AJAX_URL = '';
</script>
<?php foreach(new DirectoryIterator($_SERVER['HESTIA'].'/web/js/custom_scripts') as $customScript){
	if($customScript->getExtension() === 'js'){
		echo '<script defer src="/js/custom_scripts/'.rawurlencode($customScript->getBasename()).'"></script>';
	} elseif($customScript->getExtension() === "php"){
		require_once($customScript->getPathname());
	}
 } ?>

