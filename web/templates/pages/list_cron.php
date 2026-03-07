<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<?php if ($read_only !== "true") { ?>
				<a href="/add/cron/" class="button button-secondary js-button-create">
					<i class="fas fa-circle-plus icon-green"></i><?= tohtml( _("Add Cron Job")) ?>
				</a>
				<?php if ($panel[$user_plain]["CRON_REPORTS"] == "yes") { ?>
					<a class="button button-secondary" href="/delete/cron/reports/?token=<?= tohtml($_SESSION["token"]) ?>">
						<i class="fas fa-toggle-on icon-green"></i><?= tohtml( _("Disable Notifications")) ?>
					</a>
				<?php } else { ?>
					<a class="button button-secondary" href="/add/cron/reports/?token=<?= tohtml($_SESSION["token"]) ?>">
						<i class="fas fa-toggle-off"></i><?= tohtml( _("Enable Notifications")) ?>
					</a>
				<?php } ?>
			<?php } ?>
		</div>
		<div class="toolbar-right">
			<div class="toolbar-sorting">
				<button class="toolbar-sorting-toggle js-toggle-sorting-menu" type="button" title="<?= tohtml( _("Sort items")) ?>">
					<?= tohtml( _("Sort by")) ?>:
					<span class="u-text-bold">
						<?php if ($_SESSION['userSortOrder'] === 'name') { $label = _('Command'); } else { $label = _('Date'); } ?>
						<?= tohtml($label) ?> <i class="fas fa-arrow-down-a-z"></i>
					</span>
				</button>
				<ul class="toolbar-sorting-menu js-sorting-menu u-hidden">
					<li data-entity="sort-name">
						<span class="name <?php if ($_SESSION['userSortOrder'] === 'name') { echo 'active'; } ?>"><?= tohtml( _("Command")) ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-date" data-sort-as-int="1">
						<span class="name <?php if ($_SESSION['userSortOrder'] === 'date') { echo 'active'; } ?>"><?= tohtml( _("Date")) ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
				</ul>
				<?php if ($read_only !== "true") { ?>
					<form x-data x-bind="BulkEdit" action="/bulk/cron/" method="post">
						<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
						<select class="form-select" name="action">
							<option value=""><?= tohtml( _("Apply to selected")) ?></option>
							<?php if ($panel[$user_plain]['CRON_REPORTS'] == 'yes') echo '<option value="delete-cron-reports">' . _('Disable Notifications') . '</option>'; ?>
							<?php if ($panel[$user_plain]['CRON_REPORTS'] == 'no') echo '<option value="add-cron-reports">' . _('Enable Notifications') . '</option>'; ?>
							<option value="suspend"><?= tohtml( _("Suspend")) ?></option>
							<option value="unsuspend"><?= tohtml( _("Unsuspend")) ?></option>
							<option value="delete"><?= tohtml( _("Delete")) ?></option>
						</select>
						<button type="submit" class="toolbar-input-submit" title="<?= tohtml( _("Apply to selected")) ?>">
							<i class="fas fa-arrow-right"></i>
						</button>
					</form>
				<?php } ?>
			</div>
			<div class="toolbar-search">
				<form action="/search/" method="get">
					<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
					<input type="search" class="form-control js-search-input" name="q" value="<?= tohtml($_POST['q'] ?? '') ?>" title="<?= tohtml( _("Search")) ?>">
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

	<h1 class="u-text-center u-hide-desktop u-mt20 u-pr30 u-mb20 u-pl30"><?= tohtml( _("Cron Jobs")) ?></h1>

	<div class="units-table js-units-container">
		<div class="units-table-header">
			<div class="units-table-cell">
				<input type="checkbox" class="js-toggle-all-checkbox" title="<?= tohtml( _("Select all")) ?>" <?= tohtml($display_mode) ?>>
			</div>
			<div class="units-table-cell"><?= tohtml( _("Command")) ?></div>
			<div class="units-table-cell"></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("Minute")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("Hour")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("Day")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("Month")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("Day of Week")) ?></div>
		</div>

		<!-- Begin cron job list item loop -->
		<?php
		foreach ($data as $key => $value) {
			++$i;
			if ($data[$key]['SUSPENDED'] == 'yes') {
				$status = 'suspended';
				$spnd_action = 'unsuspend';
				$spnd_action_title = _('Unsuspend');
				$spnd_icon = 'fa-play';
				$spnd_icon_class = 'icon-green';
				$spnd_confirmation = _('Are you sure you want to unsuspend this cron job?') ;
			} else {
				$status = 'active';
				$spnd_action = 'suspend';
				$spnd_action_title = _('Suspend');
				$spnd_icon = 'fa-pause';
				$spnd_icon_class = 'icon-highlight';
				$spnd_confirmation = _('Are you sure you want to suspend this cron job?') ;
			}
		?>
			<div class="units-table-row <?php if ($status == 'suspended') echo 'disabled'; ?> js-unit"
				data-sort-date="<?= tohtml(strtotime($data[$key]['DATE'].' '.$data[$key]['TIME'])) ?>"
				data-sort-name="<?= tohtml($data[$key]['CMD']) ?>">
				<div class="units-table-cell">
					<div>
						<input id="check<?= tohtml($i) ?>" class="js-unit-checkbox" type="checkbox" title="<?= tohtml( _("Select")) ?>" name="job[]" value="<?= tohtml($key) ?>" <?= tohtml($display_mode) ?>>
						<label for="check<?= tohtml($i) ?>" class="u-hide-desktop"><?= tohtml( _("Select")) ?></label>
					</div>
				</div>
				<div class="units-table-cell units-table-heading-cell u-text-bold">
					<span class="u-hide-desktop"><?= tohtml( _("Command")) ?>:</span>
					<?php if ($read_only === "true" || $data[$key]["SUSPENDED"] == "yes") { ?>
						<?= tohtml($data[$key]["CMD"]) ?>
					<?php } else { ?>
						<a href="/edit/cron/?job=<?= tohtml($data[$key]["JOB"]) ?>&token=<?= tohtml($_SESSION["token"]) ?>" title="<?= tohtml( _("Edit Cron Job")) ?>: <?= tohtml($data[$key]["CMD"]) ?>">
							<?= tohtml($data[$key]["CMD"]) ?>
						</a>
					<?php } ?>
				</div>
				<div class="units-table-cell">
					<?php if (!$read_only) { ?>
						<ul class="units-table-row-actions">
							<?php if ($data[$key]["SUSPENDED"] == "no") { ?>
								<li class="units-table-row-action shortcut-enter" data-key-action="href">
									<a
										class="units-table-row-action-link"
										href="/edit/cron/?job=<?= tohtml($data[$key]["JOB"]) ?>&token=<?= tohtml($_SESSION["token"]) ?>"
										title="<?= tohtml( _("Edit")) ?>"
									>
										<i class="fas fa-pencil icon-orange"></i>
										<span class="u-hide-desktop"><?= tohtml( _("Edit")) ?></span>
									</a>
								</li>
							<?php } ?>
							<li class="units-table-row-action shortcut-s" data-key-action="js">
								<a
									class="units-table-row-action-link data-controls js-confirm-action"
									href="/<?= tohtml($spnd_action) ?>/cron/?job=<?= tohtml($data[$key]["JOB"]) ?>&token=<?= tohtml($_SESSION["token"]) ?>"
									title="<?= tohtml($spnd_action_title) ?>"
									data-confirm-title="<?= tohtml($spnd_action_title) ?>"
									data-confirm-message="<?= tohtml(sprintf($spnd_confirmation, $key)) ?>"
								>
									<i class="fas <?= tohtml($spnd_icon) ?> <?= tohtml($spnd_icon_class) ?>"></i>
									<span class="u-hide-desktop"><?= tohtml($spnd_action_title) ?></span>
								</a>
							</li>
							<li class="units-table-row-action shortcut-delete" data-key-action="js">
								<a
									class="units-table-row-action-link data-controls js-confirm-action"
									href="/delete/cron/?job=<?= tohtml($data[$key]["JOB"]) ?>&token=<?= tohtml($_SESSION["token"]) ?>"
									title="<?= tohtml( _("Delete")) ?>"
									data-confirm-title="<?= tohtml( _("Delete")) ?>"
									data-confirm-message="<?= tohtml(sprintf(_("Are you sure you want to delete this cron job?"), $key)) ?>"
								>
									<i class="fas fa-trash icon-red"></i>
									<span class="u-hide-desktop"><?= tohtml( _("Delete")) ?></span>
								</a>
							</li>
						</ul>
					<?php } ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("Minute")) ?>:</span>
					<?= tohtml($data[$key]["MIN"]) ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("Hour")) ?>:</span>
					<?= tohtml($data[$key]["HOUR"]) ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("Day")) ?>:</span>
					<?= tohtml($data[$key]["DAY"]) ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("Month")) ?>:</span>
					<?= tohtml($data[$key]["MONTH"]) ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("Day of Week")) ?>:</span>
					<?= tohtml($data[$key]["WDAY"]) ?>
				</div>
			</div>
		<?php } ?>
	</div>

	<div class="units-table-footer">
		<p>
			<?php printf(ngettext("%d cron job", "%d cron jobs", $i), $i); ?>
		</p>
	</div>

</div>
