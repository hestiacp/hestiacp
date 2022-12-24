<div class="login animate__animated animate__zoomIn">
	<a href="/" class="u-block u-mr30 u-mb40">
		<img src="/images/logo.svg" alt="<?= _("Hestia Control Panel") ?>" width="100" height="120">
	</a>
	<form id="form_login" method="post" action="/login/">
		<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
		<input type="hidden" name="murmur" value="" id="murmur">
		<h1 class="login-title">
			<?= _("Welcome to Hestia Control Panel") ?>
		</h1>
		<?php show_error_message($error); ?>
		<div class="u-mb20">
			<label for="user" class="form-label"><?= _("Username") ?></label>
			<input type="text" class="form-control" name="user" id="user" autofocus>
		</div>
		<button type="submit" class="button">
			<i class="fas fa-right-to-bracket"></i><?= _("Next") ?>
		</button>
	</form>
</div>

</body>

</html>
