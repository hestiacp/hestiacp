<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a href="/edit/server/" class="button button-secondary" id="btn-back">
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

<!-- Begin form -->
<div class="container">
	<form
		x-data="{
			hide_docs: '<?= $v_hide_docs ?? "no" ?>',
		}"
		id="main-form"
		name="v_configure_server"
		method="post"
	>
		<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
		<input type="hidden" name="save" value="save">

		<div class="form-container">
			<h1 class="u-mb20">
				<?= _("White Label Options") ?>
			</h1>
			<?php show_alert_message($_SESSION); ?>

			<!-- Basic options section -->
			<details class="collapse u-mb10">
				<summary class="collapse-header">
					<i class="fas fa-gear u-mr15"></i>
					<?= _("General") ?>
				</summary>
				<div class="collapse-content">
					<div class="u-mb10">
						<label for="v_app_name" class="form-label">
							<?= _("Application Name") ?>
						</label>
						<input
							type="text"
							class="form-control"
							name="v_app_name"
							id="v_app_name"
							value="<?= htmlentities(trim($v_app_name, "'")) ?>"
						>
					</div>
					<div class="u-mb10">
						<label for="v_title" class="form-label">
							<?= _("Title") ?><span class="optional">(<?= _("Supported variables") ?>: {{appname}}, {{hostname}}, {{ip}} and {{page}} )</span>
						</label>
						<input
							type="text"
							class="form-control"
							name="v_title"
							id="v_title"
							value="<?= htmlentities(trim($v_title, "'")) ?>"
						>
					</div>
					<div class="u-mb10">
						<label for="v_from_name" class="form-label">
							<?= _("Sender Name") ?><span class="optional">(<?= _("Default") ?>: <?= htmlentities(trim($v_app_name, "'")) ?>)</span>
						</label>
						<input
							type="text"
							class="form-control"
							name="v_from_name"
							id="v_from_name"
							value="<?= htmlentities(trim($v_from_name, "'")) ?>"
						>
					</div>

					<div class="u-mb10">
						<label for="v_from_email" class="form-label">
							<?= _("Sender Email Address") ?><span class="optional">(<?= _("Default") ?>: <?= sprintf("noreply@%s", htmlentities(trim(get_hostname(), "'"))) ?>)</span>
						</label>
						<input
							type="text"
							class="form-control"
							name="v_from_email"
							id="v_from_email"
							value="<?= htmlentities(trim($v_from_email, "'")) ?>"
						>
					</div>
					<div class="u-mb10">
						<label for="v_subject_email" class="form-label">
							<?= _("Email Subject") ?><span class="optional">(<?= _("Supported variables") ?>: {{appname}}, {{hostname}}, {{subject}} )</span>
						</label>
						<input
							type="text"
							class="form-control"
							name="v_subject_email"
							id="v_subject_email"
							value="<?= htmlentities(trim($v_subject_email, "'")) ?>"
						>
					</div>
					<div class="u-mb10">
						<label for="v_hide_docs" class="form-label">
							<?= _("Hide link to Documentation") ?>
						</label>
						<select x-model="hide_docs" class="form-select" name="v_hide_docs" id="v_hide_docs">
							<option value="yes"><?= _("Yes") ?></option>
							<option value="no"><?= _("No") ?></option>
						</select>
					</div>
				</div>
			</details>
			<!-- Basic options section -->
			<details class="collapse u-mb10">
				<summary class="collapse-header">
					<i class="fas fa-gear u-mr15"></i>
					<?= _("Custom Logo") ?>
				</summary>
				<div class="collapse-content">
					<div class="u-mb10">
						<label for="v_custom_logo" class="form-label">
							<?= _("Custom logo") ?>
						</label>
					</div>
					<div class="u-mb10">
						<p><?= sprintf(_("Upload the files to %s"), "/usr/local/hestia/web/images/custom/") ?></p>
						<ul>
							<li>logo.svg <small>(100px x 120px)</small></li>
							<li>logo.png <small>(100px x 120px)</small></li>
							<li>logo-header.png <small>(54x x 29px)</small></li>
							<li>favicon.png <small>(64px x 64px)</small></li>
							<li>favicon.ico<<small>(16px x 16px)</small></li>
						</ul>
					</div>
					<div class="u-mb10">
						<input type="checkbox" id="v_update_logo" name="v_update_logo">
						<label for="v_update_logo" class="form-label">
							<?= _("Update logo") ?>
						</label>
					</div>
			</details>
		</div>
	</form>
</div>
<!-- End form -->
