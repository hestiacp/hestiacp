<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/user/">
				<i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
			</a>
			<a href="/add/package/" class="button button-secondary js-button-create">
				<i class="fas fa-circle-plus icon-green"></i><?= _("Add Package") ?>
			</a>
		</div>
		<div class="toolbar-right">
			<div class="toolbar-sorting">
				<button class="toolbar-sorting-toggle js-toggle-sorting-menu" type="button" title="<?= _("Sort items") ?>">
					<?= _("Sort by") ?>:
					<span class="u-text-bold">
						<?php if ($_SESSION['userSortOrder'] === 'name') { $label = _('Name'); } else { $label = _('Date'); } ?>
						<?= $label ?> <i class="fas fa-arrow-down-a-z"></i>
					</span>
				</button>
				<ul class="toolbar-sorting-menu js-sorting-menu u-hidden">
					<li data-entity="sort-date" data-sort-as-int="1">
						<span class="name <?php if ($_SESSION['userSortOrder'] === 'date') { echo 'active'; } ?>"><?= _("Date") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-name">
						<span class="name <?php if ($_SESSION['userSortOrder'] === 'name') { echo 'active'; } ?>"><?= _("Name") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
				</ul>
				<form x-data x-bind="BulkEdit" action="/bulk/package/" method="post">
					<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
					<select class="form-select" name="action">
						<option value=""><?= _("Apply to selected") ?></option>
						<option value="delete"><?= _("Delete") ?></option>
					</select>
					<button type="submit" class="toolbar-input-submit" title="<?= _("Apply to selected") ?>">
						<i class="fas fa-arrow-right"></i>
					</button>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container">

	<h1 class="u-text-center u-hide-desktop u-mt20 u-pr30 u-mb20 u-pl30"><?= _("Packages") ?></h1>

	<div class="units-table js-units-container">
		<div class="units-table-header">
			<div class="units-table-cell">
				<input type="checkbox" class="js-toggle-all-checkbox" title="<?= _("Select all") ?>">
			</div>
			<div class="units-table-cell"><?= _("Package") ?></div>
			<div class="units-table-cell"></div>
			<div class="units-table-cell u-text-center">
				<i class="fas fa-terminal" title="<?= _("Shell") ?>"></i>
				<span class="u-hidden-visually"><?= _("Shell") ?></span>
			</div>
			<div class="units-table-cell u-text-center">
				<i class="fas fa-hard-drive" title="<?= _("Quota") ?>"></i>
				<span class="u-hidden-visually"><?= _("Quota") ?></span>
			</div>
			<div class="units-table-cell u-text-center">
				<i class="fas fa-right-left" title="<?= _("Bandwidth") ?>"></i>
				<span class="u-hidden-visually"><?= _("Bandwidth") ?></span>
			</div>
			<div class="units-table-cell compact u-text-center">
				<i class="fas fa-earth-americas" title="<?= _("Web Domains") ?>"></i>
				<span class="u-hidden-visually"><?= _("Web Domains") ?></span>
			</div>
			<div class="units-table-cell compact u-text-center">
				<i class="fas fa-link" title="<?= _("Web Aliases") ?>"></i>
				<span class="u-hidden-visually"><?= _("Web Aliases") ?></span>
			</div>
			<div class="units-table-cell compact u-text-center">
				<i class="fas fa-book-atlas" title="<?= _("DNS Zones") ?>"></i>
				<span class="u-hidden-visually"><?= _("DNS Zones") ?></span>
			</div>
			<div class="units-table-cell compact u-text-center">
				<i class="fas fa-globe" title="<?= _("DNS Records") ?>"></i>
				<span class="u-hidden-visually"><?= _("DNS Records") ?></span>
			</div>
			<div class="units-table-cell compact u-text-center">
				<i class="fas fa-envelopes-bulk" title="<?= _("Mail Domains") ?>"></i>
				<span class="u-hidden-visually"><?= _("Mail Domains") ?></span>
			</div>
			<div class="units-table-cell compact u-text-center">
				<i class="fas fa-inbox" title="<?= _("Mail Accounts") ?>"></i>
				<span class="u-hidden-visually"><?= _("Mail Accounts") ?></span>
			</div>
			<div class="units-table-cell compact u-text-center">
				<i class="fas fa-database" title="<?= _("Databases") ?>"></i>
				<span class="u-hidden-visually"><?= _("Databases") ?></span>
			</div>
			<div class="units-table-cell compact u-text-center">
				<i class="fas fa-clock" title="<?= _("Cron Jobs") ?>"></i>
				<span class="u-hidden-visually"><?= _("Cron Jobs") ?></span>
			</div>
			<div class="units-table-cell compact u-text-center">
				<i class="fas fa-file-zipper" title="<?= _("Backups") ?>"></i>
				<span class="u-hidden-visually"><?= _("Backups") ?></span>
			</div>
		</div>

		<!-- Begin package list item loop -->
		<?php
			foreach ($data as $key => $value) {
				++$i;
			?>
			<div class="units-table-row js-unit"
				data-sort-date="<?= strtotime($data[$key]["DATE"] . " " . $data[$key]["TIME"]) ?>"
				data-sort-name="<?= $key ?>"
				data-sort-bandwidth="<?= $data[$key]["BANDWIDTH"] ?>"
				data-sort-disk="<?= $data[$key]["DISK_QUOTA"] ?>">
				<div class="units-table-cell">
					<div>
						<input id="check<?= $i ?>" class="js-unit-checkbox" type="checkbox" title="<?= _("Select") ?>" name="package[]" value="<?= $key ?>">
						<label for="check<?= $i ?>" class="u-hide-desktop"><?= _("Select") ?></label>
					</div>
				</div>
				<div class="units-table-cell units-table-heading-cell u-text-bold">
					<span class="u-hide-desktop"><?= _("Package") ?>:</span>
					<?php if ($key == "system") { ?>
						<?= $key ?>
					<?php } else { ?>
						<a href="/edit/package/?package=<?= $key ?>&token=<?= $_SESSION["token"] ?>" title="<?= _("Edit Package") ?>: <?= $key ?>">
							<?= $key ?>
						</a>
					<?php } ?>
				</div>
				<div class="units-table-cell">
					<ul class="units-table-row-actions">
						<?php if ($key != "system") { ?>
							<li class="units-table-row-action shortcut-enter" data-key-action="href">
								<a
									class="units-table-row-action-link"
									href="/edit/package/?package=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
									title="<?= _("Edit Package") ?>"
								>
									<i class="fas fa-pencil icon-orange"></i>
									<span class="u-hide-desktop"><?= _("Edit Package") ?></span>
								</a>
							</li>
						<?php } ?>
						<li class="units-table-row-action" data-key-action="href">
							<a
								class="units-table-row-action-link"
								href="/copy/package/?package=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
								title="<?= _("Duplicate") ?>"
							>
								<i class="fas fa-clone icon-teal"></i>
								<span class="u-hide-desktop"><?= _("Duplicate") ?></span>
							</a>
						</li>
						<?php if ($key != "system") { ?>
							<li class="units-table-row-action shortcut-delete" data-key-action="js">
								<a
									class="units-table-row-action-link data-controls js-confirm-action"
									href="/delete/package/?package=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
									title="<?= _("Delete") ?>"
									data-confirm-title="<?= _("Delete") ?>"
									data-confirm-message="<?= sprintf(_("Are you sure you want to delete package %s?"), $key) ?>"
								>
									<i class="fas fa-trash icon-red"></i>
									<span class="u-hide-desktop"><?= _("Delete") ?></span>
								</a>
							</li>
						<?php } ?>
					</ul>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("Shell") ?>:</span>
					<?php if ($data[$key]["SHELL"] == "nologin") { ?>
						<i class="fas fa-circle-minus icon-large" title="<?= _("SSH Access") ?>: <?= $data[$key]["SHELL"] ?>"> </i>
					<?php } else { ?>
						<i class="fas fa-circle-check icon-green icon-large"></i>
					<?php } ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("Quota") ?>:</span>
					<span title="<?= _("Quota") ?>: <?= humanize_usage_size($data[$key]["DISK_QUOTA"]) ?> <?= humanize_usage_measure($data[$key]["DISK_QUOTA"]) ?>">
						<?php if (preg_match("/[a-z]/i", $data[$key]["DISK_QUOTA"])): ?>
							<span class="u-text-bold">
								&infin;
							</span>
						<?php else: ?>
							<span class="u-text-bold">
								<?= humanize_usage_size($data[$key]["DISK_QUOTA"]) ?>
							</span>
							<span class="u-text-small">
								<?= humanize_usage_measure($data[$key]["DISK_QUOTA"]) ?>
							</span>
						<?php endif; ?>
					</span>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("Bandwidth") ?>:</span>
					<span title="<?= _("Bandwidth") ?>: <?= humanize_usage_size($data[$key]["BANDWIDTH"]) ?> <?= humanize_usage_measure($data[$key]["BANDWIDTH"]) ?>">
						<?php if ($data[$key]["BANDWIDTH"] == "unlimited") { ?>
							<span class="u-text-bold">
								&infin;
							</span>
						<?php } else { ?>
							<span class="u-text-bold">
								<?= humanize_usage_size($data[$key]["BANDWIDTH"]) ?>
							</span>
							<span class="u-text-small">
								<?= humanize_usage_measure($data[$key]["BANDWIDTH"]) ?>
							</span>
						<?php } ?>
					</span>
				</div>
				<div class="units-table-cell compact u-text-bold u-text-center-desktop">
					<span class="u-hide-desktop"><?= _("Web Domains") ?>:</span>
					<span class="units-table-badge" title="<?= _("Web Domains") ?>: <?= $data[$key]["WEB_DOMAINS"] ?>">
						<?php if ($data[$key]["WEB_DOMAINS"] == "unlimited") { ?>
							&infin;
						<?php } else { ?>
							<?= $data[$key]["WEB_DOMAINS"] ?>
						<?php } ?>
					</span>
				</div>
				<div class="units-table-cell compact u-text-bold u-text-center-desktop">
					<span class="u-hide-desktop"><?= _("Web Aliases") ?>:</span>
					<span class="units-table-badge" title="<?= _("Web Aliases") ?>: <?= $data[$key]["WEB_ALIASES"] ?>">
						<?php if ($data[$key]["WEB_ALIASES"] == "unlimited") { ?>
							&infin;
						<?php } else { ?>
							<?= $data[$key]["WEB_ALIASES"] ?>
						<?php } ?>
					</span>
				</div>
				<div class="units-table-cell compact u-text-bold u-text-center-desktop">
					<span class="u-hide-desktop"><?= _("DNS Zones") ?>:</span>
					<span class="units-table-badge" title="<?= _("DNS Zones") ?>: <?= $data[$key]["DNS_DOMAINS"] ?>">
						<?php if ($data[$key]["DNS_DOMAINS"] == "unlimited") { ?>
							&infin;
						<?php } else { ?>
							<?= $data[$key]["DNS_DOMAINS"] ?>
						<?php } ?>
					</span>
				</div>
				<div class="units-table-cell compact u-text-bold u-text-center-desktop">
					<span class="u-hide-desktop"><?= _("DNS Records") ?>:</span>
					<span class="units-table-badge" title="<?= _("DNS Records") ?>: <?= $data[$key]["DNS_RECORDS"] ?>">
						<?php if ($data[$key]["DNS_RECORDS"] == "unlimited") { ?>
							&infin;
						<?php } else { ?>
							<?= $data[$key]["DNS_RECORDS"] ?>
						<?php } ?>
					</span>
				</div>
				<div class="units-table-cell compact u-text-bold u-text-center-desktop">
					<span class="u-hide-desktop"><?= _("Mail Domains") ?>:</span>
					<span class="units-table-badge" title="<?= _("Mail Domains") ?>: <?= $data[$key]["MAIL_DOMAINS"] ?>">
						<?php if ($data[$key]["MAIL_DOMAINS"] == "unlimited") { ?>
							&infin;
						<?php } else { ?>
							<?= $data[$key]["MAIL_DOMAINS"] ?>
						<?php } ?>
					</span>
				</div>
				<div class="units-table-cell compact u-text-bold u-text-center-desktop">
					<span class="u-hide-desktop"><?= _("Mail Accounts") ?>:</span>
					<span class="units-table-badge" title="<?= _("Mail Accounts") ?>: <?= $data[$key]["MAIL_ACCOUNTS"] ?>">
						<?php if ($data[$key]["MAIL_ACCOUNTS"] == "unlimited") { ?>
							&infin;
						<?php } else { ?>
							<?= $data[$key]["MAIL_ACCOUNTS"] ?>
						<?php } ?>
					</span>
				</div>
				<div class="units-table-cell compact u-text-bold u-text-center-desktop">
					<span class="u-hide-desktop"><?= _("Databases") ?>:</span>
					<span class="units-table-badge" title="<?= _("Databases") ?>: <?= $data[$key]["DATABASES"] ?>">
						<?php if ($data[$key]["DATABASES"] == "unlimited") { ?>
							&infin;
						<?php } else { ?>
							<?= $data[$key]["DATABASES"] ?>
						<?php } ?>
					</span>
				</div>
				<div class="units-table-cell compact u-text-bold u-text-center-desktop">
					<span class="u-hide-desktop"><?= _("Cron Jobs") ?>:</span>
					<span class="units-table-badge" title="<?= _("Cron Jobs") ?>: <?= $data[$key]["CRON_JOBS"] ?>">
						<?php if ($data[$key]["CRON_JOBS"] == "unlimited") { ?>
							&infin;
						<?php } else { ?>
							<?= $data[$key]["CRON_JOBS"] ?>
						<?php } ?>
					</span>
				</div>
				<div class="units-table-cell compact u-text-bold u-text-center-desktop">
					<span class="u-hide-desktop"><?= _("Backups") ?>:</span>
					<span class="units-table-badge" title="<?= _("Backups") ?>: <?= $data[$key]["BACKUPS"] ?>">
						<?php if ($data[$key]["BACKUPS"] == "unlimited") { ?>
							&infin;
						<?php } else { ?>
							<?= $data[$key]["BACKUPS"] ?>
						<?php } ?>
					</span>
				</div>
			</div>
		<?php } ?>
	</div>

</div>

<footer class="app-footer">
	<div class="container app-footer-inner">
		<p>
			<?php printf(ngettext("%d package", "%d packages", $i), $i); ?>
		</p>
	</div>
</footer>
