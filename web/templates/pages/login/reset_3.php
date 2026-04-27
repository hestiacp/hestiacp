<div class="login">
	<a href="/" class="u-block u-mb40">
		<img src="/images/logo.svg" alt="<?= tohtml($_SESSION["APP_NAME"]) ?>" width="100" height="120">
	</a>
	<form method="post">
		<h1 class="login-title">
			<?= tohtml( _("Forgot Password")) ?>
		</h1>
		<?php if (!empty($error)) { ?>
			<p class="error"><?= tohtml($error) ?></p>
		<?php } ?>
		<div class="u-mb10">
			<input type="hidden" name="action" value="confirm">
			<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
			<input type="hidden" name="user" value="<?= tohtml($_GET["user"]) ?>">
			<input type="hidden" name="code" value="<?= tohtml($_GET["code"]) ?>">
			<label for="password" class="form-label"><?= tohtml( _("New Password")) ?></label>
			<input type="password" class="form-control" name="password" id="password" autocomplete="new-password" required autofocus>
		</div>
		<div class="u-mb20">
			<label for="password_confirm" class="form-label"><?= tohtml( _("Confirm Password")) ?></label>
			<input type="password" class="form-control" name="password_confirm" id="password_confirm" autocomplete="new-password" required>
		</div>
		<div class="u-side-by-side">
			<button type="submit" class="button">
				<?= tohtml( _("Reset")) ?>
			</button>
			<a href="/login/" class="button button-secondary">
				<?= tohtml( _("Back")) ?>
			</a>
		</div>
	</form>
</div>
