<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/firewall/">
				<i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
			</a>
			<a href="/add/firewall/banlist/" class="button button-secondary js-button-create">
				<i class="fas fa-circle-plus icon-green"></i><?= _("Ban IP Address") ?>
			</a>
		</div>
		<div class="toolbar-right">
			<form x-data x-bind="BulkEdit" action="/bulk/firewall/banlist/" method="post">
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
<!-- End toolbar -->

<div class="container">

	<h1 class="u-text-center u-hide-desktop u-mt20 u-pr30 u-mb20 u-pl30"><?= _("Banned IP Addresses") ?></h1>

	<div class="units-table js-units-container">
		<div class="units-table-header">
			<div class="units-table-cell">
				<input type="checkbox" class="js-toggle-all-checkbox" title="<?= _("Select all") ?>">
			</div>
			<div class="units-table-cell"><?= _("IP Address") ?></div>
			<div class="units-table-cell"></div>
			<div class="units-table-cell u-text-center"><?= _("Date") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Time") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Chain") ?></div>
		</div>

		<!-- Begin banned IP address list item loop -->
		<?php
			foreach ($data as $key => $value) {
				++$i;
				$ip = $key;
			?>
			<div class="units-table-row js-unit">
				<div class="units-table-cell">
					<div>
						<input id="check<?= $i ?>" class="js-unit-checkbox" type="checkbox" title="<?= _("Select") ?>" name="ipchain[]" value="<?= $ip . ":" . $value["CHAIN"] ?>">
						<label for="check<?= $i ?>" class="u-hide-desktop"><?= _("Select") ?></label>
					</div>
				</div>
				<div class="units-table-cell units-table-heading-cell u-text-bold">
					<span class="u-hide-desktop"><?= _("IP Address") ?>:</span>
					<?= $ip ?>
				</div>
				<div class="units-table-cell">
					<ul class="units-table-row-actions">
						<li class="units-table-row-action shortcut-delete" data-key-action="js">
							<a
								class="units-table-row-action-link data-controls js-confirm-action"
								href="/delete/firewall/banlist/?ip=<?= $ip ?>&chain=<?= $value["CHAIN"] ?>&token=<?= $_SESSION["token"] ?>"
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
					<span class="u-hide-desktop u-text-bold"><?= _("Date") ?>:</span>
					<time datetime="<?= _($data[$key]["DATE"]) ?>"><?= _($data[$key]["DATE"]) ?></time>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("Time") ?>:</span>
					<?= $data[$key]["TIME"] ?>
				</div>
				<div class="units-table-cell u-text-bold u-text-center-desktop">
					<span class="u-hide-desktop"><?= _("Chain") ?>:</span>
					<?= _($value["CHAIN"]) ?>
				</div>
			</div>
		<?php } ?>
	</div>

</div>

<footer class="app-footer">
	<div class="container app-footer-inner">
		<p>
			<?php
				if ( $i == 0) {
					echo _('There are currently no banned IP addresses.');
				} else {
					printf(ngettext('%d banned IP address', '%d banned IP addresses', $i),$i);
				}
			?>
		</p>
	</div>
</footer>
