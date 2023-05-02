<div class="login animate__animated animate__zoomIn">
	<a href="/" class="u-block u-mb40">
		<img src="/images/logo.svg" alt="<?= _("Hestia Control Panel") ?>" width="100" height="120">
	</a>
	<?php if ($success) { ?>
		<div>
			<h1 class="login-title">
				<?= _("2FA Reset successfully") ?>
			</h1>
			<?php show_error_message($ERROR); ?>
			<div class="u-mt20">
				<a href="/login/" class="button button-secondary">
					<?= _("Log in") ?>
				</a>
			</div>
		</div>
	<?php } else { ?>
		<form method="post" action="/reset2fa/">
			<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
			<h1 class="login-title">
				<?= _("Reset 2FA") ?>
			</h1>
			<?php show_error_message($ERROR); ?>
			<div class="u-mb10">
				<label for="user" class="form-label"><?= _("Username") ?></label>
				<input type="text" class="form-control" name="user" id="user" required>
			</div>
			<div class="u-mb20">
				<label for="twofa" class="form-label"><?= _("2FA Reset Code") ?></label>
				<input type="text" class="form-control" name="twofa" id="twofa" required>
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
