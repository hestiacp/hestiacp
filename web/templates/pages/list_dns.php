<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<?php if ($read_only !== "true") { ?>
				<a href="/add/dns/" class="button button-secondary js-button-create">
					<i class="fas fa-circle-plus icon-green"></i><?= _("Add DNS Domain") ?>
				</a>
			<?php } ?>
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
					<li data-entity="sort-expire" data-sort-as-int="1">
						<span class="name"><?= _("Expire") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-ip">
						<span class="name"><?= _("IP Address") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-name">
						<span class="name <?php if ($_SESSION['userSortOrder'] === 'name') { echo 'active'; } ?>"><?= _("Name") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-records">
						<span class="name"><?= _("Records") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
				</ul>
				<?php if ($read_only !== "true") { ?>
					<form x-data x-bind="BulkEdit" action="/bulk/dns/" method="post">
						<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
						<select class="form-select" name="action">
							<option value=""><?= _("Apply to selected") ?></option>
							<?php if ($_SESSION["userContext"] === "admin") { ?>
								<option value="rebuild"><?= _("Rebuild") ?></option>
							<?php } ?>
							<option value="suspend"><?= _("Suspend") ?></option>
							<option value="unsuspend"><?= _("Unsuspend") ?></option>
							<option value="delete"><?= _("Delete") ?></option>
						</select>
						<button type="submit" class="toolbar-input-submit" title="<?= _("Apply to selected") ?>">
							<i class="fas fa-arrow-right"></i>
						</button>
					</form>
				<?php } ?>
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

	<h1 class="u-text-center u-hide-desktop u-mt20 u-pr30 u-mb20 u-pl30"><?= _("DNS Records") ?></h1>

	<div class="units-table js-units-container">
		<div class="units-table-header">
			<div class="units-table-cell">
				<input type="checkbox" class="js-toggle-all-checkbox" title="<?= _("Select all") ?>" <?= $display_mode ?>>
			</div>
			<div class="units-table-cell"><?= _("Name") ?></div>
			<div class="units-table-cell"></div>
			<div class="units-table-cell u-text-center"><?= _("Records") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Template") ?></div>
			<div class="units-table-cell u-text-center"><?= _("TTL") ?></div>
			<div class="units-table-cell u-text-center"><?= _("SOA") ?></div>
			<div class="units-table-cell u-text-center"><?= _("DNSSEC") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Expiration Date") ?></div>
		</div>

		<!-- Begin DNS zone list item loop -->
		<?php
			foreach ($data as $key => $value) {
				++$i;
				if ($data[$key]['SUSPENDED'] == 'yes') {
					$status = 'suspended';
					$spnd_action = 'unsuspend';
					$spnd_action_title = _('Unsuspend');
					$spnd_icon = 'fa-play';
					$spnd_icon_class = 'icon-green';
					$spnd_confirmation = _('Are you sure you want to unsuspend domain %s?');
					if ($data[$key]['DNSSEC'] !== 'yes') {
						$dnssec_icon = 'fa-circle-xmark';
						$dnssec_title = _('Disabled');
					} else {
						$dnssec_icon = 'fa-circle-check';
						$dnssec_title = _('Enabled');
					}
				} else {
					$status = 'active';
					$spnd_action = 'suspend';
					$spnd_action_title = _('Suspend');
					$spnd_icon = 'fa-pause';
					$spnd_icon_class = 'icon-highlight';
					$spnd_confirmation = _('Are you sure you want to suspend domain %s?');
					if ($data[$key]['DNSSEC'] !== 'yes') {
						$dnssec_icon = 'fa-circle-xmark icon-red';
						$dnssec_title = _('Disabled');
					} else {
						$dnssec_icon = 'fa-circle-check icon-green';
						$dnssec_title = _('Enabled');
					}
				}
			?>
			<div class="units-table-row <?php if ($status == 'suspended') echo 'disabled'; ?> js-unit"
				data-sort-ip="<?= str_replace('.', '', $data[$key]['IP']) ?>"
				data-sort-date="<?= strtotime($data[$key]['DATE'].' '.$data[$key]['TIME']) ?>"
				data-sort-name="<?= htmlentities($key);?>"
				data-sort-expire="<?= strtotime($data[$key]['EXP']) ?>"
				data-sort-records="<?=(int)$data[$key]['RECORDS'] ?>">
				<div class="units-table-cell">
					<div>
						<input id="check<?= $i ?>" class="js-unit-checkbox" type="checkbox" title="<?= _("Select") ?>" name="domain[]" value="<?= $key ?>" <?= $display_mode ?>>
						<label for="check<?= $i ?>" class="u-hide-desktop"><?= _("Select") ?></label>
					</div>
				</div>
				<div class="units-table-cell units-table-heading-cell u-text-bold">
					<span class="u-hide-desktop"><?= _("Name") ?>:</span>
					<a href="/list/dns/?domain=<?= htmlentities($key) ?>&token=<?= $_SESSION["token"] ?>" title="<?= _("DNS Records") ?>: <?= htmlentities($key) ?>">
						<?= htmlentities($key) ?>
					</a>
					<?= empty($data[$key]["SRC"]) ? "" : '<br>⇢ <span class="u-text-small">' . htmlspecialchars($data[$key]["SRC"], ENT_QUOTES) . "</span>" ?>
				</div>
				<div class="units-table-cell">
					<?php if (!$read_only) { ?>
						<ul class="units-table-row-actions">
							<?php if ($data[$key]["SUSPENDED"] == "no") { ?>
								<li class="units-table-row-action shortcut-n" data-key-action="href">
									<a
										class="units-table-row-action-link"
										href="/add/dns/?domain=<?= htmlentities($key) ?>&token=<?= $_SESSION["token"] ?>"
										title="<?= _("Add DNS Record") ?>"
									>
										<i class="fas fa-circle-plus icon-green"></i>
										<span class="u-hide-desktop"><?= _("Add DNS Record") ?></span>
									</a>
								</li>
								<li class="units-table-row-action shortcut-enter" data-key-action="href">
									<a
										class="units-table-row-action-link"
										href="/edit/dns/?domain=<?= htmlentities($key) ?>&token=<?= $_SESSION["token"] ?>"
										title="<?= _("Edit DNS Domain") ?>"
									>
										<i class="fas fa-pencil icon-orange"></i>
										<span class="u-hide-desktop"><?= _("Edit DNS Domain") ?></span>
									</a>
								</li>
								<?php if ($data[$key]["DNSSEC"] == "yes") { ?>
									<li class="units-table-row-action shortcut-enter" data-key-action="href">
										<a
											class="units-table-row-action-link"
											href="/list/dns/?domain=<?= htmlentities($key) ?>&action=dnssec&token=<?= $_SESSION["token"] ?>"
											title="<?= _("View Public DNSSEC Key") ?>"
										>
											<i class="fas fa-key icon-orange"></i>
											<span class="u-hide-desktop"><?= _("View Public DNSSEC Key") ?></span>
										</a>
									</li>
								<?php } ?>
							<?php } ?>
							<li class="units-table-row-action shortcut-l" data-key-action="href">
								<a
									class="units-table-row-action-link"
									href="/list/dns/?domain=<?= htmlentities($key) ?>&token=<?= $_SESSION["token"] ?>"
									title="<?= _("DNS Records") ?>"
								>
									<i class="fas fa-list icon-lightblue"></i>
									<span class="u-hide-desktop"><?= _("DNS Records") ?></span>
								</a>
							</li>
							<li class="units-table-row-action shortcut-s" data-key-action="js">
								<a
									class="units-table-row-action-link data-controls js-confirm-action"
									href="/<?= $spnd_action ?>/dns/?domain=<?= htmlentities($key) ?>&token=<?= $_SESSION["token"] ?>"
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
									href="/delete/dns/?domain=<?= htmlentities($key) ?>&token=<?= $_SESSION["token"] ?>"
									title="<?= _("Delete") ?>"
									data-confirm-title="<?= _("Delete") ?>"
									data-confirm-message="<?= sprintf(_("Are you sure you want to delete domain %s?"), $key) ?>"
								>
									<i class="fas fa-trash icon-red"></i>
									<span class="u-hide-desktop"><?= _("Delete") ?></span>
								</a>
							</li>
						</ul>
					<?php } ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("Records") ?>:</span>
					<?php if ($data[$key]['RECORDS']) {
						echo '<span>'.$data[$key]['RECORDS'].'</span>';
					} else {
						echo '<span>0</span>';
					} ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("Template") ?>:</span>
					<span class="u-text-bold">
						<?= $data[$key]["TPL"] ?>
					</span>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("TTL") ?>:</span>
					<?= $data[$key]["TTL"] ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("SOA") ?>:</span>
					<?= $data[$key]["SOA"] ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("DNSSEC") ?>:</span>
					<i class="fas <?= $dnssec_icon ?>" title="<?= $dnssec_title ?>"></i>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("Expiration Date") ?>:</span>
					<time class="u-text-bold" datetime="<?= $data[$key]["EXP"] ?>">
						<?= $data[$key]["EXP"] ?>
					</time>
				</div>
			</div>
		<?php } ?>
	</div>

</div>

<footer class="app-footer">
	<div class="container app-footer-inner">
		<p>
			<?php printf(ngettext("%d DNS zone", "%d DNS zones", $i), $i); ?>
		</p>
	</div>
</footer>
