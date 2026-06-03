<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/user/">
				<i class="fas fa-arrow-left icon-blue"></i><?= tohtml( _("Back")) ?>
			</a>
		</div>
		<div class="toolbar-buttons">
			<button type="submit" class="button" form="main-form">
				<i class="fas fa-floppy-disk icon-purple"></i><?= tohtml( _("Save")) ?>
			</button>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container">

	<form
		x-data="{
			loginDisabled: <?= tohtml($v_login_disabled == "yes" ? "true" : "false") ?>
		}"
		id="main-form"
		name="v_add_user"
		method="post"
	>
		<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
		<input type="hidden" name="ok" value="Add">

		<div class="form-container">
			<h1 class="u-mb20"><?= tohtml( _("Add User")) ?></h1>
			<?php show_alert_message($_SESSION); ?>
			<div class="u-mb10">
				<label for="v_username" class="form-label"><?= tohtml( _("Username")) ?></label>
				<input type="text" class="form-control" name="v_username" id="v_username" value="<?= tohtml(trim($v_username, "'")) ?>" tabindex="1" required>
			</div>
			<div class="u-mb10">
				<label for="v_name" class="form-label"><?= tohtml( _("Contact Name")) ?></label>
				<input type="text" class="form-control" name="v_name" id="v_name" value="<?= tohtml(trim($v_name, "'")) ?>" tabindex="2" required>
			</div>
			<div class="u-mb10">
				<label for="v_email" class="form-label"><?= tohtml( _("Email")) ?></label>
				<input type="email" class="form-control js-sync-email-input" name="v_email" id="v_email" value="<?= tohtml(trim($v_email, "'")) ?>" tabindex="3" required>
			</div>
			<div class="u-mb10">
				<label for="v_password" class="form-label">
					<?= tohtml( _("Password")) ?>
					<button type="button" title="<?= tohtml( _("Generate")) ?>" class="u-unstyled-button u-ml5 js-generate-password">
						<i class="fas fa-arrows-rotate icon-green"></i>
					</button>
				</label>
				<div class="u-pos-relative u-mb10">
					<input type="text" class="form-control js-password-input" name="v_password" id="v_password" value="<?= tohtml(trim($v_password, "'")) ?>" tabindex="4" required>
					<div class="password-meter">
						<meter max="4" class="password-meter-input js-password-meter"></meter>
					</div>
				</div>
			</div>
			<?php require $_SERVER["HESTIA"] . "/web/templates/includes/password-requirements.php"; ?>
			<div class="form-check">
				<input x-model="loginDisabled" class="form-check-input" type="checkbox" name="v_login_disabled" id="v_login_disabled">
				<label for="v_login_disabled">
					<?= tohtml( _("Do not allow user to log in to Control Panel")) ?>
				</label>
			</div>
			<div x-cloak x-show="!loginDisabled" id="send-welcome">
				<div class="form-check u-mb10">
					<input class="form-check-input js-sync-email-checkbox" type="checkbox" name="v_email_notice" id="v_email_notify" tabindex="5">
					<label for="v_email_notify">
						<?= tohtml( _("Send welcome email")) ?>
					</label>
				</div>
			</div>
			<div class="u-mb10">
				<label for="v_language" class="form-label"><?= tohtml( _("Language")) ?></label>
				<select class="form-select" name="v_language" id="v_language" tabindex="6" required>
					<?php
						foreach ($languages as $key => $value) {
							echo "\n\t\t\t\t\t\t\t\t\t<option value=\"".htmlentities($key)."\"";
							if (( $key == $_SESSION['LANGUAGE'] ) && (empty($v_language))){
								echo ' selected' ;
							}
							if (isset($v_language)){
								if ( htmlentities($key) == trim($v_language,"'") ){
									echo ' selected' ;
								}
							}
							echo ">".htmlentities($value)."</option>\n";
						}
					?>
				</select>
			</div>
			<div class="u-mb10">
				<label for="v_role" class="form-label"><?= tohtml( _("Role")) ?></label>
				<select class="form-select" name="v_role" id="v_role" required>
					<option value="user"><?= tohtml( _("User")) ?></option>
					<option value="admin" <?= tohtml($v_role == "admin" ? "selected" : "") ?>><?= tohtml( _("Administrator")) ?></option>
					<option value="dns-cluster" <?= tohtml($v_role == "dns-cluster" ? "selected" : "") ?>><?= tohtml( _("DNS Sync User")) ?></option>
				</select>
			</div>
			<div class="u-mb10">
				<label for="v_package" class="form-label"><?= tohtml( _("Package")) ?></label>
				<select class="form-select" name="v_package" id="v_package" tabindex="8" required>
					<?php
						foreach ($data as $key => $value) {
							echo "\n\t\t\t\t\t\t\t\t\t\t\t\t\t<option value=\"".htmlentities($key)."\"";
							if ((!empty($v_package)) && ( $key == $_POST['v_package'])){
								echo 'selected' ;
							} else {
								if ( $key == 'default'){
									echo 'selected' ;
								}
							}
							echo ">".htmlentities($key)."</option>\n";
						}
					?>
				</select>
			</div>
			<div class="u-mb10">
				<label for="v_notify" class="form-label">
					<?= tohtml( _("Email login credentials to:")) ?>
				</label>
				<input type="email" class="form-control js-sync-email-output" name="v_notify" id="v_notify" value="<?= tohtml(trim($v_notify, "'")) ?>" tabindex="8">
			</div>
		</div>

	</form>

</div>
