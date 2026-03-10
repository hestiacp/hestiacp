<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<?php if ($read_only !== "true") { ?>
				<a href="/add/dns/" class="button button-secondary js-button-create">
					<i class="fas fa-circle-plus icon-green"></i><?= tohtml( _("Add DNS Domain")) ?>
				</a>
			<?php } ?>
		</div>
		<div class="toolbar-right">
			<div class="toolbar-sorting">
				<button class="toolbar-sorting-toggle js-toggle-sorting-menu" type="button" title="<?= tohtml( _("Sort items")) ?>">
					<?= tohtml( _("Sort by")) ?>:
					<span class="u-text-bold">
						<?php if ($_SESSION['userSortOrder'] === 'name') { $label = _('Name'); } else { $label = _('Date'); } ?>
						<?= tohtml($label) ?> <i class="fas fa-arrow-down-a-z"></i>
					</span>
				</button>
				<ul class="toolbar-sorting-menu js-sorting-menu u-hidden">
					<li data-entity="sort-date" data-sort-as-int="1">
						<span class="name <?php if ($_SESSION['userSortOrder'] === 'date') { echo 'active'; } ?>"><?= tohtml( _("Date")) ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-expire" data-sort-as-int="1">
						<span class="name"><?= tohtml( _("Expire")) ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-ip">
						<span class="name"><?= tohtml( _("IP Address")) ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-name">
						<span class="name <?php if ($_SESSION['userSortOrder'] === 'name') { echo 'active'; } ?>"><?= tohtml( _("Name")) ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-records">
						<span class="name"><?= tohtml( _("Records")) ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
				</ul>
				<?php if ($read_only !== "true") { ?>
					<form x-data x-bind="BulkEdit" action="/bulk/dns/" method="post">
						<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
						<select class="form-select" name="action">
							<option value=""><?= tohtml( _("Apply to selected")) ?></option>
							<?php if ($_SESSION["userContext"] === "admin") { ?>
								<option value="rebuild"><?= tohtml( _("Rebuild")) ?></option>
							<?php } ?>
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

	<h1 class="u-text-center u-hide-desktop u-mt20 u-pr30 u-mb20 u-pl30"><?= tohtml( _("DNS Records")) ?></h1>

	<div class="units-table js-units-container">
		<div class="units-table-header">
			<div class="units-table-cell">
				<input type="checkbox" class="js-toggle-all-checkbox" title="<?= tohtml( _("Select all")) ?>" <?= tohtml($display_mode) ?>>
			</div>
			<div class="units-table-cell"><?= tohtml( _("Name")) ?></div>
			<div class="units-table-cell"></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("Records")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("Template")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("TTL")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("SOA")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("DNSSEC")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("Expiration Date")) ?></div>
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
				data-sort-ip="<?= tohtml(str_replace('.', '', $data[$key]['IP'])) ?>"
				data-sort-date="<?= tohtml(strtotime($data[$key]['DATE'].' '.$data[$key]['TIME'])) ?>"
				data-sort-name="<?= tohtml($key) ?>"
				data-sort-expire="<?= tohtml(strtotime($data[$key]['EXP'])) ?>"
				data-sort-records="<?= tohtml((int)$data[$key]['RECORDS']) ?>">
				<div class="units-table-cell">
					<div>
						<input id="check<?= tohtml($i) ?>" class="js-unit-checkbox" type="checkbox" title="<?= tohtml( _("Select")) ?>" name="domain[]" value="<?= tohtml($key) ?>" <?= tohtml($display_mode) ?>>
						<label for="check<?= tohtml($i) ?>" class="u-hide-desktop"><?= tohtml( _("Select")) ?></label>
					</div>
				</div>
				<div class="units-table-cell units-table-heading-cell u-text-bold">
					<span class="u-hide-desktop"><?= tohtml( _("Name")) ?>:</span>
						<a href="/list/dns/?<?= tohtml(http_build_query(array("domain" => $key, "token" => $_SESSION["token"]))) ?>" title="<?= tohtml( _("DNS Records")) ?>: <?= tohtml($key) ?>">
						<?= tohtml($key) ?>
					</a>
					<?= tohtml(empty($data[$key]["SRC"]) ? "" : '<br>⇢ <span class="u-text-small">' . $data[$key]["SRC"] . "</span>") ?>
				</div>
				<div class="units-table-cell">
					<?php if (!$read_only) { ?>
						<ul class="units-table-row-actions">
							<?php if ($data[$key]["SUSPENDED"] == "no") { ?>
								<li class="units-table-row-action shortcut-n" data-key-action="href">
									<a
										class="units-table-row-action-link"
											href="/add/dns/?<?= tohtml(http_build_query(array("domain" => $key, "token" => $_SESSION["token"]))) ?>"
										title="<?= tohtml( _("Add DNS Record")) ?>"
									>
										<i class="fas fa-circle-plus icon-green"></i>
										<span class="u-hide-desktop"><?= tohtml( _("Add DNS Record")) ?></span>
									</a>
								</li>
								<li class="units-table-row-action shortcut-enter" data-key-action="href">
									<a
										class="units-table-row-action-link"
											href="/edit/dns/?<?= tohtml(http_build_query(array("domain" => $key, "token" => $_SESSION["token"]))) ?>"
										title="<?= tohtml( _("Edit DNS Domain")) ?>"
									>
										<i class="fas fa-pencil icon-orange"></i>
										<span class="u-hide-desktop"><?= tohtml( _("Edit DNS Domain")) ?></span>
									</a>
								</li>
								<?php if ($data[$key]["DNSSEC"] == "yes") { ?>
									<li class="units-table-row-action shortcut-enter" data-key-action="href">
										<a
											class="units-table-row-action-link"
												href="/list/dns/?<?= tohtml(http_build_query(array("domain" => $key, "action" => "dnssec", "token" => $_SESSION["token"]))) ?>"
											title="<?= tohtml( _("View Public DNSSEC Key")) ?>"
										>
											<i class="fas fa-key icon-orange"></i>
											<span class="u-hide-desktop"><?= tohtml( _("View Public DNSSEC Key")) ?></span>
										</a>
									</li>
								<?php } ?>
							<?php } ?>
							<li class="units-table-row-action shortcut-l" data-key-action="href">
								<a
									class="units-table-row-action-link"
									href="/list/dns/?<?= tohtml(http_build_query(array("domain" => $key, "token" => $_SESSION["token"]))) ?>"
									title="<?= tohtml( _("DNS Records")) ?>"
								>
									<i class="fas fa-list icon-lightblue"></i>
									<span class="u-hide-desktop"><?= tohtml( _("DNS Records")) ?></span>
								</a>
							</li>
							<li class="units-table-row-action shortcut-s" data-key-action="js">
								<a
									class="units-table-row-action-link data-controls js-confirm-action"
									href="/<?= tohtml($spnd_action) ?>/dns/?<?= tohtml(http_build_query(array("domain" => $key, "token" => $_SESSION["token"]))) ?>"
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
									href="/delete/dns/?<?= tohtml(http_build_query(array("domain" => $key, "token" => $_SESSION["token"]))) ?>"
									title="<?= tohtml( _("Delete")) ?>"
									data-confirm-title="<?= tohtml( _("Delete")) ?>"
									data-confirm-message="<?= tohtml(sprintf(_("Are you sure you want to delete domain %s?"), $key)) ?>"
								>
									<i class="fas fa-trash icon-red"></i>
									<span class="u-hide-desktop"><?= tohtml( _("Delete")) ?></span>
								</a>
							</li>
						</ul>
					<?php } ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("Records")) ?>:</span>
					<?php if ($data[$key]['RECORDS']) {
						echo '<span>'.$data[$key]['RECORDS'].'</span>';
					} else {
						echo '<span>0</span>';
					} ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("Template")) ?>:</span>
					<span class="u-text-bold">
						<?= tohtml($data[$key]["TPL"]) ?>
					</span>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("TTL")) ?>:</span>
					<?= tohtml($data[$key]["TTL"]) ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("SOA")) ?>:</span>
					<?= tohtml($data[$key]["SOA"]) ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("DNSSEC")) ?>:</span>
					<i class="fas <?= tohtml($dnssec_icon) ?>" title="<?= tohtml($dnssec_title) ?>"></i>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("Expiration Date")) ?>:</span>
					<time class="u-text-bold" datetime="<?= tohtml($data[$key]["EXP"]) ?>">
						<?= tohtml($data[$key]["EXP"]) ?>
					</time>
				</div>
			</div>
		<?php } ?>
	</div>

	<div class="units-table-footer">
		<p>
			<?php printf(ngettext("%d DNS zone", "%d DNS zones", $i), $i); ?>
		</p>
	</div>

</div>
