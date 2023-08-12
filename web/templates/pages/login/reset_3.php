<div class="login">
	<a href="/" class="u-block u-mb40">
		<img src="/images/logo.svg" alt="<?= htmlentities($_SESSION["APP_NAME"]) ?>" width="100" height="120">
	</a>
	<form method="post">
		<h1 class="login-title">
			<?= _("Forgot Password") ?>
		</h1>
		<?php if (!empty($error)) { ?>
			<p class="error"><?= $error ?></p>
		<?php } ?>
		<div class="u-mb10">
			<input type="hidden" name="action" value="confirm">
			<input type="hidden" name="token" value="<?= htmlentities($_SESSION["token"]) ?>">
			<input type="hidden" name="user" value="<?= htmlentities($_GET["user"]) ?>">
			<input type="hidden" name="code" value="<?= htmlentities($_GET["code"]) ?>">
			<label for="password" class="form-label"><?= _("New Password") ?></label>
			<input type="password" class="form-control" name="password" id="password" autocomplete="new-password" required autofocus>
		</div>
		<div class="u-mb20">
			<label for="password_confirm" class="form-label"><?= _("Confirm Password") ?></label>
			<input type="password" class="form-control" name="password_confirm" id="password_confirm" autocomplete="new-password" required>
		</div>
		<div class="u-side-by-side">
			<button type="submit" class="button">
				<?= _("Reset") ?>
			</button>
			<a href="/login/" class="button button-secondary">
				<?= _("Back") ?>
			</a>
		</div>
	</form>
</div>

</body>

</html>
