	</main>
<?php if (
	$_SESSION["userContext"] === "admin" &&
	$_SESSION["POLICY_SYSTEM_HIDE_SERVICES"] !== "yes" &&
	$_SESSION["UPDATE_AVAILABLE"] === "yes"
) {
?>
	<p x-data="{ open: true }" x-cloak x-show="open" class="updates-banner">
		<span class="u-text-bold">New updates are available!</span> To upgrade your server now, run
		<code>apt update && apt upgrade</code> from a shell session.
		(<button type="button" class="u-text-bold" x-on:click="open = false">
			hide
		</button>)
	</p>
<?php } ?>
	<div class="spinner-overlay js-spinner">
		<i class="fas fa-circle-notch fa-spin"></i>
	</div>

	<div x-data>
		<dialog x-ref="dialog" class="shortcuts">
			<div class="shortcuts-header">
				<div class="shortcuts-title"><?= _("Shortcuts") ?></div>
				<div
					x-on:click="$refs.dialog.close()"
					class="shortcuts-close"
				>
					<i class="fas fa-xmark"></i>
				</div>
			</div>
			<div class="shortcuts-inner">
				<ul class="shortcuts-list">
					<li><span class="key">a</span><?= _("Add new object") ?></li>
					<li><span class="key">Ctrl + Enter</span><?= _("Save form") ?></li>
					<li class="u-mb20"><span class="key">Ctrl + Backspace</span><?= _("Unsave form") ?></li>
					<li><span class="key">1</span><?= _("Go to WEB list") ?></li>
					<li><span class="key">2</span><?= _("Go to DNS list") ?></li>
					<li><span class="key">3</span><?= _("Go to MAIL list") ?></li>
					<li><span class="key">4</span><?= _("Go to DB list") ?></li>
					<li><span class="key">5</span><?= _("Go to CRON list") ?></li>
					<li><span class="key">6</span><?= _("Go to BACKUP list") ?></li>
				</ul>
				<ul class="shortcuts-list">
					<li class="u-mb20"><span class="key">f</span><?= _("Focus on search") ?></li>
					<li class="u-mb20"><span class="key">h</span><?= _("Display / Hide shortcuts") ?></li>
					<li><span class="key bigger">&larr;</span><?= _("Move backward through top menu") ?></li>
					<li><span class="key bigger">&rarr;</span><?= _("Move forward through top menu") ?></li>
					<li class="u-mb20"><span class="key">Enter</span><?= _("Enter focused element") ?></li>
					<li><span class="key bigger">&uarr;</span><?= _("Move up through elements list") ?></li>
					<li><span class="key bigger">&darr;</span><?= _("Move down through elements list") ?></li>
				</ul>
			</div>
		</dialog>

		<button
			x-on:click="$refs.dialog.showModal()"
			type="button"
			class="button button-secondary button-circle button-floating button-floating-shortcuts"
			title="<?= _("Shortcuts") ?>"
		>
			<i class="fas fa-keyboard"></i>
			<span class="u-hidden"><?= _("Shortcuts") ?></span>
		</button>
	</div>
	<a
		href="#top"
		class="button button-secondary button-circle button-floating button-floating-top"
		title="<?= _("Top") ?>"
	>
		<i class="fas fa-arrow-up"></i>
		<span class="u-hidden"><?= _("Top") ?></span>
	</a>

<?php if ($_SESSION["DEBUG_MODE"] == "true") {
	require $_SERVER["HESTIA"] . "/web/templates/pages/debug_panel.php";
} ?>

</body>
</html>
