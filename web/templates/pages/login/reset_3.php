<div class="login animate__animated animate__zoomIn">
	<a href="/" class="u-block u-mr30 u-mb40">
		<img src="/images/logo.svg" alt="<?= _("Hestia Control Panel") ?>" width="100" height="120">
	</a>
	<form method="post">
		<h1 class="login-title">
			<?= _("Forgot Password") ?>
		</h1>
		<?php if (isset($ERROR)) echo $ERROR ?>
		<div class="u-mb10">
			<input type="hidden" name="action" value="confirm">
			<input type="hidden" name="token" value="<?= htmlentities($_SESSION["token"]) ?>">
			<input type="hidden" name="user" value="<?= htmlentities($_GET["user"]) ?>">
			<input type="hidden" name="code" value="<?= htmlentities($_GET["code"]) ?>">
			<label for="password" class="form-label"><?= _("New Password") ?></label>
			<input type="password" class="form-control" name="password" id="password">
		</div>
		<div class="u-mb20">
			<label for="password_confirm" class="form-label"><?= _("Confirm Password") ?></label>
			<input type="password" class="form-control" name="password_confirm" id="password_confirm">
		</div>
		<div class="u-side-by-side">
			<button type="submit" class="button">
				<?= _("Reset") ?>
			</button>
			<button type="button" class="button button-secondary" onclick="location.href='/login/'">
				<?= _("Back") ?>
			</button>
		</div>
	</form>
</div>

</body>

</html>
