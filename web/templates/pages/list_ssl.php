<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-right">
		</div>
	</div>
</div>
<!-- End toolbar -->

<!-- Begin form -->
<div class="container">
	<form id="main-form" name="v_generate_csr" method="post">
		<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">

		<div class="form-container">
			<h1 class="u-mb20"><?= _("Generate Self-Signed SSL Certificate") ?></h1>
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
				<label for="v_crt" class="form-label u-side-by-side">
					<?= _("SSL Certificate") ?>
					<a
						x-bind:href="blob()"
						download="<?= htmlentities($v_domain) ?>.crt"
						title="<?= _("Download") ?>"
					>
						<i class="fas fa-download"></i>
						<span class="u-hidden"><?= _("Download") ?></span>
					</a>
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
				<label for="v_key" class="form-label u-side-by-side">
					<?= _("SSL Private Key") ?>
					<a
						x-bind:href="blob()"
						download="<?= htmlentities($v_domain) ?>.key"
						title="<?= _("Download") ?>"
					>
						<i class="fas fa-download"></i>
						<span class="u-hidden"><?= _("Download") ?></span>
					</a>
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
				<label for="v_csr" class="form-label u-side-by-side">
					<?= _("SSL CSR") ?>
					<a
						x-bind:href="blob()"
						download="<?= htmlentities($v_domain) ?>.csr"
						title="<?= _("Download") ?>"
					>
						<i class="fas fa-download"></i>
						<span class="u-hidden"><?= _("Download") ?></span>
					</a>
				</label>
				<textarea
					x-model="atob(text)"
					class="form-control u-min-height100"
					name="v_csr"
					id="v_csr"
				></textarea>
			</div>
		</div>
	</form>
</div>
<!-- End form -->
