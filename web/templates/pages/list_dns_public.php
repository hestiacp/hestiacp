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

	<div class="form-container">
		<h1 class="u-mb20"><?= _("View Public DNSSEC Key") ?></h1>
		<div class="u-mb10">
			<label class="form-label"><?= _("DNSKEY Record") ?></label>
			<input type="text" class="form-control" value="<?php echo $data[$domain]["RECORD"]; ?>" readonly>
		</div>
		<div class="u-mb10">
			<label class="form-label"><?= _("DS Record") ?></label>
			<input type="text" class="form-control" value="<?php echo $data[$domain]["DS"]; ?>" readonly>
		</div>
		<div class="u-mb10">
			<label class="form-label"><?= _("Public Key") ?></label>
			<input type="text" class="form-control" value="<?php echo $data[$domain]["KEY"]; ?>" readonly>
		</div>
		<div class="u-mb10">
			<label class="form-label"><?= _("Key Type / Flag") ?></label>
			<input type="text" class="form-control" value="<?php echo $flag; ?>" readonly>
		</div>
		<div class="u-mb10">
			<label class="form-label"><?= _("Key Tag") ?></label>
			<input type="text" class="form-control" value="<?php echo $data[$domain]["KEYTAG"]; ?>" readonly>
		</div>
		<div class="u-mb10">
			<label class="form-label"><?= _("Flag") ?></label>
			<input type="text" class="form-control" value="<?php echo $data[$domain]["FLAG"]; ?>" readonly>
		</div>
		<div class="u-mb10">
			<label class="form-label"><?= _("Algorithm") ?></label>
			<input type="text" class="form-control" value="<?php echo $algorithm; ?>" readonly>
		</div>
	</div>

</div>
