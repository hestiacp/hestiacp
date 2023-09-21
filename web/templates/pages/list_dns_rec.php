<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/dns/">
				<i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
			</a>
			<?php if ($read_only !== "true") { ?>
				<a href="/add/dns/?domain=<?= htmlentities($_GET["domain"]) ?>" class="button button-secondary js-button-create">
					<i class="fas fa-circle-plus icon-green"></i><?= _("Add Record") ?>
				</a>
				<a href="/edit/dns/?domain=<?= htmlentities($_GET["domain"]) ?>" class="button button-secondary js-button-create">
					<i class="fas fa-pencil icon-blue"></i><?= _("Edit DNS Domain") ?>
				</a>
			<?php } ?>
		</div>
		<div class="toolbar-right">
			<div class="toolbar-sorting">
				<button class="toolbar-sorting-toggle js-toggle-sorting-menu" type="button" title="<?= _("Sort items") ?>">
					<?= _("Sort by") ?>:
					<span class="u-text-bold">
						<?php if ($_SESSION['userSortOrder'] === 'name') { $label = _('Record'); } else { $label = _('Date'); } ?>
						<?= $label ?> <i class="fas fa-arrow-down-a-z"></i>
					</span>
				</button>
				<ul class="toolbar-sorting-menu js-sorting-menu u-hidden">
					<li data-entity="sort-date" data-sort-as-int="1">
						<span class="name <?php if ($_SESSION['userSortOrder'] === 'date') { echo 'active'; } ?>"><?= _("Date") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-value">
						<span class="name"><?= _("IP or Value") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-record">
						<span class="name"><?= _("Record") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-ttl" data-sort-as-int="1">
						<span class="name"><?= _("TTL") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-type">
						<span class="name"><?= _("Type") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
				</ul>
				<?php if ($read_only !== "true") { ?>
					<form x-data x-bind="BulkEdit" action="/bulk/dns/" method="post">
						<input type="hidden" name="domain" value="<?= htmlentities($_GET["domain"]) ?>">
						<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
						<select class="form-select" name="action">
							<option value=""><?= _("Apply to selected") ?></option>
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
			<div class="units-table-cell"><?= _("Record") ?></div>
			<div class="units-table-cell"></div>
			<div class="units-table-cell u-text-center"><?= _("Type") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Priority") ?></div>
			<div class="units-table-cell u-text-center"><?= _("TTL") ?></div>
			<div class="units-table-cell"><?= _("IP or Value") ?></div>
		</div>

		<!-- Begin DNS record list item loop -->
		<?php
			foreach ($data as $key => $value) {
				++$i;
				if ($data[$key]['SUSPENDED'] == 'yes') {
					$status = 'suspended';
				} else {
					$status = 'active';
				}
			?>
			<div class="units-table-row <?php if ($status == 'suspended') echo 'disabled'; ?> js-unit"
				data-sort-date="<?= strtotime($data[$key]['DATE'].' '.$data[$key]['TIME']) ?>"
				data-sort-record="<?= $data[$key]['RECORD'] ?>"
				data-sort-type="<?= $data[$key]['TYPE'] ?>"
				data-sort-ttl="<?= $data[$key]['TTL'] ?>"
				data-sort-value="<?= $data[$key]['VALUE'] ?>">
				<div class="units-table-cell">
					<div>
						<input id="check<?= $data[$key]["ID"] ?>" class="js-unit-checkbox" type="checkbox" title="<?= _("Select") ?>" name="record[]" value="<?= $data[$key]["ID"] ?>" <?= $display_mode ?>>
						<label for="check<?= $data[$key]["ID"] ?>" class="u-hide-desktop"><?= _("Select") ?></label>
					</div>
				</div>
				<div class="units-table-cell units-table-heading-cell u-text-bold">
					<span class="u-hide-desktop"><?= _("Record") ?>:</span>
					<?php if (($read_only === 'true') || ($data[$key]['SUSPENDED'] == 'yes')) { ?>
						<?= substr($data[$key]['RECORD'], 0, 12); if (strlen($data[$key]['RECORD']) > 12 ) echo '...'; ?>
					<?php } else { ?>
						<a href="/edit/dns/?domain=<?= htmlspecialchars($_GET['domain']) ?>&record_id=<?= $data[$key]['ID'] ?>&token=<?= $_SESSION['token'] ?>" title="<?= _("Edit DNS Record") . ': '.htmlspecialchars($data[$key]['RECORD']) ?>">
							<? echo substr($data[$key]['RECORD'], 0, 12); if (strlen($data[$key]['RECORD']) > 12 ) echo '...'; ?>
						</a>
					<?php } ?>
				</div>
				<div class="units-table-cell">
					<?php if ($read_only !== "true") { ?>
						<ul class="units-table-row-actions">
							<?php if ($read_only !== "true") { ?>
								<?php if ($data[$key]["SUSPENDED"] == "no") { ?>
									<li class="units-table-row-action shortcut-enter" data-key-action="href">
										<a
											class="units-table-row-action-link"
											href="/edit/dns/?domain=<?= htmlspecialchars($_GET["domain"]) ?>&record_id=<?= $data[$key]["ID"] ?>&token=<?= $_SESSION["token"] ?>"
											title="<?= _("Edit DNS Record") ?>"
										>
											<i class="fas fa-pencil icon-orange"></i>
											<span class="u-hide-desktop"><?= _("Edit DNS Record") ?></span>
										</a>
									</li>
								<?php } ?>
								<li class="units-table-row-action shortcut-delete" data-key-action="js">
									<a
										class="units-table-row-action-link data-controls js-confirm-action"
										href="/delete/dns/?domain=<?= htmlspecialchars($_GET["domain"]) ?>&record_id=<?= $data[$key]["ID"] ?>&token=<?= $_SESSION["token"] ?>"
										title="<?= _("Delete") ?>"
										data-confirm-title="<?= _("Delete") ?>"
										data-confirm-message="<?= sprintf(_("Are you sure you want to delete record %s?"), $key) ?>"
									>
										<i class="fas fa-trash icon-red"></i>
										<span class="u-hide-desktop"><?= _("Delete") ?></span>
									</a>
								</li>
							<?php } ?>
						</ul>
					<?php } ?>
				</div>
				<div class="units-table-cell u-text-bold u-text-center-desktop">
					<span class="u-hide-desktop"><?= _("Type") ?>:</span>
					<?= $data[$key]["TYPE"] ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("Priority") ?>:</span>
					<?= $data[$key]["PRIORITY"] ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("TTL") ?>:</span>
					<?php if ($data[$key]['TTL'] == ''){ echo _('Default'); } else { echo $data[$key]['TTL'];} ?>
				</div>
				<div class="units-table-cell">
					<span class="u-hide-desktop u-text-bold"><?= _("IP or Value") ?>:</span>
					<span class="u-text-break">
						<?= htmlspecialchars($data[$key]["VALUE"], ENT_QUOTES, "UTF-8") ?>
					</span>
				</div>
			</div>
		<?php } ?>
	</div>

</div>

<footer class="app-footer">
	<div class="container app-footer-inner">
		<p>
			<?php printf(ngettext("%d DNS record", "%d DNS records", $i), $i); ?>
		</p>
	</div>
</footer>
