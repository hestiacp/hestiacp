<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a href="/add/user/" class="button button-secondary js-button-create">
				<i class="fas fa-circle-plus icon-green"></i><?= _("Add User") ?>
			</a>
			<a href="/list/package/" class="button button-secondary">
				<i class="fas fa-box-open icon-orange"></i><?= _("Packages") ?>
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
					<li data-entity="sort-bandwidth" data-sort-as-int="1">
						<span class="name"><?= _("Bandwidth") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-date" data-sort-as-int="1">
						<span class="name <?php if ($_SESSION['userSortOrder'] === 'date') { echo 'active'; } ?>"><?= _("Date") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-disk" data-sort-as-int="1">
						<span class="name"><?= _("Disk") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-package">
						<span class="name"><?= _("Package") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-name">
						<span class="name <?php if ($_SESSION['userSortOrder'] === 'name') { echo 'active'; } ?>"><?= _("Name") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
				</ul>
				<form x-data x-bind="BulkEdit" action="/bulk/user/" method="post">
					<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
					<select class="form-select" name="action">
						<option value=""><?= _("Apply to selected") ?></option>
						<option value="rebuild"><?= _("Rebuild All") ?></option>
						<option value="rebuild user"><?= _("Rebuild User Profile") ?></option>
						<option value="rebuild web"><?= _("Rebuild Web Domains") ?></option>
						<option value="rebuild dns"><?= _("Rebuild DNS Zones") ?></option>
						<option value="rebuild mail"><?= _("Rebuild Mail Domains") ?></option>
						<option value="rebuild db"><?= _("Rebuild Databases") ?></option>
						<option value="rebuild cron"><?= _("Rebuild Cron Jobs") ?></option>
						<option value="update counters"><?= _("Update Usage Counters") ?></option>
						<option value="suspend"><?= _("Suspend") ?></option>
						<option value="unsuspend"><?= _("Unsuspend") ?></option>
						<option value="delete"><?= _("Delete") ?></option>
					</select>
					<button type="submit" class="toolbar-input-submit" title="<?= _("Apply to selected") ?>">
						<i class="fas fa-arrow-right"></i>
					</button>
				</form>
			</div>
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

	<h1 class="u-text-center u-hide-desktop u-mt20 u-pr30 u-mb20 u-pl30"><?= _("Users") ?></h1>

	<div class="units-table js-units-container">
		<div class="units-table-header">
			<div class="units-table-cell">
				<input type="checkbox" class="js-toggle-all-checkbox" title="<?= _("Select all") ?>">
			</div>
			<div class="units-table-cell"><?= _("Name") ?></div>
			<div class="units-table-cell"></div>
			<div class="units-table-cell u-text-center"><?= _("Package") ?></div>
			<div class="units-table-cell u-text-center"><?= _("IPs") ?></div>
			<div class="units-table-cell u-text-center">
				<i class="fas fa-hard-drive" title="<?= _("Disk") ?>"></i>
				<span class="u-hidden-visually"><?= _("Disk") ?></span>
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
				<i class="fas fa-book-atlas" title="<?= _("DNS Zones") ?>"></i>
				<span class="u-hidden-visually"><?= _("DNS Zones") ?></span>
			</div>
			<div class="units-table-cell compact u-text-center">
				<i class="fas fa-envelopes-bulk" title="<?= _("Mail Domains") ?>"></i>
				<span class="u-hidden-visually"><?= _("Mail Domains") ?></span>
			</div>
			<div class="units-table-cell compact u-text-center">
				<i class="fas fa-database" title="<?= _("Databases") ?>"></i>
				<span class="u-hidden-visually"><?= _("Databases") ?></span>
			</div>
			<div class="units-table-cell compact u-text-center">
				<i class="fas fa-file-zipper" title="<?= _("Backups") ?>"></i>
				<span class="u-hidden-visually"><?= _("Backups") ?></span>
			</div>
		</div>

		<!-- Begin user list item loop -->
		<?php
			foreach ($data as $key => $value) {
				++$i;
				if ($data[$key]['SUSPENDED'] == 'yes') {
					$status = 'suspended';
					$spnd_action = 'unsuspend';
					$spnd_action_title = _('Unsuspend');
					$spnd_icon = 'fa-play';
					$spnd_icon_class = 'icon-green';
					$spnd_confirmation = _('Are you sure you want to unsuspend user %s?');
				} else {
					$status = 'active';
					$spnd_action = 'suspend';
					$spnd_action_title = _('Suspend');
					$spnd_icon = 'fa-pause';
					$spnd_icon_class = 'icon-highlight';
					$spnd_confirmation = _('Are you sure you want to suspend user %s?');
				}
			?>
			<div class="units-table-row <?php if ($status == 'suspended') echo 'disabled'; ?> js-unit <?php if (($_SESSION['POLICY_SYSTEM_HIDE_ADMIN'] === 'yes') && ($_SESSION['user'] !== 'admin') && ($key === 'admin')) { echo 'u-hidden'; } ?>"
				data-sort-date="<?= strtotime($data[$key]['DATE'].' '.$data[$key]['TIME']) ?>"
				data-sort-name="<?= strtolower($key) ?>"
				data-sort-package="<?= strtolower($data[$key]['PACKAGE']) ?>"
				data-sort-bandwidth="<?= $data[$key]["U_BANDWIDTH"] ?>"
				data-sort-disk="<?= $data[$key]["U_DISK"] ?>">
				<div class="units-table-cell">
					<div>
						<input id="check<?= $i ?>" class="js-unit-checkbox" type="checkbox" title="<?= _("Select") ?>" name="user[]" value="<?= $key ?>">
						<label for="check<?= $i ?>" class="u-hide-desktop"><?= _("Select") ?></label>
					</div>
				</div>
				<div class="units-table-cell units-table-heading-cell">
					<span class="u-hide-desktop u-text-bold"><?= _("Name") ?>:</span>
					<?php if ($key == $user_plain) { ?>
						<a href="/edit/user/?user=<?= $key ?>&token=<?= $_SESSION["token"] ?>" title="<?= _("Edit User") ?>">
							<span class="u-text-bold">
								<?= $key ?>
							</span>
							(<?= $data[$key]["NAME"] ?>)
						</a>
					<?php } else { ?>
						<a href="/login/?loginas=<?= $key ?>&token=<?= $_SESSION["token"] ?>" title="<?= _("Log in as") ?> <?= $key ?>">
							<span class="u-text-bold">
								<?= $key ?>
							</span>
							(<?= $data[$key]["NAME"] ?>)
						</a>
					<?php } ?>
					<p class="u-max-width200 u-text-truncate">
						<span class="u-hide-desktop u-text-bold"><?= _("Email") ?>:</span>
						<span title="<?= $data[$key]["CONTACT"] ?>"><?= $data[$key]["CONTACT"] ?></span>
					</p>
				</div>
				<div class="units-table-cell">
					<ul class="units-table-row-actions">
						<?php if ($key == $user_plain) { ?>
							<li class="units-table-row-action">
								<i class="fas fa-user-check" title="<?= $key ?> (<?= $data[$key]["NAME"] ?>)"></i>
								<span class="u-hide-desktop"><?= $key ?> (<?= $data[$key]["NAME"] ?>)</span>
							</li>
						<?php } else { ?>
							<li class="units-table-row-action">
								<a
									class="units-table-row-action-link"
									href="/login/?loginas=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
									title="<?= _("Log in as") ?> <?= $key ?>"
								>
									<i class="fas fa-right-to-bracket icon-green"></i>
									<span class="u-hide-desktop"><?= _("Log in as") ?> <?= $key ?></span>
								</a>
							</li>
						<?php } ?>
						<?php if (!($_SESSION["userContext"] === "admin" && $key == "admin" && $_SESSION["user"] != "admin")) { ?>
							<li class="units-table-row-action shortcut-enter" data-key-action="href">
								<a
									class="units-table-row-action-link"
									href="/edit/user/?user=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
									title="<?= _("Edit User") ?>"
								>
									<i class="fas fa-pencil icon-orange"></i>
									<span class="u-hide-desktop"><?= _("Edit User") ?></span>
								</a>
							</li>
						<?php } ?>
						<?php if (!($key == "admin" || $key == $user_plain)) { ?>
							<li class="units-table-row-action shortcut-s" data-key-action="js">
								<a
									class="units-table-row-action-link data-controls js-confirm-action"
									href="/<?= $spnd_action ?>/user/?user=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
									title="<?= $spnd_action_title ?>"
									data-confirm-title="<?= $spnd_action_title ?>"
									data-confirm-message="<?= sprintf($spnd_confirmation, $key) ?>"
								>
									<i class="fas <?= $spnd_icon ?> <?= $spnd_icon_class ?>"></i>
									<span class="u-hide-desktop"><?= $spnd_action_title ?></span>
								</a>
							</li>
							<li class="units-table-row-action shortcut-delete" data-key-action="js">
								<a
									class="units-table-row-action-link data-controls js-confirm-action"
									href="/delete/user/?user=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
									title="<?= _("Delete") ?>"
									data-confirm-title="<?= _("Delete") ?>"
									data-confirm-message="<?= sprintf(_("Are you sure you want to delete user %s?"), $key) ?>"
								>
									<i class="fas fa-trash icon-red"></i>
									<span class="u-hide-desktop"><?= _("Delete") ?></span>
								</a>
							</li>
						<?php } ?>
					</ul>
				</div>
				<div class="units-table-cell u-text-bold u-text-center-desktop">
					<span class="u-hide-desktop"><?= _("Package") ?>:</span>
					<?php if ($data[$key]["PACKAGE"] === "system") { ?>
						<?= $data[$key]["PACKAGE"] ?>
					<?php } else { ?>
						<a href="/edit/package/?package=<?= $data[$key]["PACKAGE"] ?>&token=<?= $_SESSION["token"] ?>" title="<?= _("Edit Package") ?>">
							<?= $data[$key]["PACKAGE"] ?>
						</a>
					<?php } ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("IPs") ?>:</span>
					<?= $data[$key]["IP_OWNED"] ?>
				</div>
				<div class="units-table-cell u-text-center-desktop u-text-no-wrap">
					<span class="u-hide-desktop u-text-bold"><?= _("Disk") ?>:</span>
					<span class="u-text-bold">
						<?= humanize_usage_size($data[$key]["U_DISK"], 1) ?>
					</span>
					<span class="u-text-small">
						<?= humanize_usage_measure($data[$key]["U_DISK"]) ?>
					</span> /
					<span class="u-text-bold">
						<?= humanize_usage_size($data[$key]["DISK_QUOTA"], 1) ?>
					</span>
					<span class="u-text-small">
						<?= humanize_usage_measure($data[$key]["DISK_QUOTA"]) ?>
					</span>
				</div>
				<div class="units-table-cell u-text-center-desktop u-text-no-wrap">
					<span class="u-hide-desktop u-text-bold"><?= _("Bandwidth") ?>:</span>
					<span class="u-text-bold">
						<?= humanize_usage_size($data[$key]["U_BANDWIDTH"], 1) ?>
					</span>
					<span class="u-text-small">
						<?= humanize_usage_measure($data[$key]["U_BANDWIDTH"]) ?>
					</span> /
					<span class="u-text-bold">
						<?= humanize_usage_size($data[$key]["BANDWIDTH"], 1) ?>
					</span>
					<span class="u-text-small">
						<?= humanize_usage_measure($data[$key]["BANDWIDTH"]) ?>
					</span>
				</div>
				<div class="units-table-cell compact u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("Web Domains") ?>:</span>
					<span class="units-table-badge">
						<?= $data[$key]["U_WEB_DOMAINS"] ?>
					</span>
				</div>
				<div class="units-table-cell compact u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("DNS Zones") ?>:</span>
					<span class="units-table-badge">
						<?= $data[$key]["U_DNS_DOMAINS"] ?>
					</span>
				</div>
				<div class="units-table-cell compact u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("Mail Domains") ?>:</span>
					<span class="units-table-badge">
						<?= $data[$key]["U_MAIL_DOMAINS"] ?>
					</span>
				</div>
				<div class="units-table-cell compact u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("Databases") ?>:</span>
					<span class="units-table-badge">
						<?= $data[$key]["U_DATABASES"] ?>
					</span>
				</div>
				<div class="units-table-cell compact u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("Backups") ?>:</span>
					<span class="units-table-badge">
						<?= $data[$key]["U_BACKUPS"] ?>
					</span>
				</div>
			</div>
		<?php } ?>
	</div>

</div>

<footer class="app-footer">
	<div class="container app-footer-inner">
		<p>
			<?php printf(ngettext("%d user account", "%d user accounts", $i), $i); ?>
		</p>
	</div>
</footer>
