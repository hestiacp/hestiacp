<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/backup/incremental/"><i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?></a>
			<?php if ($read_only !== "true") { ?>
			<a href="/schedule/restore/incremental/?token=<?= $_SESSION["token"] ?>&snapshot=<?= htmlentities($_GET["snapshot"]) ?>" class="button button-secondary"><i class="fas fa-arrow-rotate-left icon-green"></i><?= _("Restore All") ?></a>
			<?php } ?>
		</div>
		<div class="toolbar-right">
			<?php if ($read_only !== "true") { ?>
				<form x-data x-bind="BulkEdit" action="/bulk/restore/incremental/" method="post">
					<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
					<input type="hidden" name="snapshot" value="<?= htmlentities($_GET["snapshot"]) ?>">
					<select class="form-select" name="action">
						<option value=""><?= _("Apply to selected") ?></option>
						<option value="restore"><?= _("Restore Snapshot") ?></option>
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

<h1 class="u-text-center u-hide-desktop u-mt20 u-pr30 u-mb20 u-pl30"><?= _("Backup Details") ?></h1>

<div class="units-table js-units-container">
	<div class="units-table-header">
		<div class="units-table-cell">
			<input type="checkbox" class="js-toggle-all-checkbox" title="<?= _("Select all") ?>">
		</div>
		<div class="units-table-cell"><?= _("Type") ?></div>
		<div class="units-table-cell"><?= _("Details") ?></div>
		<div class="units-table-cell"><?= _("Restore") ?></div>
	</div>
	<?php
		$web = explode(',',$data['snapshot']['WEB']);
		foreach ($web as $key) {
			if (!empty($key)) {
				++$i;
		?>
		<div class="units-table-row js-unit">
			<div class="units-table-cell">
				<div class="clearfix l-unit__stat-col--left super-compact">
					<input id="check<?= $i ?>" class="js-unit-checkbox" type="checkbox" name="web[]" value="<?= $key ?>">
					<label for="check<?= $i ?>" class="u-hide-desktop"><?= _("Select") ?></label>
				</div>
			</div>
			<div class="units-table-cell units-table-heading-cell">
				<span class="u-hide-desktop u-text-bold"><?= _("Type") ?>:</span>
				<?= _("Web Domain") ?>
			</div>
			<div class="units-table-cell u-text-bold">
				<span class="u-hide-desktop"><?= _("Details") ?>:</span>
				<?= $key ?>
			</div>
			<div class="units-table-cell">
				<ul class="units-table-row-actions">
					<li class="units-table-row-action shortcut-enter" data-key-action="href">
						<a href="/schedule/restore/incremental/?snapshot=<?= htmlentities($_GET["snapshot"]) ?>&type=web&object=<?= $key ?>&token=<?= $_SESSION["token"] ?>" title="<?= _("Restore") ?>">
						<i class="fas fa-arrow-rotate-left icon-green"></i>
						<span class="u-hide-desktop"><?= _("Restore") ?></span>
						</a>
					</li>
				</ul>
			</div>
		</div>
		<?php }} ?>

		<?php
		$mail = explode(',',$data['snapshot']['MAIL']);
		foreach ($mail as $key) {
			if (!empty($key)) {
				++$i;
		?>
		<div class="units-table-row js-unit">
			<div class="units-table-cell">
				<div class="clearfix l-unit__stat-col--left super-compact">
					<input id="check<?= $i ?>" class="js-unit-checkbox" type="checkbox" name="mail[]" value="<?= $key ?>">
					<label for="check<?= $i ?>" class="u-hide-desktop"><?= _("Select") ?></label>
				</div>
			</div>
			<div class="units-table-cell units-table-heading-cell">
				<span class="u-hide-desktop u-text-bold"><?= _("Type") ?>:</span>
				<?= _("Mail Domain") ?>
			</div>
			<div class="units-table-cell u-text-bold">
				<span class="u-hide-desktop"><?= _("Details") ?>:</span>
				<?= $key ?>
			</div>
			<div class="units-table-cell">
				<ul class="units-table-row-actions">
					<li class="units-table-row-action shortcut-enter" data-key-action="href">
						<a href="/schedule/restore/incremental/?snapshot=<?= htmlentities($_GET["snapshot"]) ?>&type=mail&object=<?= $key ?>&token=<?= $_SESSION["token"] ?>" title="<?= _("Restore") ?>">
						<i class="fas fa-arrow-rotate-left icon-green"></i>
						<span class="u-hide-desktop"><?= _("Restore") ?></span>
						</a>
					</li>
				</ul>
			</div>
		</div>
		<?php }} ?>

		<?php
		$dns = explode(',',$data['snapshot']['DNS']);
		foreach ($dns as $key) {
			if (!empty($key)) {
				++$i;
		?>
		<div class="units-table-row js-unit">
			<div class="units-table-cell">
				<div class="clearfix l-unit__stat-col--left super-compact">
					<input id="check<?= $i ?>" class="js-unit-checkbox" type="checkbox" name="dns[]" value="<?= $key ?>">
					<label for="check<?= $i ?>" class="u-hide-desktop"><?= _("Select") ?></label>
				</div>
			</div>
			<div class="units-table-cell units-table-heading-cell">
				<span class="u-hide-desktop u-text-bold"><?= _("Type") ?>:</span>
				<?= _("DNS Domain") ?>
			</div>
			<div class="units-table-cell u-text-bold">
				<span class="u-hide-desktop"><?= _("Details") ?>:</span>
				<?= $key ?>
			</div>
			<div class="units-table-cell">
				<ul class="units-table-row-actions">
					<li class="units-table-row-action shortcut-enter" data-key-action="href">
						<a href="/schedule/restore/incremental/?snapshot=<?= htmlentities($_GET["snapshot"]) ?>&type=dns&object=<?= $key ?>&token=<?= $_SESSION["token"] ?>" title="<?= _("Restore") ?>">
						<i class="fas fa-arrow-rotate-left icon-green"></i>
						<span class="u-hide-desktop"><?= _("Restore") ?></span>
						</a>
					</li>
				</ul>
			</div>
		</div>
		<?php }} ?>

		<?php
		$db = explode(',',$data['snapshot']['DB']);
		foreach ($db as $key) {
			if (!empty($key)) {
				++$i;
		?>
		<div class="units-table-row js-unit">
			<div class="units-table-cell">
				<div class="clearfix l-unit__stat-col--left super-compact">
					<input id="check<?= $i ?>" class="js-unit-checkbox" type="checkbox" name="db[]" value="<?= $key ?>">
					<label for="check<?= $i ?>" class="u-hide-desktop"><?= _("Select") ?></label>
				</div>
			</div>
			<div class="units-table-cell units-table-heading-cell">
				<span class="u-hide-desktop u-text-bold"><?= _("Type") ?>:</span>
				<?= _("Database") ?>
			</div>
			<div class="units-table-cell u-text-bold">
				<span class="u-hide-desktop"><?= _("Details") ?>:</span>
				<?= $key ?>
			</div>
			<div class="units-table-cell">
				<ul class="units-table-row-actions">
					<li class="units-table-row-action shortcut-enter" data-key-action="href">
						<a href="/schedule/restore/incremental/?snapshot=<?= htmlentities($_GET["snapshot"]) ?>&type=db&object=<?= $key ?>&token=<?= $_SESSION["token"] ?>" title="<?= _("Restore") ?>">
						<i class="fas fa-arrow-rotate-left icon-green"></i>
						<span class="u-hide-desktop"><?= _("Restore") ?></span>
						</a>
					</li>
				</ul>
			</div>
		</div>
		<?php }} ?>

	<?php
	$cron = explode(',',$data['snapshot']['CRON']);
	foreach ($cron as $key) {
		if (!empty($key)) {
			++$i;
	?>
	<div class="units-table-row js-unit">
		<div class="units-table-cell">
			<div class="clearfix l-unit__stat-col--left super-compact">
				<input id="check<?= $i ?>" class="js-unit-checkbox" type="checkbox" name="cron[]" value="<?= $key ?>">
				<label for="check<?= $i ?>" class="u-hide-desktop"><?= _("Select") ?></label>
			</div>
		</div>
		<div class="units-table-cell units-table-heading-cell">
			<span class="u-hide-desktop u-text-bold"><?= _("Type") ?>:</span>
			<?= _("Cronjob") ?>
		</div>
		<div class="units-table-cell u-text-bold">
			<span class="u-hide-desktop"><?= _("Details") ?>:</span>
			<?= $key ?>
		</div>
		<div class="units-table-cell">
			<ul class="units-table-row-actions">
				<li class="units-table-row-action shortcut-enter" data-key-action="href">
					<a href="/schedule/restore/incremental/?snapshot=<?= htmlentities($_GET["snapshot"]) ?>&type=cron&object=<?= $key ?>&token=<?= $_SESSION["token"] ?>" title="<?= _("Restore") ?>">
					<i class="fas fa-arrow-rotate-left icon-green"></i>
					<span class="u-hide-desktop"><?= _("Restore") ?></span>
					</a>
				</li>
			</ul>
		</div>
	</div>
	<?php }} ?>
</div>

<footer class="app-footer">
	<div class="container app-footer-inner">
		<p>
			<?php printf(ngettext("%d item", "%d items", $i), $i); ?>
		</p>
	</div>
</footer>
