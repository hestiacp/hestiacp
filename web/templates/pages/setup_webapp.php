<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/add/webapp/?domain=<?= htmlentities($v_domain) ?>">
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

	<?php if (!empty($WebappInstaller->getOptions())) { ?>
		<form id="main-form" method="POST" name="v_setup_webapp">
			<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
			<input type="hidden" name="ok" value="true">

			<div class="form-container">
				<h1 class="u-mb20"><?= sprintf(_("Install %s"), $WebappInstaller->info()["name"]) ?></h1>
				<?php show_alert_message($_SESSION); ?>
				<?php if (!$WebappInstaller->isDomainRootClean()) { ?>
					<div class="alert alert-info u-mb10" role="alert">
						<i class="fas fa-info"></i>
						<div>
							<p class="u-mb10"><?= _("Data Loss Warning!") ?></p>
							<p class="u-mb10"><?= _("Your web folder already has files uploaded to it. The installer will overwrite your files and/or the installation might fail.") ?></p>
							<p><?php echo sprintf(_("Please make sure ~/web/%s/public_html is empty!"), $v_domain); ?></p>
						</div>
					</div>
				<?php } ?>
				<?php foreach ($WebappInstaller->getOptions() as $form_name => $form_control) {
					$field_name = $WebappInstaller->formNs() . "_" . $form_name;
					$field_type = $form_control;
					$field_value = "";
					$field_label =
						isset($form_control["label"])
							? htmlentities($form_control["label"])
							: ucwords(str_replace([".","_"], " ", $form_name));
					$field_placeholder = "";
					if (is_array($form_control)) {
						$field_type = !empty($form_control["type"]) ? $form_control["type"] : "text";
						$field_value = !empty($form_control["value"]) ? $form_control["value"] : "";
						$field_placeholder = !empty($form_control["placeholder"]) ? $form_control["placeholder"] : "";
					}
					$field_value = htmlentities($field_value);
					$field_label = htmlentities($field_label);
					$field_name = htmlentities($field_name);
					$field_placeholder = htmlentities($field_placeholder);
				?>
					<div class="u-mb10">
						<?php if ($field_type != "boolean"): ?>
							<label for="<?= $field_name ?>" class="form-label">
								<?= $field_label ?>
								<?php if ($field_type == "password"): ?>
									<button type="button" title="<?= _("Generate") ?>" class="u-unstyled-button u-ml5 js-generate-password">
										<i class="fas fa-arrows-rotate icon-green"></i>
									</button>
								<?php endif; ?>
							</label>
						<?php endif; ?>

						<?php if ($field_type == "select" && count($form_control["options"])): ?>
							<select class="form-select" name="<?= $field_name ?>" id="<?= $field_name ?>">
								<?php foreach ($form_control["options"] as $key => $option):
									$key = !is_numeric($key) ? $key : $option;
									$selected = !empty($form_control["value"] && $key == $form_control["value"]) ? "selected" : ""; ?>
									<option value="<?= $key ?>" <?= $selected ?>>
										<?= htmlentities($option) ?>
									</option>
								<?php endforeach; ?>
							</select>
						<?php elseif ($field_type == "boolean"):
							$checked = !empty($field_value) ? "checked" : ""; ?>
							<div class="form-check">
								<input
									class="form-check-input"
									type="checkbox"
									name="<?= $field_name ?>"
									id="<?= $field_name ?>"
									value="true"
									<?= $checked ?>
								>
								<label for="<?= $field_name ?>">
									<?= $field_label ?>
								</label>
							</div>
						<?php else: ?>
							<?php if ($field_type == "password"): ?>
								<div class="u-pos-relative">
									<input
										type="text"
										class="form-control js-password-input"
										name="<?= $field_name ?>"
										id="<?= $field_name ?>"
										placeholder="<?= $field_placeholder ?>"
									>
									<div class="password-meter">
										<meter max="4" class="password-meter-input js-password-meter"></meter>
									</div>
								</div>
							<?php else: ?>
								<input
									type="text"
									class="form-control"
									name="<?= $field_name ?>"
									id="<?= $field_name ?>"
									placeholder="<?= $field_placeholder ?>"
								>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				<?php } ?>
			</div>
		</form>
	<?php } ?>
</div>
<!-- End form -->
