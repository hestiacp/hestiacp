<script defer src="/js/main.js?<?= JS_LATEST_UPDATE ?>"></script>
<script defer src="/js/shortcuts.js?<?= JS_LATEST_UPDATE ?>"></script>
<script defer src="/js/vendor/alpine-3.10.5.min.js?<?= JS_LATEST_UPDATE ?>"></script>
<script defer src="/js/vendor/jquery-3.6.3.min.js?<?= JS_LATEST_UPDATE ?>"></script>
<script defer src="/js/vendor/jquery-ui.min.js?<?= JS_LATEST_UPDATE ?>"></script>
<script defer src="/js/vendor/chart.min.js?<?= JS_LATEST_UPDATE ?>"></script>
<script defer src="/js/events.js?<?= JS_LATEST_UPDATE ?>"></script>
<script defer src="/js/init.js?<?= JS_LATEST_UPDATE ?>"></script>
<script>
	// TODO: REMOVE
	const App = {
		Actions: {
			DB: {},
			WEB: {},
			PACKAGE: {},
			MAIL_ACC: {},
			MAIL: {},
		},
		Listeners: {
			DB: {},
			WEB: {},
			PACKAGE: {},
			MAIL_ACC: {},
		}
	}

	document.documentElement.classList.replace('no-js', 'js');
	document.addEventListener('alpine:init', () => {
		Alpine.store('globals', {
			FTP_USER_PREFIX: '<?= $user_plain ?>_',
			DB_USER_PREFIX: '<?= $user_plain ?>_',
			DB_DBNAME_PREFIX: '<?= $user_plain ?>_',
			UNLIM_VALUE: 'unlimited',
			UNLIM_TRANSLATED_VALUE: '<?= _("unlimited") ?>',
			NOTIFICATIONS_EMPTY: '<?= _("no notifications") ?>',
			NOTIFICATIONS_DELETE_ALL: '<?= _("Delete all notifications") ?>',
			isUnlimitedValue(value) {
				return value.trim() == this.UNLIM_VALUE || value.trim() == this.UNLIM_TRANSLATED_VALUE;
			}
		});
	})
</script>

<?php
$customScriptDirectory = new DirectoryIterator($_SERVER["HESTIA"] . "/web/js/custom_scripts");
foreach ($customScriptDirectory as $customScript) {
	$extension = $customScript->getExtension();
	if ($extension === "js") {
		$customScriptPath = "/js/custom_scripts/" . rawurlencode($customScript->getBasename());
		echo '<script defer src="' . $customScriptPath . '"></script>';
	} elseif ($extension === "php") {
		require_once $customScript->getPathname();
	}
} ?>
