<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a href="/edit/server/" class="button button-secondary button-back js-button-back">
				<i class="fas fa-arrow-left icon-blue"></i><?= tohtml( _("Back")) ?>
			</a>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container">

	<h1 class="u-text-center u-hide-desktop u-mt20 u-pr30 u-mb20 u-pl30"><?= tohtml( _("Preview Features")) ?></h1>

	<div class="units-table js-units-container">
		<div class="units-table-header">
			<div class="units-table-cell"></div>
			<div class="units-table-cell"><?= tohtml( _("Category")) ?></div>
			<div class="units-table-cell"><?= tohtml( _("Name")) ?></div>
			<div class="units-table-cell"><?= tohtml( _("Status")) ?></div>
		</div>

		<div class="units-table-row js-unit">
			<div class="units-table-cell u-text-center-desktop">
				<i class="fas fa-gear icon-blue"></i>
			</div>
			<div class="units-table-cell units-table-heading-cell u-text-bold">
				<span class="u-hide-desktop"><?= tohtml( _("Category")) ?>:</span>
				<?= tohtml( _("System")) ?>
			</div>
			<div class="units-table-cell u-text-bold">
				<span class="u-hide-desktop"><?= tohtml( _("Name")) ?>:</span>
				<?= tohtml( _("Policy")) ?>: <?= tohtml( _("Allow suspended users to log in with read-only access")) ?>
			</div>
			<div class="units-table-cell">
				<span class="u-hide-desktop u-text-bold"><?= tohtml( _("Status")) ?>:</span>
				<?= tohtml( _("Partially implemented")) ?>
			</div>
		</div>
	</div>

</div>
