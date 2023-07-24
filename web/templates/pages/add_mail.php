<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/mail/">
				<i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
			</a>
		</div>
		<div class="toolbar-buttons">
			<?php if (($user_plain == "admin" && $accept === "true") || $user_plain !== "admin") { ?>
				<button type="submit" class="button" form="main-form">
					<i class="fas fa-floppy-disk icon-purple"></i><?= _("Save") ?>
				</button>
			<?php } ?>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container">

	<form
		x-data="{
			hasSmtpRelay: <?= $v_smtp_relay == "true" ? "true" : "false" ?>
		}"
		id="main-form"
		name="v_add_mail"
		method="post"
	>
		<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
		<input type="hidden" name="ok" value="Add">

		<div class="form-container">
			<h1 class="u-mb20"><?= _("Add Mail Domain") ?></h1>
			<?php show_alert_message($_SESSION); ?>
			<?php if ($user_plain == "admin" && $accept !== "true") { ?>
				<div class="alert alert-danger" role="alert">
					<i class="fas fa-exclamation"></i>
					<p><?= htmlify_trans(sprintf(_("It is strongly advised to {create a standard user account} before adding %s to the server due to the increased privileges the admin account possesses and potential security risks."), _('a mail domain')), '</a>', '<a href="/add/user/">'); ?></p>
				</div>
			<?php } ?>
			<?php if ($user_plain == "admin" && empty($accept)) { ?>
				<div class="u-side-by-side u-mt20">
					<a href="/add/user/" class="button u-width-full u-mr10"><?= _("Add User") ?></a>
					<a href="/add/mail/?accept=true" class="button button-danger u-width-full u-ml10"><?= _("Continue") ?></a>
				</div>
			<?php } ?>
			<?php if (($user_plain == "admin" && $accept === "true") || $user_plain !== "admin") { ?>
				<div class="u-mb20">
					<label for="v_domain" class="form-label"><?= _("Domain") ?></label>
					<input type="text" class="form-control" name="v_domain" id="v_domain" value="<?= htmlentities(trim($v_domain, "'")) ?>" required>
				</div>
				<?php if ($_SESSION["WEBMAIL_SYSTEM"]) { ?>
					<div class="u-mb20">
						<label for="v_webmail" class="form-label"><?= _("Webmail Client") ?></label>
						<select class="form-select" name="v_webmail" id="v_webmail" tabindex="6">
							<?php foreach ($webmail_clients as $client){
								echo "\t\t\t\t<option value=\"".htmlentities($client)."\"";
								if (( $v_webmail == $client )) {
									echo ' selected' ;
								}
								echo ">".htmlentities(ucfirst($client))."</option>\n";
								}
							?>
							<option value="" <?php if (empty($v_webmail) || $v_webmail == 'disabled' ){ echo "selected"; }?>><?= _("Disabled") ?></option>
						</select>
					</div>
				<?php } ?>
				<?php if (!empty($_SESSION["ANTISPAM_SYSTEM"])) { ?>
					<div class="form-check u-mb10">
						<input class="form-check-input" type="checkbox" name="v_antispam" id="v_antispam" <?php if ((empty($v_antispam)) || ($v_antispam == 'yes')) echo 'checked'; ?>>
						<label for="v_antispam">
							<?= _("Spam Filter") ?>
						</label>
					</div>
					<div class="form-check u-mb10">
						<input class="form-check-input" type="checkbox" name="v_reject" id="v_reject" <?php if ((empty($v_reject)) || ($v_reject == 'yes')) echo 'checked'; ?>>
						<label for="v_reject">
							<?= _("Reject Spam") ?>
						</label>
					</div>
				<?php } ?>
				<?php if (!empty($_SESSION['ANTIVIRUS_SYSTEM'])) { ?>
					<div class="form-check u-mb10">
						<input class="form-check-input" type="checkbox" name="v_antivirus" id="v_antivirus" <?php if ((empty($v_antivirus)) || ($v_antivirus == 'yes')) echo 'checked'; ?>>
						<label for="v_antivirus">
							<?= _("Anti-Virus") ?>
						</label>
					</div>
				<?php } ?>
				<div class="form-check u-mb10">
					<input class="form-check-input" type="checkbox" name="v_dkim" id="v_dkim" <?php if (isset($v_dkim)&&$v_dkim == 'yes') echo 'checked'; ?>>
					<label for="v_dkim">
						<?= _("DKIM Support") ?>
					</label>
				</div>
				<div class="form-check u-mb10">
					<input x-model="hasSmtpRelay" class="form-check-input" type="checkbox" name="v_smtp_relay" id="v_smtp_relay">
					<label for="v_smtp_relay">
						<?= _("SMTP Relay") ?>
					</label>
				</div>
				<div x-cloak x-show="hasSmtpRelay" id="smtp_relay_table" class="u-pl30">
					<div class="u-mb10">
						<label for="v_smtp_relay_host" class="form-label"><?= _("Host") ?></label>
						<input type="text" class="form-control" name="v_smtp_relay_host" id="v_smtp_relay_host" value="<?= htmlentities(trim($v_smtp_relay_host, "'")) ?>">
					</div>
					<div class="u-mb10">
						<label for="v_smtp_relay_port" class="form-label"><?= _("Port") ?></label>
						<input type="text" class="form-control" name="v_smtp_relay_port" id="v_smtp_relay_port" value="<?= htmlentities(trim($v_smtp_relay_port, "'")) ?>">
					</div>
					<div class="u-mb10">
						<label for="v_smtp_relay_user" class="form-label"><?= _("Username") ?></label>
						<input type="text" class="form-control" name="v_smtp_relay_user" id="v_smtp_relay_user" value="<?= htmlentities(trim($v_smtp_relay_user, "'")) ?>">
					</div>
					<div class="u-mb10">
						<label for="v_smtp_relay_pass" class="form-label"><?= _("Password") ?></label>
						<input type="text" class="form-control" name="v_smtp_relay_pass" id="v_smtp_relay_pass">
					</div>
				</div>
			<?php } ?>
		</div>

	</form>

</div>
