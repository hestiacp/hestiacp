<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<?php if ($read_only !== "true") { ?>
				<a href="/schedule/backup/?token=<?= tohtml($_SESSION["token"]) ?>" class="button button-secondary"><i class="fas fa-circle-plus icon-green"></i><?= tohtml( _("Create Backup")) ?></a>
				<a href="/list/backup/exclusions/" class="button button-secondary"><i class="fas fa-folder-minus icon-orange"></i><?= tohtml( _("Backup Exclusions")) ?></a>
			<?php } ?>
			<?php if ($panel[$user_plain]['BACKUPS_INCREMENTAL'] === 'yes') { ?>
				<a href="/list/backup/incremental/" class="button button-secondary"><i class="fas fa-vault icon-blue"></i><?= tohtml( _("Incremental Backups")) ?></a>
			<?php } ?>
		</div>
		<div class="toolbar-right">
			<?php if ($read_only !== "true") { ?>
				<form x-data x-bind="BulkEdit" action="/bulk/backup/" method="post">
					<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
					<select class="form-select" name="action">
						<option value=""><?= tohtml( _("Apply to selected")) ?></option>
						<option value="delete"><?= tohtml( _("Delete")) ?></option>
					</select>
					<button type="submit" class="toolbar-input-submit" title="<?= tohtml( _("Apply to selected")) ?>">
						<i class="fas fa-arrow-right"></i>
					</button>
				</form>
			<?php } ?>
			<div class="toolbar-search">
				<form action="/search/" method="get">
					<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
					<input type="search" class="form-control js-search-input" name="q" value="<? echo isset($_POST['q']) ? htmlspecialchars($_POST['q']) : '' ?>" title="<?= tohtml( _("Search")) ?>">
					<button type="submit" class="toolbar-input-submit" title="<?= tohtml( _("Search")) ?>">
						<i class="fas fa-magnifying-glass"></i>
					</button>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container">

	<h1 class="u-text-center u-hide-desktop u-mt20 u-pr30 u-mb20 u-pl30"><?= tohtml( _("Backups")) ?></h1>

	<div class="units-table js-units-container">
		<div class="units-table-header">
			<div class="units-table-cell">
				<input type="checkbox" class="js-toggle-all-checkbox" title="<?= tohtml( _("Select all")) ?>" <?= tohtml($display_mode) ?>>
			</div>
			<div class="units-table-cell"><?= tohtml( _("File Name")) ?></div>
			<div class="units-table-cell"></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("Date")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("Size")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("Type")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("Runtime")) ?></div>
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
						<input id="check<?= tohtml($i) ?>" class="js-unit-checkbox" type="checkbox" title="<?= tohtml( _("Select")) ?>" name="backup[]" value="<?= tohtml($key) ?>" <?= tohtml($display_mode) ?>>
						<label for="check<?= tohtml($i) ?>" class="u-hide-desktop"><?= tohtml( _("Select")) ?></label>
					</div>
				</div>
				<div class="units-table-cell units-table-heading-cell u-text-bold">
					<span class="u-hide-desktop"><?= tohtml( _("File Name")) ?>:</span>
					<?php if ($read_only === "true") { ?>
						<?= tohtml($key) ?>
					<?php } else { ?>
						<a href="/list/backup/?backup=<?= tohtml($key) ?>&token=<?= tohtml($_SESSION["token"]) ?>" title="<?= tohtml( _("Restore")) ?>">
							<?= tohtml($key) ?>
						</a>
					<?php } ?>
				</div>
				<div class="units-table-cell">
					<?php if (!($_SESSION["userContext"] === "admin" && $_SESSION["look"] === "admin" && $read_only === "true")) { ?>
						<ul class="units-table-row-actions">
							<li class="units-table-row-action shortcut-d" data-key-action="href">
								<a
									class="units-table-row-action-link"
									href="/download/backup/?backup=<?= tohtml($key) ?>&token=<?= tohtml($_SESSION["token"]) ?>"
									title="<?= tohtml( _("Download")) ?>"
								>
									<i class="fas fa-file-arrow-down icon-lightblue"></i>
									<span class="u-hide-desktop"><?= tohtml( _("Download")) ?></span>
								</a>
							</li>
							<?php if ($read_only !== "true") { ?>
								<li class="units-table-row-action shortcut-enter" data-key-action="href">
									<a
										class="units-table-row-action-link data-controls"
										href="/list/backup/?backup=<?= tohtml($key) ?>&token=<?= tohtml($_SESSION["token"]) ?>"
										title="<?= tohtml( _("Restore")) ?>"
									>
										<i class="fas fa-arrow-rotate-left icon-green"></i>
										<span class="u-hide-desktop"><?= tohtml( _("Restore")) ?></span>
									</a>
								</li>
								<li class="units-table-row-action shortcut-delete" data-key-action="js">
									<a
										class="units-table-row-action-link data-controls js-confirm-action"
										href="/delete/backup/?backup=<?= tohtml($key) ?>&token=<?= tohtml($_SESSION["token"]) ?>"
										title="<?= tohtml( _("Delete")) ?>"
										data-confirm-title="<?= tohtml( _("Delete")) ?>"
										data-confirm-message="<?= tohtml(sprintf(_("Are you sure you want to delete backup %s?"), $key)) ?>"
									>
										<i class="fas fa-trash icon-red"></i>
										<span class="u-hide-desktop"><?= tohtml( _("Delete")) ?></span>
									</a>
								</li>
							<?php } ?>
						</ul>
					<?php } ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("Date")) ?>:</span>
					<span class="u-text-bold">
						<?= tohtml(translate_date($data[$key]["DATE"])) ?>
					</span>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("Size")) ?>:</span>
					<span class="u-text-bold">
						<?= tohtml(humanize_usage_size($data[$key]["SIZE"])) ?>
					</span>
					<span class="u-text-small">
						<?= tohtml(humanize_usage_measure($data[$key]["SIZE"])) ?>
					</span>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("Type")) ?>:</span>
					<?= tohtml($data[$key]["TYPE"]) ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("Runtime")) ?>:</span>
					<?= tohtml(humanize_time($data[$key]["RUNTIME"])) ?>
				</div>
			</div>
		<?php } ?>
	</div>

	<div class="units-table-footer">
		<p>
			<?php printf(ngettext("%d backup", "%d backups", $i), $i); ?>
		</p>
	</div>

</div>
