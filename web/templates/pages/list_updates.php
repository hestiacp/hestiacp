<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/server/">
				<i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
			</a>
			<?php
				if ($autoupdate == 'Enabled') {
					$btn_url = '/delete/cron/autoupdate/?token='.$_SESSION['token'].'';
					$btn_icon = 'fa-toggle-on icon-green';
					$btn_label = _('Disable Automatic Updates');
				} else {
					$btn_url = '/add/cron/autoupdate/?token='.$_SESSION['token'].'';
					$btn_icon = 'fa-toggle-off icon-red';
					$btn_label = _('Enable Automatic Updates');
				}
			?>
			<a class="button button-secondary" href="<?= $btn_url ?>">
				<i class="fas <?= $btn_icon ?>"></i><?= $btn_label ?>
			</a>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container">

	<h1 class="u-text-center u-hide-desktop u-mt20 u-pr30 u-mb20 u-pl30"><?= _("Updates") ?></h1>

	<div class="units-table js-units-container">
		<div class="units-table-header">
			<div class="units-table-cell"><?= _("Package Names") ?></div>
			<div class="units-table-cell"><?= _("Description") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Version") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Status") ?></div>
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
			<div class="units-table-row <?php if ($status == 'suspended') echo 'disabled'; ?> js-unit">
				<div class="units-table-cell units-table-heading-cell u-text-bold">
					<span class="u-hide-desktop"><?= _("Package Names") ?>:</span>
					<?= $key ?>
				</div>
				<div class="units-table-cell">
					<span class="u-hide-desktop u-text-bold"><?= _("Description") ?>:</span>
					<?= _($data[$key]["DESCR"]) ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("Version") ?>:</span>
					<?= $data[$key]["VERSION"] ?> (<?= $data[$key]["ARCH"] ?>)
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("Status") ?>:</span>
					<?php if ($data[$key]['UPDATED'] == 'no') { echo '<i class="fas fa-triangle-exclamation" style="color: orange;"></i>'; } ?>
					<?php if ($data[$key]['UPDATED'] == 'yes') { echo '<i class="fas fa-circle-check icon-green"></i>'; } ?>
				</div>
			</div>
		<?php } ?>
	</div>

</div>

<footer class="app-footer">
</footer>
