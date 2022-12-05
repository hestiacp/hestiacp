	</main>
<?php
	if (($_SESSION['userContext'] === 'admin') && ($_SESSION['POLICY_SYSTEM_HIDE_SERVICES'] !== 'yes')) {
		if ($_SESSION['UPDATE_AVAILABLE'] === 'yes') {
?>
	<div class="footer-banner updates" id="banner" x-data="{ open: false }>
		<div>
			<b>New updates are available!</b> To upgrade your server now, run <span style="font-family:'Courier New', Courier, monospace">apt update && apt upgrade</span> from a shell session.
		</div>
		<div style="margin-top: 4px;"></div><a href="#" x-on:click="open = !open">Hide</a></div>
	</div>
<?php
		}
	}
?>
	<div title="<?=_('Confirmation');?>" class="dialog js-confirm-dialog-redirect">
		<p><?=_('LEAVE_PAGE_CONFIRMATION')?></p>
	</div>

	<div x-data>
		<dialog x-ref="dialog" class="shortcuts animate__animated animate__fadeIn">
			<div class="shortcuts-header">
				<div class="shortcuts-title"><?=_('Shortcuts')?></div>
				<div
					x-on:click="$refs.dialog.close()"
					class="shortcuts-close"
				>
					<i class="fas fa-xmark"></i>
				</div>
			</div>
			<div class="shortcuts-inner">
				<ul class="shortcuts-list">
					<li><span class="key">a</span><?=_('Add New object')?></li>
					<li><span class="key">Ctrl + Enter</span><?=_('Save Form')?></li>
					<li class="u-mb20"><span class="key">Ctrl + Backspace</span><?=_('Cancel saving form')?></li>
					<li><span class="key">1</span><?=_('Go to WEB list')?></li>
					<li><span class="key">2</span><?=_('Go to DNS list')?></li>
					<li><span class="key">3</span><?=_('Go to MAIL list')?></li>
					<li><span class="key">4</span><?=_('Go to DB list')?></li>
					<li><span class="key">5</span><?=_('Go to CRON list')?></li>
					<li><span class="key">6</span><?=_('Go to BACKUP list')?></li>
				</ul>
				<ul class="shortcuts-list">
					<li class="u-mb20"><span class="key">f</span><?=_('Focus on search')?></li>
					<li class="u-mb20"><span class="key">h</span><?=_('Display/Close shortcuts')?></li>
					<li><span class="key bigger">&larr;</span><?=_('Move backward through top menu')?></li>
					<li><span class="key bigger">&rarr;</span><?=_('Move forward through top menu')?></li>
					<li class="u-mb20"><span class="key">Enter</span><?=_('Enter focused element')?></li>
					<li><span class="key bigger">&uarr;</span><?=_('Move up through elements list')?></li>
					<li><span class="key bigger">&darr;</span><?=_('Move down through elements list')?></li>
				</ul>
			</div>
		</dialog>

		<button
			x-data x-on:click="$refs.dialog.showModal()"
			type="button"
			class="button button-secondary button-circle button-floating button-floating-shortcuts"
			title="<?=_('Shortcuts');?>"
		>
			<i class="fas fa-keyboard"></i>
			<span class="u-hidden"><?=_('Shortcuts');?></span>
		</button>
	</div>
	<a
		href="#top"
		class="button button-secondary button-circle button-floating button-floating-top "
		title="<?=_('Top');?>"
	>
		<i class="fas fa-arrow-up"></i>
		<span class="u-hidden"><?=_('Top');?></span>
	</a>

<?php
	if (!empty($_SESSION['error_msg'])):
	?>
	<div>
		<script>
			$(function() {
				$('#dialog:ui-dialog').dialog('destroy');
				$('#dialog-message').dialog({
					modal: true,
					resizable: false,
					buttons: {
						Ok: function() {
							$(this).dialog('close');
						}
					},
					create: function() {
						var buttonGroup = $(this).closest(".ui-dialog").find('.ui-dialog-buttonset');
						buttonGroup.find('button:first').addClass('button submit')
						buttonGroup.find('button:last').addClass('button button-secondary cancel');
					}
				});
			});
		</script>
		<div id="dialog-message" title="">
			<p><?=htmlentities($_SESSION['error_msg'])?></p>
		</div>
	</div>
<?php
	unset($_SESSION['error_msg']);
	endif;

	if (($_SESSION['DEBUG_MODE']) == "true") {
		require $_SERVER['HESTIA'] . '/web/templates/pages/debug_panel.php';
	}
?>

</body>
</html>
