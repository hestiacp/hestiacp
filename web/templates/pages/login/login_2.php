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
				<?= tohtml( _("2FA Token")) ?>
				<a class="login-form-link" href="/reset2fa/">
					<?= tohtml( _("Forgot Token")) ?>
				</a>
			</label>
			<input type="text" class="form-control" name="twofa" id="twofa" autocomplete="one-time-code" required autofocus>
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
