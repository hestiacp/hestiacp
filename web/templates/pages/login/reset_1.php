<div class="login">
	<a href="/" class="u-block u-mb40">
		<img src="/images/logo.svg" alt="<?= tohtml( htmlentities($_SESSION["APP_NAME"])) ?>" width="100" height="120">
	</a>
	<form method="post" action="/reset/">
		<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
		<h1 class="login-title">
			<?= tohtml( _("Forgot Password")) ?>
		</h1>
		<?php if (!empty($error)) { ?>
			<p class="error"><?= tohtml($error) ?></p>
		<?php } ?>
		<div class="u-mb10">
			<label for="username" class="form-label"><?= tohtml( _("Username")) ?></label>
			<input type="text" class="form-control" name="user" id="username" autocomplete="username" required autofocus>
		</div>
		<div class="u-mb20">
			<label for="email" class="form-label"><?= tohtml( _("Email")) ?></label>
			<input type="email" class="form-control" name="email" id="email" autocomplete="email" required>
		</div>
		<div class="u-side-by-side">
			<button type="submit" class="button">
				<?= tohtml( _("Submit")) ?>
			</button>
			<a href="/login/?logout" class="button button-secondary">
				<?= tohtml( _("Back")) ?>
			</a>
		</div>
	</form>
</div>
