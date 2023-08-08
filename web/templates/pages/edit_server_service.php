<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/server/">
				<i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
			</a>
		</div>
		<div class="toolbar-buttons">
			<button type="submit" class="button" form="main-form">
				<i class="fas fa-floppy-disk icon-purple"></i><?= _("Save") ?>
			</button>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container">

	<form id="main-form" name="v_configure_server" method="post">
		<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
		<input type="hidden" name="save" value="save">

		<div class="form-container">
			<h1 class="u-mb20"><?= _("Configure Server") ?>: <?= $v_service_name ?></h1>
			<?php show_alert_message($_SESSION); ?>
			<div class="u-mb20">
				<label for="v_config" class="form-label"><?= $v_config_path ?></label>
				<textarea class="form-control u-min-height300 u-allow-resize u-console" name="v_config" id="v_config"><?= $v_config ?></textarea>
			</div>
			<div class="form-check">
				<input class="form-check-input" type="checkbox" name="v_restart" id="v_restart" checked>
				<label for="v_restart">
					<?= _("Restart") ?>
				</label>
			</div>
		</div>

	</form>

</div>
