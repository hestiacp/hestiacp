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
			<h1 class="u-mb20"><?= _("Configure Server") ?>: PHP</h1>
			<?php show_alert_message($_SESSION); ?>
			<div class="js-basic-options">
				<div class="u-mb10">
					<label for="v_max_execution_time" class="form-label">max_execution_time</label>
					<input type="text" class="form-control" data-regexp="max_execution_time" data-prev-value="<?= htmlentities($v_max_execution_time) ?>" name="v_max_execution_time" id="v_max_execution_time" value="<?= htmlentities($v_max_execution_time) ?>">
				</div>
				<div class="u-mb10">
					<label for="v_worker_connections" class="form-label">max_input_time</label>
					<input type="text" class="form-control" data-regexp="max_input_time" data-prev-value="<?= htmlentities($v_max_input_time) ?>" name="v_worker_connections" id="v_worker_connections" value="<?= htmlentities($v_max_input_time) ?>">
				</div>
				<div class="u-mb10">
					<label for="v_memory_limit" class="form-label">memory_limit</label>
					<input type="text" class="form-control" data-regexp="memory_limit" data-prev-value="<?= htmlentities($v_memory_limit) ?>" name="v_memory_limit" id="v_memory_limit" value="<?= htmlentities($v_memory_limit) ?>">
				</div>
				<div class="u-mb10">
					<label for="v_error_reporting" class="form-label">error_reporting</label>
					<input type="text" class="form-control" data-regexp="error_reporting" data-prev-value="<?= htmlentities($v_error_reporting) ?>" name="v_error_reporting" id="v_error_reporting" value="<?= htmlentities($v_error_reporting) ?>">
				</div>
				<div class="u-mb10">
					<label for="v_display_errors" class="form-label">display_errors</label>
					<input type="text" class="form-control" data-regexp="display_errors" data-prev-value="<?= htmlentities($v_display_errors) ?>" name="v_display_errors" id="v_display_errors" value="<?= htmlentities($v_display_errors) ?>">
				</div>
				<div class="u-mb10">
					<label for="v_post_max_size" class="form-label">post_max_size</label>
					<input type="text" class="form-control" data-regexp="post_max_size" data-prev-value="<?= htmlentities($v_post_max_size) ?>" name="v_post_max_size" id="v_post_max_size" value="<?= htmlentities($v_post_max_size) ?>">
				</div>
				<div class="u-mb20">
					<label for="v_upload_max_filesize" class="form-label">upload_max_filesize</label>
					<input type="text" class="form-control" data-regexp="upload_max_filesize" data-prev-value="<?= htmlentities($v_upload_max_filesize) ?>" name="v_upload_max_filesize" id="v_upload_max_filesize" value="<?= htmlentities($v_upload_max_filesize) ?>">
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
