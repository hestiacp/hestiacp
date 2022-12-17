<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary" id="btn-back" href="/add/webapp/?domain=<?= htmlentities($v_domain) ?>">
				<i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
			</a>
		</div>
		<div class="toolbar-buttons">
			<?php
				if (!empty($_SESSION['error_msg'])) {
					echo "<p class=\"inline-alert inline-alert-danger\"> → ".htmlentities($_SESSION['error_msg'])."</p>";
				} else {
					if (!empty($_SESSION['ok_msg'])) {
						echo "<p class=\"inline-alert inline-alert-success\"> → ".$_SESSION['ok_msg']."</p>";
					}
				}
			?>
			<button class="button" type="submit" form="vstobjects">
				<i class="fas fa-floppy-disk icon-purple"></i><?= _("Save") ?>
			</button>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container animate__animated animate__fadeIn">

	<?php if (!empty($WebappInstaller->getOptions())): ?>
		<form id="vstobjects" method="POST" name="v_setup_webapp">
			<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
			<input type="hidden" name="ok" value="true">

			<div class="form-container">
				<h1 class="form-title"><?= sprintf(_("Install %s"), $WebappInstaller->info()["name"]) ?></h1>
				<?php if (!$WebappInstaller->isDomainRootClean()): ?>
					<div class="alert alert-info" role="alert">
						<i class="fas fa-info"></i>
						<div>
							<p class="u-mb10"><?= _("Data loss warning!") ?></p>
							<p class="u-mb10"><?= _("Your web folder already has files uploaded to it. The installer will overwrite your files and / or the installation might fail.") ?></p>
							<p><?php echo sprintf(_("Please make sure ~/web/%s/public_html is empty!"), $v_domain); ?></p>
						</div>
					</div>
				<?php endif; ?>
				<div class="u-mt20">
					<?php foreach ($WebappInstaller->getOptions() as $form_name => $form_control): ?>
						<?php
							$f_name = $WebappInstaller->formNs() . '_' . $form_name;
							$f_type = $form_control;
							$f_value = '';
							if (isset($form_control['label'])) {
								$f_label = htmlentities($form_control['label']);
							} else {
								$f_label = ucwords(str_replace(['.','_'], ' ', $form_name));
							}
							$f_placeholder = '';
							if (is_array($form_control)) {
								$f_type = (!empty($form_control['type']))?$form_control['type']:'text';
								$f_value = (!empty($form_control['value']))?$form_control['value']:'';
								$f_placeholder = (!empty($form_control['placeholder']))?$form_control['placeholder']:'';
							}

							$f_value = htmlentities($f_value);
							$f_label = htmlentities($f_label);
							$f_name = htmlentities($f_name);
							$f_placeholder = htmlentities($f_placeholder);
						?>
						<div class="u-mb10">
							<?php if ($f_type != "boolean"): ?>
								<label for="<?= $f_name ?>" class="form-label">
									<?= $f_label ?>
									<?php if ($f_type === "password"): ?> / <a href="javascript:applyRandomStringToTarget('<?= $f_name ?>');" class="form-link"><?= _("generate") ?></a> <?php endif; ?>
								</label>
							<?php endif; ?>
							<?php if (in_array($f_type, ['select']) && count($form_control['options']) ):?>
								<select class="form-select" name="<?=$f_name?>" id="<?=$f_name?>">
									<?php foreach ($form_control['options'] as $key => $option){
										if(is_numeric($key)){
											$key = $option;
										}
									?>
										<?php $selected = (!empty($form_control['value']) && $key == $form_control['value'])?'selected':''?>
										<option value="<?=$key?>" <?=$selected?>><?=htmlentities($option)?></option>
									<?php }; ?>
								</select>
							<?php elseif (in_array($f_type, ["boolean"])): ?>
								<div class="form-check">
									<?php $checked = !empty($f_value) ? "checked" : ""; ?>
									<input class="form-check-input" type="checkbox" name="<?= $f_name ?>" id="<?= $f_name ?>" <?= $checked ?> value="true">
									<label for="<?= $f_name ?>"><?= $f_label ?></label>
								</div>
							<?php else: ?>
								<input type="text" class="form-control" name="<?= $f_name ?>" id="<?= $f_name ?>" placeholder="<?= $f_placeholder ?>" value="<?= $f_value ?>">
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</form>
	<?php endif; ?>
</div>
