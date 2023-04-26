<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-right">
		</div>
	</div>
</div>
<!-- End toolbar -->

<!-- Begin form -->
<div class="container animate__animated animate__fadeIn">
	<form id="vstobjects" name="v_generate_csr" method="post">
		<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">

		<div class="form-container">
			<h1 class="form-title"><?= _("Generate SSL Certificate") ?></h1>
			<?php show_alert_message($_SESSION); ?>
			<div
				x-data="{
					text: '<?= base64_encode($v_crt) ?>',
					blob() {
						return window.URL.createObjectURL(new Blob([atob(this.text)], { type: 'text/plain' }))
					}
				}"
				class="u-mb20"
			>
				<label for="v_crt" class="form-label">
					<?= _("SSL Certificate") ?>
					<a
						x-bind:href="blob()"
						download="<?= htmlentities($v_domain) ?>.crt"
					><i class="fas fa-download"></i></a>
				</label>
				<textarea
					x-model="atob(text)"
					class="form-control u-min-height100"
					name="v_crt"
					id="v_crt"
				></textarea>
			</div>
			<div
				x-data="{
					text: '<?= base64_encode($v_key) ?>',
					blob() {
						return window.URL.createObjectURL(new Blob([atob(this.text)], { type: 'text/plain' }))
					}
				}"
				class="u-mb20"
			>
				<label for="v_key" class="form-label">
					<?= _("SSL Certificate Key") ?>
					<a
						x-bind:href="blob()"
						download="<?= htmlentities($v_domain) ?>.key"
					><i class="fas fa-download"></i></a>
				</label>
				<textarea
					x-model="atob(text)"
					class="form-control u-min-height100"
					name="v_key"
					id="v_key"
				></textarea>
			</div>
			<div
				x-data="{
					text: '<?= base64_encode($v_csr) ?>',
					blob() {
						return window.URL.createObjectURL(new Blob([atob(this.text)], { type: 'text/plain' }))
					}
				}"
				class="u-mb20"
			>
				<label for="v_csr" class="form-label">
					<?= _("SSL Certificate CSR") ?>
					<a
						x-bind:href="blob()"
						download="<?= htmlentities($v_domain) ?>.csr"
					><i class="fas fa-download"></i></a>
				</label>
				<textarea
					x-model="atob(text)"
					class="form-control u-min-height100"
					name="v_csr"
					id="v_csr"
				></textarea>
			</div>
			<div>
				<button type="button" class="button button-secondary" onclick="<?= $back ?>">
					<?= _("Back") ?>
				</button>
			</div>
		</div>
	</form>
</div>
<!-- End form -->
