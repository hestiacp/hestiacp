<div class="login animate__animated animate__zoomIn">
	<a href="/" class="u-block u-mb40">
		<img src="/images/logo.svg" alt="<?= _("Hestia Control Panel") ?>" width="100" height="120">
	</a>
	<form id="form_login" method="post" action="/login/">
		<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
		<input type="hidden" name="murmur" value="" id="murmur">
		<h1 class="login-title">
			<?= _("2 Factor Authentication") ?>
		</h1>
		<?php show_error_message($ERROR); ?>
		<div class="u-mb20">
			<label for="twofa" class="form-label u-side-by-side">
				<?= _("2FA Token") ?>
				<a class="login-form-link" href="/reset2fa/">
					<?= _("Forgot token") ?>
				</a>
			</label>
			<input type="text" class="form-control" name="twofa" id="twofa" required autofocus>
		</div>
		<div class="u-side-by-side">
			<button type="submit" class="button">
				<i class="fas fa-right-to-bracket"></i><?= _("Login") ?>
			</button>
			<a href="/login/?logout" class="button button-secondary">
				<?= _("Back") ?>
			</a>
		</div>
	</form>
</div>

</body>

</html>
