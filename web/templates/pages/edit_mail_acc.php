<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/mail/?domain=<?= tohtml( htmlentities(trim($v_domain, "'"))) ?>&token=<?= tohtml($_SESSION["token"]) ?>">
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
			hasAutoReply: <?= tohtml($v_autoreply == "yes" ? "true" : "false") ?>
		}"
		id="main-form"
		name="v_edit_mail_acc"
		method="post"
		class="<?= tohtml($v_status) ?>"
	>
		<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
		<input type="hidden" name="save" value="save">

		<div class="form-container form-container-wide">
			<h1 class="u-mb20"><?= tohtml( _("Edit Mail Account")) ?></h1>
			<?php show_alert_message($_SESSION); ?>
			<div class="sidebar-right-grid">
				<div class="sidebar-right-grid-content">
					<div class="u-mb10">
						<label for="v_email" class="form-label"><?= tohtml( _("Account")) ?></label>
						<input type="text" class="form-control" name="v_email" id="v_email" value="<?= tohtml( htmlentities($_GET["account"]) . "@" . htmlentities($_GET["domain"])) ?>" disabled>
						<input type="hidden" name="v_domain" value="<?= tohtml( htmlentities(trim($v_domain, "'"))) ?>">
						<input type="hidden" name="v_account" value="<?= tohtml( htmlentities(trim($v_account, "'"))) ?>" class="js-account-input">
					</div>
					<div class="u-mb10">
						<label for="v_password" class="form-label">
							<?= tohtml( _("Password")) ?>
							<button type="button" title="<?= tohtml( _("Generate")) ?>" class="u-unstyled-button u-ml5 js-generate-password">
								<i class="fas fa-arrows-rotate icon-green"></i>
							</button>
						</label>
						<div class="u-pos-relative u-mb10">
							<input type="text" class="form-control js-password-input" name="v_password" id="v_password" value="<?= tohtml( htmlentities(trim($v_password, "'"))) ?>">
							<div class="password-meter">
								<meter max="4" class="password-meter-input js-password-meter"></meter>
							</div>
						</div>
					</div>
					<p class="u-mb10"><?= tohtml( _("Your password must have at least")) ?>:</p>
					<ul class="u-list-bulleted u-mb20">
						<li><?= tohtml( _("8 characters long")) ?></li>
						<li><?= tohtml( _("1 uppercase & 1 lowercase character")) ?></li>
						<li><?= tohtml( _("1 number")) ?></li>
					</ul>
					<div class="u-mb10">
						<label for="v_send_email" class="form-label">
							<?= tohtml( _("Email login credentials to:")) ?>
						</label>
						<input type="email" class="form-control" name="v_send_email" id="v_send_email" value="<?= tohtml( htmlentities(trim($v_send_email, "'"))) ?>">
					</div>
					<div class="u-mb10">
						<label for="v_quota" class="form-label">
							<?= tohtml( _("Quota")) ?> <span class="optional">(<?= tohtml( _("in MB")) ?>)</span>
						</label>
						<div class="u-pos-relative">
							<input type="text" class="form-control" name="v_quota" id="v_quota" value="<?php if (!empty($v_quota)) {echo htmlentities(trim($v_quota, "'"));} else { echo "0"; } ?>">
							<button type="button" class="unlimited-toggle js-unlimited-toggle" title="<?= tohtml( _("Unlimited")) ?>">
								<i class="fas fa-infinity"></i>
							</button>
						</div>
					</div>
					<div class="u-mb10">
						<label for="v_aliases" class="form-label">
							<?= tohtml( _("Aliases")) ?> <span class="optional">(<?= tohtml( _("Use local-part without domain name")) ?>)</span>
						</label>
						<textarea class="form-control" name="v_aliases" id="v_aliases"><?= tohtml( htmlentities(trim($v_aliases, "'"))) ?></textarea>
					</div>
					<div class="form-check">
						<input class="form-check-input js-discard-all-mail" type="checkbox" name="v_blackhole" id="v_blackhole" <?php if ($v_blackhole == 'yes') echo 'checked' ?>>
						<label for="v_blackhole">
							<?= tohtml( _("Discard all mail")) ?>
						</label>
					</div>
					<div class="form-check <?php if ($v_blackhole == 'yes') { echo 'u-hidden'; } ?>">
						<input class="form-check-input js-do-not-store-checkbox" type="checkbox" name="v_fwd_only" id="v_fwd_for" <?php if ($v_fwd_only == 'yes') echo 'checked' ?>>
						<label for="v_fwd_for">
							<?= tohtml( _("Do not store forwarded mail")) ?>
						</label>
					</div>
					<div class="u-mb10">
						<label for="v_fwd" class="form-label">
							<?= tohtml( _("Forward to")) ?> <span class="optional">(<?= tohtml( _("one or more email addresses")) ?>)</span>
						</label>
						<textarea class="form-control js-forward-to-textarea" name="v_fwd" id="v_fwd" <?php if ($v_blackhole == 'yes') echo "disabled"; ?>><?= tohtml( htmlentities(trim($v_fwd, "'"))) ?></textarea>
					</div>
					<div class="form-check u-mb10">
						<input x-model="hasAutoReply" class="form-check-input" type="checkbox" name="v_autoreply" id="v_autoreply">
						<label for="v_autoreply">
							<?= tohtml( _("Auto Reply")) ?>
						</label>
					</div>
					<div x-cloak x-show="hasAutoReply" id="autoreplytable">
						<div class="u-mb10">
							<label for="v_autoreply_message" class="form-label"><?= tohtml( _("Message")) ?></label>
							<textarea class="form-control" name="v_autoreply_message" id="v_autoreply_message"><?= tohtml( htmlentities(trim($v_autoreply_message, "'"))) ?></textarea>
						</div>
					</div>
					<div class="u-mb20">
						<label for="v_rate" class="form-label">
							<?= tohtml( _("Rate Limit")) ?> <span class="optional">(<?= tohtml( _("email / hour")) ?>)</span>
						</label>
						<input type="text" class="form-control" name="v_rate" id="v_rate" value="<?= tohtml( htmlentities(trim($v_rate, "'"))) ?>" <?php if ($_SESSION['userContext'] != "admin"){ echo "disabled"; }?>>
					</div>
				</div>
				<div class="sidebar-right-grid-sidebar">
					<?php require $_SERVER["HESTIA"] . "/web/templates/includes/email-settings-panel.php"; ?>
				</div>
			</div>
		</div>

	</form>

</div>
