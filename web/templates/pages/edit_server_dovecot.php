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
			<?php if (!empty($v_config_path1)) { ?>
				<div class="u-mb20">
					<label for="v_config1" class="form-label"><?= $v_config_path1 ?></label>
					<textarea class="form-control u-min-height300 u-allow-resize u-console" name="v_config1" id="v_config1"><?= $v_config1 ?></textarea>
				</div>
				<div class="u-mb20">
					<label for="v_config2" class="form-label"><?= $v_config_path2 ?></label>
					<textarea class="form-control u-min-height300 u-allow-resize u-console" name="v_config2" id="v_config2"><?= $v_config2 ?></textarea>
				</div>
				<div class="u-mb20">
					<label for="v_config3" class="form-label"><?= $v_config_path3 ?></label>
					<textarea class="form-control u-min-height300 u-allow-resize u-console" name="v_config3" id="v_config3"><?= $v_config3 ?></textarea>
				</div>
				<div class="u-mb20">
					<label for="v_config4" class="form-label"><?= $v_config_path4 ?></label>
					<textarea class="form-control u-min-height300 u-allow-resize u-console" name="v_config4" id="v_config4"><?= $v_config4 ?></textarea>
				</div>
				<div class="u-mb20">
					<label for="v_config5" class="form-label"><?= $v_config_path5 ?></label>
					<textarea class="form-control u-min-height300 u-allow-resize u-console" name="v_config5" id="v_config5"><?= $v_config5 ?></textarea>
				</div>
				<div class="u-mb20">
					<label for="v_config6" class="form-label"><?= $v_config_path6 ?></label>
					<textarea class="form-control u-min-height300 u-allow-resize u-console" name="v_config6" id="v_config6"><?= $v_config6 ?></textarea>
				</div>
				<div class="u-mb20">
					<label for="v_config7" class="form-label"><?= $v_config_path7 ?></label>
					<textarea class="form-control u-min-height300 u-allow-resize u-console" name="v_config7" id="v_config7"><?= $v_config7 ?></textarea>
				</div>
				<div class="u-mb20">
					<label for="v_config8" class="form-label"><?= $v_config_path8 ?></label>
					<textarea class="form-control u-min-height300 u-allow-resize u-console" name="v_config8" id="v_config8"><?= $v_config8 ?></textarea>
				</div>
			<?php } ?>
			<div class="form-check">
				<input class="form-check-input" type="checkbox" name="v_restart" id="v_restart" checked>
				<label for="v_restart">
					<?= _("Restart") ?>
				</label>
			</div>
		</div>

	</form>

</div>
