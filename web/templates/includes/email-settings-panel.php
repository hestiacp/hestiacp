<div class="panel">
	<h2 class="u-text-H3 u-mb10"><?= _("Common Account Settings") ?></h2>
	<p class="u-mb10">
		<?= _("Connect to this account using credentials:") ?>
	</p>
	<div class="u-mb10">
		<label for="email_settings_username"><?= _("Username") ?></label>
		<div class="clipboard">
			<input type="text" class="form-control clipboard-input js-copy-input js-account-output" name="email_settings_username" id="email_settings_username" value="<?= htmlentities(trim($v_account, "'")) ?>@<?= htmlentities(trim($v_domain, "'")) ?>" data-postfix="@<?= htmlentities(trim($v_domain, "'")) ?>" readonly>
			<button type="button" class="clipboard-button js-copy-button" title="<?= _("Copy to clipboard") ?>">
				<i class="fas fa-copy"></i>
			</button>
		</div>
	</div>
	<div class="u-mb10">
		<label for="email_settings_password"><?= _("Password") ?></label>
		<div class="clipboard">
			<input type="text" class="form-control clipboard-input js-copy-input js-password-output" name="email_settings_password" id="email_settings_password" readonly>
			<button type="button" class="clipboard-button js-copy-button" title="<?= _("Copy to clipboard") ?>">
				<i class="fas fa-copy"></i>
			</button>
		</div>
	</div>
	<?php if ($_SESSION["WEBMAIL_SYSTEM"]) { ?>
		<div class="u-mb10">
			<label for="email_settings_webmail"><?= _("Webmail") ?></label>
			<div class="clipboard">
				<input type="text" class="form-control clipboard-input js-copy-input" name="email_settings_webmail" id="email_settings_webmail" value="http://<?= htmlentities($v_webmail_alias) ?>" readonly>
				<button type="button" class="clipboard-button js-copy-button" title="<?= _("Copy to clipboard") ?>">
					<i class="fas fa-copy"></i>
				</button>
			</div>
		</div>
	<?php } ?>
	<div class="u-mb20">
		<label for="email_settings_hostname"><?= _("Hostname") ?></label>
		<div class="clipboard">
			<input type="text" class="form-control clipboard-input js-copy-input" name="email_settings_hostname" id="email_settings_hostname" value="mail.<?= htmlentities($v_domain) ?>" readonly>
			<button type="button" class="clipboard-button js-copy-button" title="<?= _("Copy to clipboard") ?>">
				<i class="fas fa-copy"></i>
			</button>
		</div>
	</div>
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
