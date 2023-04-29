<script defer src="/js/vendor/jquery-3.6.4.min.js?<?= JS_LATEST_UPDATE ?>"></script>
<script defer src="/js/dist/main.min.js?<?= JS_LATEST_UPDATE ?>"></script>
<script defer src="/js/vendor/alpine-3.12.0.min.js?<?= JS_LATEST_UPDATE ?>"></script>
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
			USER_PREFIX: '<?= $user_plain ?>_',
			UNLIMITED_SYMBOL: '<?= _("Unlimited") ?>',
			NOTIFICATIONS_EMPTY: '<?= _("No notifications") ?>',
			NOTIFICATIONS_DELETE_ALL: '<?= _("Delete all notifications") ?>',
			CONFIRM_LEAVE_PAGE: '<?= _("LEAVE_PAGE_CONFIRMATION") ?>',
			ERROR_MESSAGE: '<?= !empty($_SESSION['error_msg']) ? htmlentities($_SESSION['error_msg']) : '' ?>',
			BLACKLIST: '<?= _("BLACKLIST") ?>',
			IPVERSE: '<?= _("IPVERSE") ?>'
		});
	})
</script>

<?php if (!empty($_SESSION['error_msg'])) unset($_SESSION['error_msg']); ?>

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
