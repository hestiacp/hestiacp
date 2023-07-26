<div class="login">
	<a href="/" class="u-block u-mb40">
		<img src="/images/logo.svg" alt="<?= htmlentities($_SESSION["APP_NAME"]) ?>" width="100" height="120">
	</a>
	<?php if ($success) { ?>
		<div>
			<h1 class="login-title">
				<?= _("Account Unlocked") ?>
			</h1>
			<div class="u-mt20">
				<p><?_("Two-factor authentication is now turned off for your account.<br><br>You may now proceed to log in.");?></p>
				<a href="/login/" class="button button-secondary">
					<?= _("Log in") ?>
				</a>
			</div>
		</div>
	<?php } else { ?>
		<form method="post" action="/reset2fa/">
			<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
			<h1 class="login-title">
				<?= _("Unlock Account") ?>
			</h1>
			<?php if (!empty($error)) { ?>
				<p class="error"><?= $error ?></p>
			<?php } ?>
			<div class="u-mb10">
				<label for="user" class="form-label"><?= _("Username") ?></label>
				<input type="text" class="form-control" name="user" id="user" autocomplete="username" required autofocus>
			</div>
			<div class="u-mb20">
				<label for="twofa" class="form-label"><?= _("2FA Reset Code") ?></label>
				<input type="text" class="form-control" name="twofa" id="twofa" autocomplete="off" required>
			</div>
			<div class="u-side-by-side">
				<button type="submit" class="button">
					<?= _("Submit") ?>
				</button>
				<a href="/login/?logout" class="button button-secondary">
					<?= _("Back") ?>
				</a>
			</div>
		</form>
	<?php } ?>
</div>

</body>

</html>
