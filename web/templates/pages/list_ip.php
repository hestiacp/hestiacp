<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/edit/server/">
				<i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
			</a>
			<a href="/add/ip/" class="button button-secondary js-button-create">
				<i class="fas fa-circle-plus icon-green"></i><?= _("Add IP Address") ?>
			</a>
		</div>
		<div class="toolbar-right">
			<div class="toolbar-sorting">
				<button class="toolbar-sorting-toggle js-toggle-sorting-menu" type="button" title="<?= _("Sort items") ?>">
					<?= _("Sort by") ?>:
					<span class="u-text-bold">
						<?= _("Date") ?> <i class="fas fa-arrow-down-a-z"></i>
					</span>
				</button>
				<ul class="toolbar-sorting-menu js-sorting-menu u-hidden">
					<li data-entity="sort-date" data-sort-as-int="1">
						<span class="name <?php if ($_SESSION['userSortOrder'] === 'date') { echo 'active'; } ?>"><?= _("Date") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-ip">
						<span class="name"><?= _("IP Address") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-netmask">
						<span class="name"><?= _("Netmask") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-interface">
						<span class="name"><?= _("Interface") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-domains" data-sort-as-int="1">
						<span class="name"><?= _("Domains") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-owner">
						<span class="name"><?= _("Owner") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
				</ul>
				<form x-data x-bind="BulkEdit" action="/bulk/ip/" method="post">
					<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
					<select class="form-select" name="action">
						<option value=""><?= _("Apply to selected") ?></option>
						<option value="reread IP"><?= _("Refresh IP Addresses") ?></option>
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

	<h1 class="u-text-center u-hide-desktop u-mt20 u-pr30 u-mb20 u-pl30"><?= _("IP Addresses") ?></h1>

	<div class="units-table js-units-container">
		<div class="units-table-header">
			<div class="units-table-cell">
				<input type="checkbox" class="js-toggle-all-checkbox" title="<?= _("Select all") ?>">
			</div>
			<div class="units-table-cell"><?= _("IP Address") ?></div>
			<div class="units-table-cell"></div>
			<div class="units-table-cell u-text-center"><?= _("Netmask") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Interface") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Status") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Domains") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Owner") ?></div>
		</div>

		<!-- Begin IP address list item loop -->
		<?php
			foreach ($data as $key => $value) {
				++$i;
			?>
			<div class="units-table-row js-unit"
				data-sort-ip="<?= str_replace(".", "", $key) ?>"
				data-sort-date="<?= strtotime($data[$key]["DATE"] . " " . $data[$key]["TIME"]) ?>"
				data-sort-netmask="<?= str_replace(".", "", $data[$key]["NETMASK"]) ?>"
				data-sort-interface="<?= $data[$key]["INTERFACE"] ?>"
				data-sort-domains="<?= $data[$key]["U_WEB_DOMAINS"] ?>"
				data-sort-owner="<?= $data[$key]["OWNER"] ?>">
				<div class="units-table-cell">
					<div>
						<input id="check<?= $i ?>" class="js-unit-checkbox" type="checkbox" title="<?= _("Select") ?>" name="ip[]" value="<?= $key ?>">
						<label for="check<?= $i ?>" class="u-hide-desktop"><?= _("Select") ?></label>
					</div>
				</div>
				<div class="units-table-cell units-table-heading-cell u-text-bold">
					<span class="u-hide-desktop"><?= _("IP Address") ?>:</span>
					<a href="/edit/ip/?ip=<?= $key ?>&token=<?= $_SESSION['token'] ?>" title="<?= _("Edit IP Address") ?>">
						<?= $key ?> <?php if (!empty($data[$key]['NAT'])) echo ' → ' . $data[$key]['NAT'] . ''; ?>
					</a>
				</div>
				<div class="units-table-cell">
					<ul class="units-table-row-actions">
						<li class="units-table-row-action shortcut-enter" data-key-action="href">
							<a
								class="units-table-row-action-link"
								href="/edit/ip/?ip=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
								title="<?= _("Edit IP Address") ?>"
							>
								<i class="fas fa-pencil icon-orange"></i>
								<span class="u-hide-desktop"><?= _("Edit IP Address") ?></span>
							</a>
						</li>
						<li class="units-table-row-action shortcut-delete" data-key-action="js">
							<a
								class="units-table-row-action-link data-controls js-confirm-action"
								href="/delete/ip/?ip=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
								title="<?= _("Delete") ?>"
								data-confirm-title="<?= _("Delete") ?>"
								data-confirm-message="<?= sprintf(_("Are you sure you want to delete IP address %s?"), $key) ?>"
							>
								<i class="fas fa-trash icon-red"></i>
								<span class="u-hide-desktop"><?= _("Delete") ?></span>
							</a>
						</li>
					</ul>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("Netmask") ?>:</span>
					<?= $data[$key]["NETMASK"] ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("Interface") ?>:</span>
					<?= $data[$key]["INTERFACE"] ?>
				</div>
				<div class="units-table-cell u-text-bold u-text-center-desktop">
					<span class="u-hide-desktop"><?= _("Status") ?>:</span>
					<?= _($data[$key]["STATUS"]) ?>
				</div>
				<div class="units-table-cell u-text-bold u-text-center-desktop">
					<span class="u-hide-desktop"><?= _("Domains") ?>:</span>
					<?= $data[$key]["U_WEB_DOMAINS"] ?>
				</div>
				<div class="units-table-cell u-text-bold u-text-center-desktop">
					<span class="u-hide-desktop"><?= _("Owner") ?>:</span>
					<?= $data[$key]["OWNER"] ?>
				</div>
			</div>
		<?php } ?>
	</div>

</div>

<footer class="app-footer">
	<div class="container app-footer-inner">
		<p>
			<?php printf(ngettext("%d IP address", "%d IP addresses", $i), $i); ?>
		</p>
	</div>
</footer>
