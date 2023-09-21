<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/backup/"><i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?></a>
			<a href="/schedule/restore/?token=<?= $_SESSION["token"] ?>&backup=<?= htmlentities($_GET["backup"]) ?>" class="button button-secondary"><i class="fas fa-arrow-rotate-left icon-green"></i><?= _("Restore All") ?></a>
		</div>
		<div class="toolbar-right">
			<form x-data x-bind="BulkEdit" action="/bulk/restore/" method="post">
				<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
				<input type="hidden" name="backup" value="<?= htmlentities($_GET["backup"]) ?>">
				<select class="form-select" name="action">
					<option value=""><?= _("Apply to selected") ?></option>
					<option value="restore"><?= _("Restore") ?></option>
				</select>
				<button type="submit" class="toolbar-input-submit" title="<?= _("Apply to selected") ?>">
					<i class="fas fa-arrow-right"></i>
				</button>
			</form>
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
				<input type="checkbox" class="js-toggle-all-checkbox" title="<?= _("Select all") ?>" <?= $display_mode ?>>
			</div>
			<div class="units-table-cell"><?= _("Type") ?></div>
			<div class="units-table-cell"><?= _("Details") ?></div>
			<div class="units-table-cell"><?= _("Restore") ?></div>
		</div>

		<!-- List web domains -->
		<?php
			$backup = htmlentities($_GET['backup']);
			$web = explode(',',$data[$backup]['WEB']);
			foreach ($web as $key) {
				if (!empty($key)) {
					++$i;
			?>
			<div class="units-table-row js-unit">
				<div class="units-table-cell">
					<div>
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
							<a
								class="units-table-row-action-link"
								href="/schedule/restore/?backup=<?= $backup ?>&type=web&object=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
								title="<?= _("Restore") ?>"
							>
								<i class="fas fa-arrow-rotate-left icon-green"></i>
								<span class="u-hide-desktop"><?= _("Restore") ?></span>
							</a>
						</li>
					</ul>
				</div>
			</div>
		<?php }} ?>

		<!-- List mail domains -->
		<?php
			$mail = explode(',',$data[$backup]['MAIL']);
			foreach ($mail as $key) {
				if (!empty($key)) {
			?>
			<div class="units-table-row js-unit">
				<div class="units-table-cell">
					<div>
						<input id="check2<?= $i ?>" class="js-unit-checkbox" type="checkbox" name="mail[]" value="<?= $key ?>">
						<label for="check2<?= $i ?>" class="u-hide-desktop"><?= _("Select") ?></label>
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
							<a
								class="units-table-row-action-link"
								href="/schedule/restore/?backup=<?= $backup ?>&type=mail&object=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
								title="<?= _("Restore") ?>"
							>
								<i class="fas fa-arrow-rotate-left icon-green"></i>
								<span class="u-hide-desktop"><?= _("Restore") ?></span>
							</a>
						</li>
					</ul>
				</div>
			</div>
		<?php }} ?>

		<!-- List DNS zones -->
		<?php
			$dns = explode(',',$data[$backup]['DNS']);
			foreach ($dns as $key) {
				if (!empty($key)) {
			?>
			<div class="units-table-row js-unit">
				<div class="units-table-cell">
					<div>
						<input id="check3<?= $i ?>" class="js-unit-checkbox" type="checkbox" name="dns[]" value="<?= $key ?>">
						<label for="check3<?= $i ?>" class="u-hide-desktop"><?= _("Select") ?></label>
					</div>
				</div>
				<div class="units-table-cell units-table-heading-cell">
					<span class="u-hide-desktop u-text-bold"><?= _("Type") ?>:</span>
					<?= _("DNS Zone") ?>
				</div>
				<div class="units-table-cell u-text-bold">
					<span class="u-hide-desktop"><?= _("Details") ?>:</span>
					<?= $key ?>
				</div>
				<div class="units-table-cell">
					<ul class="units-table-row-actions">
						<li class="units-table-row-action shortcut-enter" data-key-action="href">
							<a
								class="units-table-row-action-link"
								href="/schedule/restore/?backup=<?= $backup ?>&type=dns&object=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
								title="<?= _("Restore") ?>"
							>
								<i class="fas fa-arrow-rotate-left icon-green"></i>
								<span class="u-hide-desktop"><?= _("Restore") ?></span>
							</a>
						</li>
					</ul>
				</div>
			</div>
		<?php }} ?>

		<!-- List Databases -->
		<?php
			$db = explode(',',$data[$backup]['DB']);
			foreach ($db as $key) {
				if (!empty($key)) {
			?>
			<div class="units-table-row js-unit">
				<div class="units-table-cell">
					<div>
						<input id="check4<?= $i ?>" class="js-unit-checkbox" type="checkbox" name="db[]" value="<?= $key ?>">
						<label for="check4<?= $i ?>" class="u-hide-desktop"><?= _("Select") ?></label>
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
							<a
								class="units-table-row-action-link"
								href="/schedule/restore/?backup=<?= $backup ?>&type=db&object=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
								title="<?= _("Restore") ?>"
							>
								<i class="fas fa-arrow-rotate-left icon-green"></i>
								<span class="u-hide-desktop"><?= _("Restore") ?></span>
							</a>
						</li>
					</ul>
				</div>
			</div>
		<?php }} ?>

		<!-- List Cron Jobs -->
		<?php if (!empty($data[$backup]["CRON"])) {
		if (!empty($key)) { ?>
			<div class="units-table-row js-unit">
				<div class="units-table-cell">
					<div>
						<input id="check5<?= $i ?>" class="js-unit-checkbox" type="checkbox" name="check" value="<?= $key ?>">
						<label for="check5<?= $i ?>" class="u-hide-desktop"><?= _("Select") ?></label>
					</div>
				</div>
				<div class="units-table-cell units-table-heading-cell">
					<span class="u-hide-desktop u-text-bold"><?= _("Type") ?>:</span>
					<?= _("Cron Jobs") ?>
				</div>
				<div class="units-table-cell u-text-bold">
					<span class="u-hide-desktop"><?= _("Details") ?>:</span>
					<?= _("Jobs") ?>
				</div>
				<div class="units-table-cell">
					<ul class="units-table-row-actions">
						<li class="units-table-row-action shortcut-enter" data-key-action="href">
							<a
								class="units-table-row-action-link"
								href="/schedule/restore/?backup=<?= $backup ?>&type=cron&object=records&token=<?= $_SESSION["token"] ?>"
								title="<?= _("Restore") ?>"
							>
								<i class="fas fa-arrow-rotate-left icon-green"></i>
								<span class="u-hide-desktop"><?= _("Restore") ?></span>
							</a>
						</li>
					</ul>
				</div>
			</div>
		<?php }} ?>

		<!-- List user directories -->
		<?php
			$udir = explode(',',$data[$backup]['UDIR']);
			foreach ($udir as $key) {
				if (!empty($key)) {
			?>
			<div class="units-table-row js-unit">
				<div class="units-table-cell">
					<div>
						<input id="check6<?= $i ?>" class="js-unit-checkbox" type="checkbox" name="udir[]" value="<?= $key ?>">
						<label for="check6<?= $i ?>" class="u-hide-desktop"><?= _("Select") ?></label>
					</div>
				</div>
				<div class="units-table-cell units-table-heading-cell">
					<span class="u-hide-desktop u-text-bold"><?= _("Type") ?>:</span>
					<?= _("User Directory") ?>
				</div>
				<div class="units-table-cell u-text-bold">
					<span class="u-hide-desktop"><?= _("Details") ?>:</span>
					<?= $key ?>
				</div>
				<div class="units-table-cell">
					<ul class="units-table-row-actions">
						<li class="units-table-row-action shortcut-enter" data-key-action="href">
							<a
								class="units-table-row-action-link"
								href="/schedule/restore/?backup=<?= $backup ?>&type=udir&object=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
								title="<?= _("Restore") ?>"
							>
								<i class="fas fa-arrow-rotate-left icon-green"></i>
								<span class="u-hide-desktop"><?= _("Restore") ?></span>
							</a>
						</li>
					</ul>
				</div>
			</div>
		<?php }} ?>
	</div>

</div>

<footer class="app-footer">
	<div class="container app-footer-inner">
		<p>
			<?php printf(ngettext("%d item", "%d items", $i), $i); ?>
		</p>
	</div>
</footer>
