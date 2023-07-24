<div class="login">
	<a href="/" class="u-block u-mb40">
		<img src="/images/logo.svg" alt="<?= htmlentities($_SESSION["APP_NAME"]) ?>" width="100" height="120">
	</a>
	<form method="get" action="/reset/">
		<h1 class="login-title">
			<?= _("Forgot Password") ?>
		</h1>
		<?php if (!empty($error)) { ?>
			<p class="error"><?= $error ?></p>
		<?php } ?>
		<p class="inline-success u-mb10">
			<?= _("Password reset code has been sent to your email address") ?>
		</p>
		<div class="u-mb20">
			<input type="hidden" name="action" value="confirm">
			<input type="hidden" name="token" value="<?= htmlentities($_SESSION["token"]) ?>">
			<input type="hidden" name="user" value="<?= htmlentities($_GET["user"]) ?>">
			<label for="code" class="form-label"><?= _("Reset Code") ?></label>
			<input type="text" class="form-control" name="code" id="code" required autofocus>
		</div>
		<div class="u-side-by-side">
			<button type="submit" class="button">
				<?= _("Confirm") ?>
			</button>
			<a href="/reset/" class="button button-secondary">
				<?= _("Back") ?>
			</a>
		</div>
	</form>
</div>

</body>

</html>
