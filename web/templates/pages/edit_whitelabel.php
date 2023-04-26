<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a href="/list/server/" class="button button-secondary" id="btn-back">
				<i class="fas fa-arrow-left icon-blue"></i>
				<?= _("Back") ?>
			</a>
			<a href="/list/ip/" class="button button-secondary">
				<i class="fas fa-ethernet icon-blue"></i>
				<?= _("IP") ?>
			</a>
			<?php if (isset($_SESSION["FIREWALL_SYSTEM"]) && !empty($_SESSION["FIREWALL_SYSTEM"])) { ?>
				<a href="/list/firewall/" class="button button-secondary">
					<i class="fas fa-shield-halved icon-red"></i>
					<?= _("Firewall") ?>
				</a>
			<?php } ?>
		</div>
		<div class="toolbar-buttons">
			<button type="submit" class="button" form="vstobjects">
				<i class="fas fa-floppy-disk icon-purple"></i>
				<?= _("Save") ?>
			</button>
		</div>
	</div>
</div>
<!-- End toolbar -->

<!-- Begin form -->
<div class="container animate__animated animate__fadeIn">
	<form
		x-data="{
			hide_docs: '<?= $v_hide_docs ?? "no" ?>',
		}"
		id="vstobjects"
		name="v_configure_server"
		method="post"
	>
		<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
		<input type="hidden" name="save" value="save">

		<div class="form-container">
			<h1 class="form-title">
				<?= _("White label options") ?>
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
						<label for="v_hostname" class="form-label">
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
							<?= _("Title") ?><span class="optional">Supported vars: {{appname}}, {{hostname}}, {{ip}} and {{page}}</span>
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
							<?= _("Sender Name") ?><span class="optional"><?=sprintf('Default: %s', htmlentities(trim($v_app_name, "'")));?></span>
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
							<?= _("Sender email adress") ?><span class="optional"><?=sprintf('Default: noreply@%s', htmlentities(trim(get_hostname(), "'")));?></span>
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
							<?= _("Subject Email") ?><span class="optional">Supported vars: {{appname}}, {{hostname}}, {{subject}}</span>
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
						<label for="v_timezone" class="form-label">
							<?= _("Hide link to Documentation") ?>
						</label>
						<select x-model="hide_docs" class="form-select" name="v_hide_docs" id="v_hide_docs">
							<option value="yes"><?=_('Hide Documentation Link');?></option>
							<option value="no"><?=_('Display Documentation Link');?></option>

						</select>
					</div>
				</div>
			</details>
		</div>
	</form>
</div>
<!-- End form -->
