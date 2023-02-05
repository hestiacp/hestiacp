<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-right">

		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container animate__animated animate__fadeIn">

	<form id="vstobjects" name="v_generate_csr" method="post">
		<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">

		<div class="form-container">
			<h1 class="form-title"><?= _("Generating CSR") ?></h1>
			<?php show_alert_message($_SESSION); ?>
			<div class="u-mb20">
				<label for="v_crt" class="form-label">
					<?= _("SSL Certificate") ?>
					<a href="javascript:saveTextToBlob('<?php echo htmlentities($v_domain); ?>.crt', 'v_crt');"><i class="fas fa-download"></i></a>
				</label>
				<textarea class="form-control u-min-height100" name="v_crt" id="v_crt"><?= $v_crt ?></textarea>
			</div>
			<div class="u-mb20">
				<label for="v_key" class="form-label">
					<?= _("SSL Key") ?>
					<a href="javascript:saveTextToBlob('<?php echo htmlentities($v_domain); ?>.key', 'v_key');"><i class="fas fa-download"></i></a>
				</label>
				<textarea class="form-control u-min-height100" name="v_key" id="v_key"><?= $v_key ?></textarea>
			</div>
			<div class="u-mb20">
				<label for="v_csr" class="form-label">
					<?= _("SSL CSR") ?>
					<a href="javascript:saveTextToBlob('<?php echo htmlentities($v_domain); ?>.csr', 'v_crt');"><i class="fas fa-download"></i></a>
				</label>
				<textarea class="form-control u-min-height100" name="v_csr" id="v_csr"><?= $v_csr ?></textarea>
			</div>
			<div>
				<button type="button" class="button button-secondary" onclick="<?= $back ?>">
					<?= _("Back") ?>
				</button>
			</div>
		</div>

	</form>

</div>
