<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/server/">
				<i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
			</a>
			<?php
				if($autoupdate == 'Enabled') {
					$btn_url = '/delete/cron/autoupdate/?token='.$_SESSION['token'].'';
					$btn_icon = 'fa-toggle-on icon-green';
					$btn_label = _('Disable Automatic Updates');
				} else {
					$btn_url = '/add/cron/autoupdate/?token='.$_SESSION['token'].'';
					$btn_icon = 'fa-toggle-off icon-red';
					$btn_label = _('Enable Automatic Updates');
				}
			?>
			<a class="button button-secondary" href="<?=$btn_url;?>">
				<i class="fas <?=$btn_icon;?>"></i><?= $btn_label; ?>
			</a>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container units">
	<div class="header units-header">
		<div class="l-unit__col l-unit__col--right">
			<div>
				<div class="clearfix l-unit__stat-col--left super-compact center">
					<input type="checkbox" class="js-toggle-all" title="<?= _("Select all") ?>">
				</div>
				<!-- Not a typo, to differentiate from User "Package Name" -->
				<div class="clearfix l-unit__stat-col--left wide"><b><?= _("Package Names") ?></b></div>
				<div class="clearfix l-unit__stat-col--left wide-5"><b><?= _("Description") ?></b></div>
				<div class="clearfix l-unit__stat-col--left u-text-center wide"><b><?= _("Version") ?></b></div>
				<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= _("Status") ?></b></div>
			</div>
		</div>
	</div>

	<!-- Begin update list item loop -->
	<?php
		foreach ($data as $key => $value) {
			++$i;

			if ($data[$key]['UPDATED'] == 'yes') {
				$status = 'active';
				$upd_status = 'updated';
			} else {
				$status = 'suspended';
				$upd_status = 'outdated';
			}
		?>
		<div class="l-unit<?php if ($status == 'suspended') echo ' l-unit--outdated';?> animate__animated animate__fadeIn">
			<div class="l-unit-toolbar clearfix">
				<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
				</div>
			</div>

			<div class="l-unit__col l-unit__col--right">
				<div>
					<div class="clearfix l-unit__stat-col--left super-compact center">
						<input id="check<?= $i ?>" class="ch-toggle" type="checkbox" title="<?= _("Select") ?>" name="pkg[]" value="<?= $key ?>">
					</div>
					<div class="clearfix l-unit__stat-col--left wide"><b><?=$key?></b></div>
					<div class="clearfix l-unit__stat-col--left wide-5"><?=_($data[$key]['DESCR'])?></div>
					<div class="clearfix l-unit__stat-col--left u-text-center wide"><?=$data[$key]['VERSION'] ?> (<?=$data[$key]['ARCH']?>)</div>
					<div class="clearfix l-unit__stat-col--left u-text-center">
						<?php if ($data[$key]['UPDATED'] == 'no') { echo '<i class="fas fa-triangle-exclamation" style="color: orange;"></i>'; } ?>
						<?php if ($data[$key]['UPDATED'] == 'yes') { echo '<i class="fas fa-circle-check icon-green"></i>'; } ?>
					</div>
				</div>
			</div>
		</div>
	<?php } ?>

</div>

<footer class="app-footer">
</footer>
