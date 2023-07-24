<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/backup/">
				<i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
			</a>
			<a href="/edit/backup/exclusions/" class="button button-secondary">
				<i class="fas fa-pencil icon-orange"></i><?= _("Edit Backup Exclusions") ?>
			</a>
		</div>
		<div class="toolbar-right">
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

	<h1 class="u-text-center u-hide-desktop u-mt20 u-pr30 u-mb20 u-pl30"><?= _("Backup Exclusions") ?></h1>

	<div class="units-table js-units-container">
		<div class="units-table-header">
			<div class="units-table-cell"><?= _("Type") ?></div>
			<div class="units-table-cell"><?= _("Value") ?></div>
		</div>

		<!-- Begin list of backup exclusions by type -->
		<?php foreach ($data as $key => $value) { ?>
			<div class="units-table-row js-unit">
				<div class="units-table-cell units-table-heading-cell u-text-bold">
					<span class="u-hide-desktop"><?= _("Type") ?>:</span>
					<?= $key ?>
				</div>
				<div class="units-table-cell">
					<span class="u-hide-desktop u-text-bold"><?= _("Value") ?>:</span>
					<?php
						if (empty($value)) {
							echo _("No exclusions");
						}
						foreach ($value as $ex_key => $ex_value) {
							echo "<span class='u-text-bold'>" . $ex_key . " </span>" . $ex_value . "<br>";
						}
					?>
				</div>
			</div>
		<?php } ?>
	</div>

</div>

<footer class="app-footer">
</footer>
