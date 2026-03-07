<div class="login">
	<a href="/" class="u-block u-mb40">
		<img src="/images/logo.svg" alt="<?= tohtml($_SESSION["APP_NAME"]) ?>" width="100" height="120">
	</a>
	<form id="login-form" method="post" action="/login/">
		<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
		<h1 class="login-title">
			<?= tohtml( _("Welcome")) ?> <?= tohtml($_SESSION["login"]["username"]) ?>!
		</h1>
		<div class="u-mb20">
			<label for="password" class="form-label u-side-by-side">
				<?= tohtml( _("Password")) ?>
				<?php if ($_SESSION["POLICY_SYSTEM_PASSWORD_RESET"] !== "no") { ?>
					<a class="login-form-link" href="/reset/">
						<?= tohtml( _("Forgot Password")) ?>
					</a>
				<?php } ?>
			</label>
			<input type="password" class="form-control" name="password" id="password" autocomplete="current-password" required autofocus>
		</div>
		<div class="u-side-by-side">
			<button type="submit" class="button">
				<i class="fas fa-right-to-bracket"></i><?= tohtml( _("Login")) ?>
			</button>
			<a href="/login/?logout=true" class="button button-secondary">
				<?= tohtml( _("Back")) ?>
			</a>
		</div>
	</form>
</div>
