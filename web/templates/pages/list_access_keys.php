<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<?php if ($_SESSION["userContext"] === "admin" && $_SESSION['look'] !== '' && $_GET["user"] !== "admin") { ?>
				<a href="/edit/user/?user=<?= htmlentities($_SESSION["look"]) ?>&token=<?= $_SESSION["token"] ?>" class="button button-secondary button-back js-button-back">
					<i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
				</a>
			<?php } else { ?>
				<a href="/edit/user/?user=<?= htmlentities($_SESSION["user"]) ?>&token=<?= $_SESSION["token"] ?>" class="button button-secondary button-back js-button-back">
					<i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
				</a>
			<?php } ?>
			<a href="/add/access-key/" class="button button-secondary js-button-create">
				<i class="fas fa-circle-plus icon-green"></i><?= _("Add Access Key") ?>
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
					<li data-entity="sort-key">
						<span class="name"><?= _("Access Key") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-comment">
						<span class="name"><?= _("Comment") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
				</ul>
				<form x-data x-bind="BulkEdit" action="/bulk/access-key/" method="post">
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

	<h1 class="u-text-center u-hide-desktop u-mt20 u-pr30 u-mb20 u-pl30"><?= _("Access Keys") ?></h1>

	<div class="units-table js-units-container">
		<div class="units-table-header">
			<div class="units-table-cell">
				<input type="checkbox" class="js-toggle-all-checkbox" title="<?= _("Select all") ?>">
			</div>
			<div class="units-table-cell"><?= _("Access Key") ?></div>
			<div class="units-table-cell"></div>
			<div class="units-table-cell u-text-center"><?= _("Comment") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Date") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Time") ?></div>
		</div>

		<?php
			foreach ($data as $key => $value) {
				++$i;
				$key_user = !empty($value['USER']) ? $value['USER'] : 'admin';
				$key_comment = !empty($value['COMMENT']) ? $value['COMMENT'] : '-';
				//$key_permissions = !empty($value['PERMISSIONS']) ? $value['PERMISSIONS'] : '-';
				//$key_permissions = implode(' ', $key_permissions);
				$key_date = !empty($value['DATE']) ? $value['DATE'] : '-';
				$key_time = !empty($value['TIME']) ? $value['TIME'] : '-';
			?>
			<div class="units-table-row js-unit"
				data-sort-key="<?= strtolower($key) ?>"
				data-sort-comment="<?= strtolower($key_comment) ?>"
				data-sort-date="<?= strtotime($data[$key]["DATE"] . " " . $data[$key]["TIME"]) ?>">
				<div class="units-table-cell">
					<div>
						<input id="check<?= $i ?>" class="js-unit-checkbox" type="checkbox" title="<?= _("Select") ?>" name="key[]" value="<?= $key ?>">
						<label for="check<?= $i ?>" class="u-hide-desktop"><?= _("Select") ?></label>
					</div>
				</div>
				<div class="units-table-cell units-table-heading-cell u-text-bold">
					<span class="u-hide-desktop"><?= _("Access Key") ?>:</span>
					<a href="/list/access-key/?key=<?= htmlentities($key) ?>&token=<?= $_SESSION["token"] ?>" title="<?= _("Access Key") ?>: <?= $key ?>">
						<?= $key ?>
					</a>
				</div>
				<div class="units-table-cell">
					<ul class="units-table-row-actions">
						<li class="units-table-row-action shortcut-delete" data-key-action="js">
							<a
								class="units-table-row-action-link data-controls js-confirm-action"
								href="/delete/access-key/?key=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
								title="<?= _("Delete") ?>"
								data-confirm-title="<?= _("Delete") ?>"
								data-confirm-message="<?= sprintf(_("Are you sure you want to delete access key %s?"), $key) ?>"
							>
								<i class="fas fa-trash icon-red"></i>
								<span class="u-hide-desktop"><?= _("Delete") ?></span>
							</a>
						</li>
					</ul>
				</div>
				<div class="units-table-cell u-text-bold u-text-center-desktop">
					<span class="u-hide-desktop"><?= _("Comment") ?>:</span>
					<?= _($key_comment) ?>
				</div>
				<div class="units-table-cell u-text-bold u-text-center-desktop">
					<span class="u-hide-desktop"><?= _("Date") ?>:</span>
					<time datetime="<?= $key_date ?>"><?= $key_date ?></time>
				</div>
				<div class="units-table-cell u-text-bold u-text-center-desktop">
					<span class="u-hide-desktop"><?= _("Time") ?>:</span>
					<?= $key_time ?>
				</div>
			</div>
		<?php } ?>
	</div>

</div>

<footer class="app-footer">
	<div class="container app-footer-inner">
		<p>
			<?php printf(ngettext("%d access key", "%d access keys", $i), $i); ?>
		</p>
	</div>
</footer>
