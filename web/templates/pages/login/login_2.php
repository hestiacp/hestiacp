<div class="login">
	<a href="/" class="u-block u-mb40">
		<img src="/images/logo.svg" alt="<?= tohtml($_SESSION["APP_NAME"]) ?>" width="100" height="120">
	</a>
	<form id="login-form" method="post" action="/login/">
		<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
		<h1 class="login-title">
			<?= tohtml( _("Two-factor Authentication")) ?>
		</h1>
		<?php if (!empty($error)) { ?>
			<p class="error"><?= tohtml($error) ?></p>
		<?php } ?>
		<div class="u-mb20">
			<label for="twofa" class="form-label u-side-by-side">
				<?= tohtml( _("Authentication Code")) ?>
				<a class="login-form-link" href="/reset2fa/">
					<?= tohtml( _("Lost your device?")) ?>
				</a>
			</label>
			<input type="text" class="form-control" name="twofa" id="twofa" autocomplete="one-time-code" required autofocus>
			<p class="u-mt5"><?= tohtml( _("Enter the 6-digit code from your authenticator app, or one of your backup codes.")) ?></p>
		</div>
		<div class="u-side-by-side">
			<button type="submit" class="button">
				<i class="fas fa-right-to-bracket"></i><?= tohtml( _("Login")) ?>
			</button>
			<a href="/login/?logout" class="button button-secondary">
				<?= tohtml( _("Back")) ?>
			</a>
		</div>
	</form>
</div>
