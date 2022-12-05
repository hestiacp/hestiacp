<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="/js/vendor/jquery-3.6.1.min.js?<?=JS_LATEST_UPDATE?>"></script>
<script defer src="/js/vendor/jquery.cookie.js?<?=JS_LATEST_UPDATE?>"></script>
<script defer src="/js/vendor/jquery-ui.min.js?<?=JS_LATEST_UPDATE?>"></script>
<script defer src="/js/vendor/jquery.finder.js?<?=JS_LATEST_UPDATE?>"></script>
<script defer src="/js/vendor/chart.min.js?<?=JS_LATEST_UPDATE?>"></script>
<script defer src="/js/shortcuts.js?<?=JS_LATEST_UPDATE?>"></script>
<script defer src="/js/events.js?<?=JS_LATEST_UPDATE?>"></script>
<script defer src="/js/app.js?<?=JS_LATEST_UPDATE?>"></script>
<script defer src="/js/init.js?<?=JS_LATEST_UPDATE?>"></script>
<script defer src="/js/main.js?<?=JS_LATEST_UPDATE?>"></script>
<script>
	const GLOBAL = {
		FTP_USER_PREFIX: '<?= $user_plain; ?>_',
		DB_USER_PREFIX: '<?= $user_plain; ?>_',
		DB_DBNAME_PREFIX: '<?= $user_plain; ?>_',
		UNLIM_VALUE: 'unlimited',
		UNLIM_TRANSLATED_VALUE: '<?= _("unlimited") ?>',
		NOTIFICATIONS_EMPTY: '<?= _("no notifications") ?>',
		NOTIFICATIONS_DELETE_ALL: '<?= _("Delete notifications") ?>',
	};
</script>
<?php foreach(new DirectoryIterator($_SERVER['HESTIA'].'/web/js/custom_scripts') as $customScript){
	if($customScript->getExtension() === 'js'){
		echo '<script defer src="/js/custom_scripts/'.rawurlencode($customScript->getBasename()).'"></script>';
	} elseif($customScript->getExtension() === "php"){
		require_once($customScript->getPathname());
	}
 } ?>

