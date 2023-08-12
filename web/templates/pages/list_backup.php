<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<?php if ($read_only !== "true") { ?>
				<a href="/schedule/backup/?token=<?= $_SESSION["token"] ?>" class="button button-secondary"><i class="fas fa-circle-plus icon-green"></i><?= _("Create Backup") ?></a>
				<a href="/list/backup/exclusions/" class="button button-secondary"><i class="fas fa-folder-minus icon-orange"></i><?= _("Backup Exclusions") ?></a>
			<?php } ?>
		</div>
		<div class="toolbar-right">
			<?php if ($read_only !== "true") { ?>
				<form x-data x-bind="BulkEdit" action="/bulk/backup/" method="post">
					<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
					<select class="form-select" name="action">
						<option value=""><?= _("Apply to selected") ?></option>
						<option value="delete"><?= _("Delete") ?></option>
					</select>
					<button type="submit" class="toolbar-input-submit" title="<?= _("Apply to selected") ?>">
						<i class="fas fa-arrow-right"></i>
					</button>
				</form>
			<?php } ?>
			<div class="toolbar-search">
				<form action="/search/" method="get">
					<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
					<input type="search" class="form-control js-search-input" name="q" value="<? echo isset($_POST['q']) ? htmlspecialchars($_POST['q']) : '' ?>" title="<?= _("Search") ?>">
					<button type="submit" class="toolbar-input-submit" title="<?= _("Search") ?>">
						<i class="fas fa-magnifying-glass"></i>
					</button>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container">

	<h1 class="u-text-center u-hide-desktop u-mt20 u-pr30 u-mb20 u-pl30"><?= _("Backups") ?></h1>

	<div class="units-table js-units-container">
		<div class="units-table-header">
			<div class="units-table-cell">
				<input type="checkbox" class="js-toggle-all-checkbox" title="<?= _("Select all") ?>" <?= $display_mode ?>>
			</div>
			<div class="units-table-cell"><?= _("File Name") ?></div>
			<div class="units-table-cell"></div>
			<div class="units-table-cell u-text-center"><?= _("Date") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Size") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Type") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Runtime") ?></div>
		</div>

		<!-- Begin user backup list item loop -->
		<?php
			foreach ($data as $key => $value) {
				++$i;
				$web = _('No');
				$dns = _('No');
				$mail = _('No');
				$db = _('No');
				$cron = _('No');
				$udir = _('No');

				if (!empty($data[$key]['WEB'])) $web = _('Yes');
				if (!empty($data[$key]['DNS'])) $dns = _('Yes');
				if (!empty($data[$key]['MAIL'])) $mail = _('Yes');
				if (!empty($data[$key]['DB'])) $db = _('Yes');
				if (!empty($data[$key]['CRON'])) $cron = _('Yes');
				if (!empty($data[$key]['UDIR'])) $udir = _('Yes');
		?>
			<div class="units-table-row js-unit">
				<div class="units-table-cell">
					<div>
						<input id="check<?= $i ?>" class="js-unit-checkbox" type="checkbox" title="<?= _("Select") ?>" name="backup[]" value="<?= $key ?>" <?= $display_mode ?>>
						<label for="check<?= $i ?>" class="u-hide-desktop"><?= _("Select") ?></label>
					</div>
				</div>
				<div class="units-table-cell units-table-heading-cell u-text-bold">
					<span class="u-hide-desktop"><?= _("File Name") ?>:</span>
					<?php if ($read_only === "true") { ?>
						<?= $key ?>
					<?php } else { ?>
						<a href="/list/backup/?backup=<?= $key ?>&token=<?= $_SESSION["token"] ?>" title="<?= _("Restore") ?>">
							<?= $key ?>
						</a>
					<?php } ?>
				</div>
				<div class="units-table-cell">
					<?php if (!($_SESSION["userContext"] === "admin" && $_SESSION["look"] === "admin" && $read_only === "true")) { ?>
						<ul class="units-table-row-actions">
							<li class="units-table-row-action shortcut-d" data-key-action="href">
								<a
									class="units-table-row-action-link"
									href="/download/backup/?backup=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
									title="<?= _("Download") ?>"
								>
									<i class="fas fa-file-arrow-down icon-lightblue"></i>
									<span class="u-hide-desktop"><?= _("Download") ?></span>
								</a>
							</li>
							<?php if ($read_only !== "true") { ?>
								<li class="units-table-row-action shortcut-enter" data-key-action="href">
									<a
										class="units-table-row-action-link data-controls"
										href="/list/backup/?backup=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
										title="<?= _("Restore") ?>"
									>
										<i class="fas fa-arrow-rotate-left icon-green"></i>
										<span class="u-hide-desktop"><?= _("Restore") ?></span>
									</a>
								</li>
								<li class="units-table-row-action shortcut-delete" data-key-action="js">
									<a
										class="units-table-row-action-link data-controls js-confirm-action"
										href="/delete/backup/?backup=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
										title="<?= _("Delete") ?>"
										data-confirm-title="<?= _("Delete") ?>"
										data-confirm-message="<?= sprintf(_("Are you sure you want to delete backup %s?"), $key) ?>"
									>
										<i class="fas fa-trash icon-red"></i>
										<span class="u-hide-desktop"><?= _("Delete") ?></span>
									</a>
								</li>
							<?php } ?>
						</ul>
					<?php } ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("Date") ?>:</span>
					<span class="u-text-bold">
						<?= translate_date($data[$key]["DATE"]) ?>
					</span>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("Size") ?>:</span>
					<span class="u-text-bold">
						<?= humanize_usage_size($data[$key]["SIZE"]) ?>
					</span>
					<span class="u-text-small">
						<?= humanize_usage_measure($data[$key]["SIZE"]) ?>
					</span>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("Type") ?>:</span>
					<?= $data[$key]["TYPE"] ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("Runtime") ?>:</span>
					<?= humanize_time($data[$key]["RUNTIME"]) ?>
				</div>
			</div>
		<?php } ?>
	</div>

</div>

<footer class="app-footer">
	<div class="container app-footer-inner">
		<p>
			<?php printf(ngettext("%d backup", "%d backups", $i), $i); ?>
		</p>
	</div>
</footer>
