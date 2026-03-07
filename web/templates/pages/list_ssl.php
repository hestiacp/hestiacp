<!-- Begin form -->
<div class="container">
	<form id="main-form" name="v_generate_csr" method="post">
		<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">

		<div class="form-container">
			<h1 class="u-mb20"><?= tohtml( _("Generate Self-Signed SSL Certificate")) ?></h1>
			<?php show_alert_message($_SESSION); ?>
			<div
				x-data="{
					text: '<?= tohtml(base64_encode($v_crt)) ?>',
					blob() {
						return window.URL.createObjectURL(new Blob([atob(this.text)], { type: 'text/plain' }))
					}
				}"
				class="u-mb20"
			>
				<label for="v_crt" class="form-label u-side-by-side">
					<?= tohtml( _("SSL Certificate")) ?>
					<a
						x-bind:href="blob()"
						download="<?= tohtml( htmlentities($v_domain)) ?>.crt"
						title="<?= tohtml( _("Download")) ?>"
					>
						<i class="fas fa-download"></i>
						<span class="u-hidden"><?= tohtml( _("Download")) ?></span>
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
					text: '<?= tohtml(base64_encode($v_key)) ?>',
					blob() {
						return window.URL.createObjectURL(new Blob([atob(this.text)], { type: 'text/plain' }))
					}
				}"
				class="u-mb20"
			>
				<label for="v_key" class="form-label u-side-by-side">
					<?= tohtml( _("SSL Private Key")) ?>
					<a
						x-bind:href="blob()"
						download="<?= tohtml( htmlentities($v_domain)) ?>.key"
						title="<?= tohtml( _("Download")) ?>"
					>
						<i class="fas fa-download"></i>
						<span class="u-hidden"><?= tohtml( _("Download")) ?></span>
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
					text: '<?= tohtml(base64_encode($v_csr)) ?>',
					blob() {
						return window.URL.createObjectURL(new Blob([atob(this.text)], { type: 'text/plain' }))
					}
				}"
				class="u-mb20"
			>
				<label for="v_csr" class="form-label u-side-by-side">
					<?= tohtml( _("SSL CSR")) ?>
					<a
						x-bind:href="blob()"
						download="<?= tohtml( htmlentities($v_domain)) ?>.csr"
						title="<?= tohtml( _("Download")) ?>"
					>
						<i class="fas fa-download"></i>
						<span class="u-hidden"><?= tohtml( _("Download")) ?></span>
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
