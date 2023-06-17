<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<?php if ($read_only !== "true") { ?>
				<a href="/add/dns/" class="button button-secondary js-button-create">
					<i class="fas fa-circle-plus icon-green"></i><?= _("Add DNS Domain") ?>
				</a>
			<?php } ?>
		</div>
		<div class="toolbar-right">
			<div class="toolbar-search">
				<form action="/search/" method="get">
					<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>" />
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

	<div class="units-table js-units-container">
		<div class="units-table-row animate__animated animate__fadeIn js-unit">
			<div class="units-table-cell u-text-bold">
				<?= _("DNSKEY Record") ?>
			</div>
			<div class="units-table-cell">
				<label class="u-hidden-visually"><?= _("DNSKEY Record") ?></label>
				<input type="text" class="form-control" value="<?php echo $data[$domain]["RECORD"]; ?>">
			</div>
		</div>
		<div class="units-table-row animate__animated animate__fadeIn js-unit">
			<div class="units-table-cell u-text-bold">
				<?= _("DS Record") ?>
			</div>
			<div class="units-table-cell">
				<label class="u-hidden-visually"><?= _("DS Record") ?></label>
				<input type="text" class="form-control" value="<?php echo $data[$domain]["RECORD"]; ?>">
			</div>
		</div>
		<div class="units-table-row animate__animated animate__fadeIn js-unit">
			<div class="units-table-cell u-text-bold">
				<?= _("Public Key") ?>
			</div>
			<div class="units-table-cell">
				<label class="u-hidden-visually"><?= _("Public Key") ?></label>
				<input type="text" class="form-control" value="<?php echo $data[$domain]["KEY"]; ?>">
			</div>
		</div>
		<div class="units-table-row animate__animated animate__fadeIn js-unit">
			<div class="units-table-cell u-text-bold">
				<?= _("Key Type / Flag") ?>
			</div>
			<div class="units-table-cell">
				<label class="u-hidden-visually"><?= _("Key Type / Flag") ?></label>
				<input type="text" class="form-control" value="<?php echo $flag; ?>">
			</div>
		</div>
		<div class="units-table-row animate__animated animate__fadeIn js-unit">
			<div class="units-table-cell u-text-bold">
				<?= _("Key Tag") ?>
			</div>
			<div class="units-table-cell">
				<label class="u-hidden-visually"><?= _("Key Tag") ?></label>
				<input type="text" class="form-control" value="<?php echo $data[$domain]["KEYTAG"]; ?>">
			</div>
		</div>
		<div class="units-table-row animate__animated animate__fadeIn js-unit">
			<div class="units-table-cell u-text-bold">
				<?= _("Flag") ?>
			</div>
			<div class="units-table-cell">
				<label class="u-hidden-visually"><?= _("Flag") ?></label>
				<input type="text" class="form-control" value="<?php echo $data[$domain]["FLAG"]; ?>">
			</div>
		</div>
		<div class="units-table-row animate__animated animate__fadeIn js-unit">
			<div class="units-table-cell u-text-bold">
				<?= _("Algorithm") ?>
			</div>
			<div class="units-table-cell">
				<label class="u-hidden-visually"><?= _("Algorithm") ?></label>
				<input type="text" class="form-control" value="<?php echo $algorithm; ?>">
			</div>
		</div>
	</div>

</div>
