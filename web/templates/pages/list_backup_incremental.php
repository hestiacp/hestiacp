<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
		<?php if ($read_only !== "true") { ?>
			<a href="/schedule/backup/incremental/?token=<?= $_SESSION["token"] ?>" class="button button-secondary js-button-create">
				<i class="fas fa-circle-plus icon-green"></i><?= _("Create Snapshot") ?>
			</a>
		<?php } ?>
		</div>
		<div class="toolbar-right">
			<?php if ($read_only !== "true") { ?>
				<form x-data x-bind="BulkEdit" action="/bulk/backup/incremental/" method="post">
					<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
					<select class="form-select" name="action">
						<option value=""><?= _("Apply to selected") ?></option>
						<option value="delete"><?= _("Delete") ?></option>
					</select>
					<button type="submit" class="toolbar-input-submit" title="<?= _("Apply to selected") ?>">
						<i class="fas fa-arrow-right"></i>
					</button>
				</form>
			<?php } ?>
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

<h1 class="u-text-center u-hide-desktop u-mt20 u-pr30 u-mb20 u-pl30"><?= _("Snapshots") ?></h1>

<div class="units-table js-units-container">
	<div class="units-table-header">
		<div class="units-table-cell">
			<input type="checkbox" class="js-toggle-all-checkbox" title="<?= _("Select all") ?>" <?= $display_mode ?>>
		</div>
		<div class="units-table-cell"><?= _("Snapshot") ?></div>
		<div class="units-table-cell"></div>
		<div class="units-table-cell u-text-center"><?= _("Date") ?></div>
		<div class="units-table-cell u-text-center"><?= _("Type") ?></div>
		<div class="units-table-cell u-text-center"><?= _("Hostname") ?></div>
	</div>
	<!-- Begin user backup list item loop -->
	<?php
		$i =0;
		foreach ($data as $key => $value) {
			$i++;
	?>
		<div class="units-table-row js-unit">
			<div class="units-table-cell">
				<div>
					<input id="check<?= $i ?>" class="js-unit-checkbox" type="checkbox" title="<?= _("Select") ?>" name="backup[]" value="<?= $value['short_id'] ?>" <?= $display_mode ?>>					<span class="u-hide-desktop"><label for="check<?= $i ?>" class="u-hide-desktop"><?= _("Select") ?></label></span>
				</div>
			</div>
			<div class="units-table-cell units-table-heading-cell u-text-bold">
				<b>
					<?php if ($read_only === "true") { ?>
							<span class="u-hide-desktop"><?= _("Snapshot") ?>:</span>
							<?= $value['short_id'] ?>
					<?php } else { ?>
						<span class="u-hide-desktop"><?= _("Snapshot") ?>:</span>
						<a href="/list/backup/incremental/?snapshot=<?= $value['short_id'] ?>&token=<?= $_SESSION["token"] ?>" title="<?= _("Restore") ?>"><?= $value['short_id'] ?></a>
					<?php } ?>
				</b>
			</div>
			<div class="units-table-cell">
				<?php if (!$read_only) { ?>
					<ul class="units-table-row-actions">
						<li class="units-table-row-action shortcut-enter" data-key-action="href">
							<a href="/list/backup/incremental/?snapshot=<?= $value['short_id'] ?>&browse=yes&token=<?= $_SESSION["token"] ?>" title="<?= _("Browse") ?>"><i class="fas fa-folder-open icon-lightblue icon-dim"></i></a>
						</li>
						<li class="units-table-row-action shortcut-enter" data-key-action="href">
							<a href="/list/backup/incremental/?snapshot=<?= $value['short_id'] ?>&token=<?= $_SESSION["token"] ?>" title="<?= _("Restore") ?>"><i class="fas fa-arrow-rotate-left icon-green icon-dim"></i></a>
						</li>
					</ul>
				<?php } ?>
			</div>
			<div class="units-table-cell">
				<span class="u-hide-desktop u-text-bold"><?= _("Date") ?>:</span>
				<span class="u-text-bold">
					<?= convert_datetime($value['time']) ?>
				</span>
			</div>
			<div class="units-table-cell">
				<span class="u-hide-desktop u-text-bold"><?= _("Type") ?>:</span>
				<span class="u-text-bold">
					Restic
				</span>
			</div>
			<div class="units-table-cell">
				<span class="u-hide-desktop u-text-bold"><?= _("Hostname") ?>:</span>
				<span class="u-text-bold">
					<?=htmlentities($value['hostname'])?>
				</span>
			</div>
		</div>
	<?php
	}
	 ?>
</div>

<footer class="app-footer">
	<div class="container app-footer-inner">
		<p>
			<?php printf(ngettext("%d snapshot", "%d snapshots", count($data)), count($data)); ?>
		</p>
	</div>
</footer>
