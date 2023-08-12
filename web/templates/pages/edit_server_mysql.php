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
			<div class="js-basic-options">
				<div class="u-mb10">
					<label for="v_max_connections" class="form-label">max_connections</label>
					<input type="text" class="form-control" data-regexp="max_connections" data-prev-value="<?= htmlentities($v_max_connections) ?>" name="v_max_connections" id="v_max_connections" value="<?= htmlentities($v_max_connections) ?>">
				</div>
				<div class="u-mb10">
					<label for="v_max_user_connections" class="form-label">max_user_connections</label>
					<input type="text" class="form-control" data-regexp="max_user_connections" data-prev-value="<?= htmlentities($v_max_user_connections) ?>" name="v_max_user_connections" id="v_max_user_connections" value="<?= htmlentities($v_max_user_connections) ?>">
				</div>
				<div class="u-mb10">
					<label for="v_wait_timeout" class="form-label">wait_timeout</label>
					<input type="text" class="form-control" data-regexp="wait_timeout" data-prev-value="<?= htmlentities($v_wait_timeout) ?>" name="v_wait_timeout" id="v_wait_timeout" value="<?= htmlentities($v_wait_timeout) ?>">
				</div>
				<div class="u-mb10">
					<label for="v_interactive_timeout" class="form-label">interactive_timeout</label>
					<input type="text" class="form-control" data-regexp="interactive_timeout" data-prev-value="<?= htmlentities($v_interactive_timeout) ?>" name="v_interactive_timeout" id="v_interactive_timeout" value="<?= htmlentities($v_interactive_timeout) ?>">
				</div>
				<div class="u-mb20">
					<label for="v_display_errors" class="form-label">max_allowed_packet</label>
					<input type="text" class="form-control" data-regexp="max_allowed_packet" data-prev-value="<?= htmlentities($v_max_allowed_packet) ?>" name="v_display_errors" id="v_display_errors" value="<?= htmlentities($v_max_allowed_packet) ?>">
				</div>
				<div class="u-mb20">
					<button type="button" class="button button-secondary js-toggle-options">
						<?= _("Advanced Options") ?>
					</button>
				</div>
			</div>
			<div class="js-advanced-options <?php if (empty($v_adv)) echo 'u-hidden'; ?>">
				<div class="u-mb20">
					<button type="button" class="button button-secondary js-toggle-options">
						<?= _("Basic Options") ?>
					</button>
				</div>
				<div class="u-mb20">
					<label for="v_config" class="form-label"><?= $v_config_path ?></label>
					<textarea class="form-control u-min-height600 u-allow-resize u-console js-advanced-textarea" name="v_config" id="v_config"><?= $v_config ?></textarea>
				</div>
				<div class="form-check">
					<input class="form-check-input" type="checkbox" name="v_restart" id="v_restart" checked>
					<label for="v_restart">
						<?= _("Restart") ?>
					</label>
				</div>
			</div>
		</div>

	</form>

</div>
