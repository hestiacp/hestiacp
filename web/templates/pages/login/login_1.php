<div class="login animate__animated animate__zoomIn">
	<a href="/" class="u-block u-mr30 u-mb40">
		<img src="/images/logo.svg" alt="<?= _("Hestia Control Panel") ?>" width="100" height="120">
	</a>
	<form id="form_login" method="post" action="/login/">
		<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
		<input type="hidden" name="murmur" value="" id="murmur">
		<h1 class="login-title">
			<?= _("Welcome") ?> <?= htmlspecialchars($_SESSION["login"]["username"]) ?>!
		</h1>
		<?php if (isset($ERROR)) echo $ERROR ?>
		<div class="u-mb20">
			<label for="password" class="form-label u-side-by-side">
				<?= _("Password") ?>
				<?php if ($_SESSION["POLICY_SYSTEM_PASSWORD_RESET"] !== "no") { ?>
					<a class="login-label-link" href="/reset/">
						<?= _("forgot password") ?>
					</a>
				<?php } ?>
			</label>
			<input type="password" class="form-control" name="password" id="password" autofocus>
		</div>
		<div class="u-side-by-side">
			<button type="submit" class="button">
				<i class="fas fa-right-to-bracket"></i><?= _("Login") ?>
			</button>
			<button type="button" class="button button-secondary" onclick="location.href='/login/?logout=true'">
				<?= _("Back") ?>
			</button>
		</div>
	</form>
</div>

</body>

</html>
