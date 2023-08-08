<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/web/">
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

	<form id="main-form" name="v_add_web" method="post" class="js-enable-inputs-on-submit">
		<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
		<input type="hidden" name="ok" value="Add">

		<div class="form-container">
			<h1 class="u-mb20"><?= _("Add Web Domain") ?></h1>
			<?php show_alert_message($_SESSION); ?>
			<?php if ($user_plain == "admin" && $accept !== "true") { ?>
				<div class="alert alert-danger" role="alert">
					<i class="fas fa-exclamation"></i>
					<p><?= htmlify_trans(sprintf(_("It is strongly advised to {create a standard user account} before adding %s to the server due to the increased privileges the admin account possesses and potential security risks."), _('a web domain')), '</a>', '<a href="/add/user/">'); ?></p>
				</div>
			<?php } ?>
			<?php if ($user_plain == "admin" && empty($accept)) { ?>
				<div class="u-side-by-side u-mt20">
					<a href="/add/user/" class="button u-width-full u-mr10"><?= _("Add User") ?></a>
					<a href="/add/web/?accept=true" class="button button-danger u-width-full u-ml10"><?= _("Continue") ?></a>
				</div>
			<?php } ?>
			<?php if (($user_plain == "admin" && $accept === "true") || $user_plain !== "admin") { ?>
				<div class="u-mb10">
					<label for="v_domain" class="form-label"><?= _("Domain") ?></label>
					<input type="text" class="form-control" name="v_domain" id="v_domain" value="<?= htmlentities(trim($v_domain, "'")) ?>" required>
				</div>
				<div class="u-mb20">
					<label for="v_ip" class="form-label"><?= _("IP Address") ?></label>
					<select class="form-select" name="v_ip" id="v_ip">
						<?php
							foreach ($ips as $ip => $value) {
								$display_ip = htmlentities(empty($value['NAT']) ? $ip : "{$value['NAT']}");
								$ip_selected = (!empty($v_ip) && $ip == $_POST['v_ip']) ? 'selected' : '';
								echo "\t\t\t\t<option value=\"{$ip}\" {$ip_selected}>{$display_ip}</option>\n";
							}
						?>
					</select>
				</div>
				<?php if (isset($_SESSION["DNS_SYSTEM"]) && !empty($_SESSION["DNS_SYSTEM"])) { ?>
					<?php if ($panel[$user_plain]["DNS_DOMAINS"] != "0") { ?>
						<div class="form-check u-mb10">
							<input class="form-check-input" type="checkbox" name="v_dns" id="v_dns" <?php if (empty($v_dns) && $panel[$user_plain]["DNS_DOMAINS"] != "0"); ?>>
							<label for="v_dns">
								<?= _("DNS Support") ?>
							</label>
						</div>
					<?php } ?>
				<?php } ?>
				<?php if (isset($_SESSION["IMAP_SYSTEM"]) && !empty($_SESSION["IMAP_SYSTEM"])) { ?>
					<?php if ($panel[$user_plain]["MAIL_DOMAINS"] != "0") { ?>
						<div class="form-check">
							<input class="form-check-input" type="checkbox" name="v_mail" id="v_mail" <?php if (empty($v_mail) && $panel[$user_plain]["MAIL_DOMAINS"] != "0"); ?>>
							<label for="v_mail">
								<?= _("Mail Support") ?>
							</label>
						</div>
					<?php } ?>
				<?php } ?>
			<?php } ?>
		</div>

	</form>

</div>
