<script defer src="/js/dist/main.min.js?<?= JS_LATEST_UPDATE ?>"></script>
<script defer src="/js/dist/alpinejs.min.js?<?= JS_LATEST_UPDATE ?>"></script>
<script>
	document.documentElement.classList.replace('no-js', 'js');
	document.addEventListener('alpine:init', () => {
		Alpine.store('globals', {
			USER_PREFIX: '<?= $user_plain ?>_',
			UNLIMITED: '<?= _("Unlimited") ?>',
			NOTIFICATIONS_EMPTY: '<?= _("No notifications") ?>',
			NOTIFICATIONS_DELETE_ALL: '<?= _("Delete all notifications") ?>',
			CONFIRM_LEAVE_PAGE: '<?= _("Are you sure you want to leave the page?") ?>',
			ERROR_MESSAGE: '<?= !empty($_SESSION["error_msg"]) ? htmlentities($_SESSION["error_msg"]) : "" ?>',
			BLACKLIST: '<?= _("BLACKLIST") ?>',
			IPVERSE: '<?= _("IPVERSE") ?>'
		});
	})
</script>
<?php $_SESSION["unset_alerts"] = true; ?>

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
