<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/mail/?domain=<?= htmlentities(trim($v_domain, "'")) ?>&token=<?= $_SESSION["token"] ?>">
				<i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
			</a>
		</div>
		<div class="toolbar-buttons">
			<button type="submit" class="button" form="main-form">
				<i class="fas fa-floppy-disk icon-purple"></i><?= _("Save") ?>
			</button>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container">

	<form
		x-data="{
			showAdvanced: <?= empty($v_adv) ? "false" : "true" ?>
		}"
		id="main-form"
		name="v_add_mail_acc"
		method="post"
	>
		<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
		<input type="hidden" name="ok_acc" value="add">

		<div class="form-container form-container-wide">
			<h1 class="u-mb20"><?= _("Add Mail Account") ?></h1>
			<?php show_alert_message($_SESSION); ?>
			<div class="sidebar-right-grid">
				<div class="sidebar-right-grid-content">
					<div class="u-mb10">
						<label for="v_domain" class="form-label"><?= _("Domain") ?></label>
						<input type="text" class="form-control" name="v_domain" id="v_domain" value="<?= htmlentities(trim($v_domain, "'")) ?>" disabled>
						<input type="hidden" name="v_domain" value="<?= htmlentities(trim($v_domain, "'")) ?>">
					</div>
					<div class="u-mb10">
						<label for="v_account" class="form-label"><?= _("Account") ?></label>
						<input type="text" class="form-control js-account-input" name="v_account" id="v_account" value="<?= htmlentities(trim($v_account, "'")) ?>" required>
					</div>
					<div class="u-mb10">
						<label for="v_password" class="form-label">
							<?= _("Password") ?>
							<button type="button" title="<?= _("Generate") ?>" class="u-unstyled-button u-ml5 js-generate-password">
								<i class="fas fa-arrows-rotate icon-green"></i>
							</button>
						</label>
						<div class="u-pos-relative u-mb10">
							<input type="text" class="form-control js-password-input" name="v_password" id="v_password" required>
							<div class="password-meter">
								<meter max="4" class="password-meter-input js-password-meter"></meter>
							</div>
						</div>
					</div>
					<p class="u-mb10"><?= _("Your password must have at least") ?>:</p>
					<ul class="u-list-bulleted u-mb20">
						<li><?= _("8 characters long") ?></li>
						<li><?= _("1 uppercase & 1 lowercase character") ?></li>
						<li><?= _("1 number") ?></li>
					</ul>
					<button x-on:click="showAdvanced = !showAdvanced" type="button" class="button button-secondary u-mb20">
						<?= _("Advanced Options") ?>
					</button>
					<div x-cloak x-show="showAdvanced" id="advtable">
						<div class="u-mb10">
							<label for="v_quota" class="form-label">
								<?= _("Quota") ?> <span class="optional">(<?= _("in MB") ?>)</span>
							</label>
							<div class="u-pos-relative">
								<input type="text" class="form-control" name="v_quota" id="v_quota" value="<?= htmlentities(trim($v_quota, "'")) ?>">
								<button type="button" class="unlimited-toggle js-unlimited-toggle" title="<?= _("Unlimited") ?>">
									<i class="fas fa-infinity"></i>
								</button>
							</div>
						</div>
						<div class="u-mb10">
							<label for="v_aliases" class="form-label">
								<?= _("Aliases") ?> <span class="optional">(<?= _("Use local-part without domain name") ?>)</span>
							</label>
							<textarea class="form-control" name="v_aliases" id="v_aliases"><?= htmlentities(trim($v_aliases, "'")) ?></textarea>
						</div>
						<div class="u-mb10">
							<label for="v_fwd" class="form-label">
								<?= _("Forward to") ?> <span class="optional">(<?= _("One or more email addresses") ?>)</span>
							</label>
							<textarea class="form-control js-forward-to-textarea" name="v_fwd" id="v_fwd" <?php if ($v_blackhole == 'yes') echo "disabled"; ?>><?= htmlentities(trim($v_fwd, "'")) ?></textarea>
						</div>
						<div class="form-check">
							<input class="form-check-input js-discard-all-mail" type="checkbox" name="v_blackhole" id="v_blackhole" <?php if ($v_blackhole == 'yes') echo 'checked' ?>>
							<label for="v_blackhole">
								<?= _("Discard all mail") ?>
							</label>
						</div>
						<div class="form-check <?php if ($v_blackhole == 'yes') { echo 'u-hidden'; } ?>">
							<input class="form-check-input js-do-not-store-checkbox" type="checkbox" name="v_fwd_only" id="v_fwd_for" <?php if ($v_fwd_only == 'yes') echo 'checked' ?>>
							<label for="v_fwd_for">
								<?= _("Do not store forwarded mail") ?>
							</label>
						</div>
						<div class="u-mt10 u-mb10">
							<label for="v_rate" class="form-label">
								<?= _("Rate Limit") ?> <span class="optional">(<?= _("email / hour") ?>)</span>
							</label>
							<input type="text" class="form-control" name="v_rate" id="v_rate" value="<?= htmlentities(trim($v_rate, "'")) ?>" <?php if ($_SESSION['userContext'] != "admin"){ echo "disabled"; }?>>
						</div>
					</div>
					<div class="u-mt15 u-mb20">
						<label for="v_send_email" class="form-label">
							<?= _("Email login credentials to:") ?>
						</label>
						<input type="email" class="form-control" name="v_send_email" id="v_send_email" value="<?= htmlentities(trim($v_send_email, "'")) ?>">
					</div>
				</div>
				<div class="sidebar-right-grid-sidebar">
					<div class="panel js-mail-info">
						<h2 class="u-text-H3 u-mb10"><?= _("Common Account Settings") ?></h2>
						<ul class="values-list u-mb20">
							<li class="values-list-item">
								<span class="values-list-label"><?= _("Username") ?></span>
								<span class="values-list-value u-overflow"><span class="js-account-output"></span>@<?= htmlentities(trim($v_domain, "'")) ?></span>
							</li>
							<li class="values-list-item">
								<span class="values-list-label"><?= _("Password") ?></span>
								<span class="values-list-value u-overflow"><span class="js-password-output"></span></span>
							</li>
							<?php if ($_SESSION["WEBMAIL_SYSTEM"]) { ?>
								<li class="values-list-item">
									<span class="values-list-label"><?= _("Webmail") ?></span>
									<span class="values-list-value"><a href="http://<?= htmlentities($v_webmail_alias) ?>" target="_blank">http://<?= htmlentities($v_webmail_alias) ?></a></span>
								</li>
							<?php } ?>
							<li class="values-list-item">
								<span class="values-list-label"><?= _("Hostname") ?></span>
								<span class="values-list-value">mail.<?= htmlentities($v_domain) ?></span>
							</li>
						</ul>
						<h2 class="u-text-H3 u-mb10"><?= _("IMAP Settings") ?></h2>
						<ul class="values-list u-mb20">
							<li class="values-list-item">
								<span class="values-list-label"><?= _("Authentication") ?></span>
								<span class="values-list-value"><?= _("Normal password") ?></span>
							</li>
							<li class="values-list-item">
								<span class="values-list-label">SSL/TLS</span>
								<span class="values-list-value"><?= _("Port") ?> 993</span>
							</li>
							<li class="values-list-item">
								<span class="values-list-label">STARTTLS</span>
								<span class="values-list-value"><?= _("Port") ?> 143</span>
							</li>
							<li class="values-list-item">
								<span class="values-list-label"><?= _("No encryption") ?></span>
								<span class="values-list-value"><?= _("Port") ?> 143</span>
							</li>
						</ul>
						<h2 class="u-text-H3 u-mb10"><?= _("POP3 Settings") ?></h2>
						<ul class="values-list u-mb20">
							<li class="values-list-item">
								<span class="values-list-label"><?= _("Authentication") ?></span>
								<span class="values-list-value"><?= _("Normal password") ?></span>
							</li>
							<li class="values-list-item">
								<span class="values-list-label">SSL/TLS</span>
								<span class="values-list-value"><?= _("Port") ?> 995</span>
							</li>
							<li class="values-list-item">
								<span class="values-list-label">STARTTLS</span>
								<span class="values-list-value"><?= _("Port") ?> 110</span>
							</li>
							<li class="values-list-item">
								<span class="values-list-label"><?= _("No encryption") ?></span>
								<span class="values-list-value"><?= _("Port") ?> 110</span>
							</li>
						</ul>
						<h2 class="u-text-H3 u-mb10"><?= _("SMTP Settings") ?></h2>
						<ul class="values-list">
							<li class="values-list-item">
								<span class="values-list-label"><?= _("Authentication") ?></span>
								<span class="values-list-value"><?= _("Normal password") ?></span>
							</li>
							<li class="values-list-item">
								<span class="values-list-label">SSL/TLS</span>
								<span class="values-list-value"><?= _("Port") ?> 465</span>
							</li>
							<li class="values-list-item">
								<span class="values-list-label">STARTTLS</span>
								<span class="values-list-value"><?= _("Port") ?> 587</span>
							</li>
							<li class="values-list-item">
								<span class="values-list-label"><?= _("No encryption") ?></span>
								<span class="values-list-value"><?= _("Port") ?> 25</span>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>

	</form>

</div>
