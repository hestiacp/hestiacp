<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/web/">
				<i class="fas fa-arrow-left icon-blue"></i><?= tohtml( _("Back")) ?>
			</a>
		</div>
		<div class="toolbar-buttons">
			<?php if (($_SESSION["role"] == "admin" && $accept === "true") || $_SESSION["role"] !== "admin") { ?>
				<button type="submit" class="button" form="main-form">
					<i class="fas fa-floppy-disk icon-purple"></i><?= tohtml( _("Save")) ?>
				</button>
			<?php } ?>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container">

	<form id="main-form" name="v_add_web" method="post" class="js-enable-inputs-on-submit">
		<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
		<input type="hidden" name="ok" value="Add">

		<div class="form-container">
			<h1 class="u-mb20"><?= $add_mode === 'subdomain' ? tohtml( _("Add Subdomain")) : tohtml( _("Add Web Domain")) ?></h1>
			<?php show_alert_message($_SESSION); ?>
			<?php if ($_SESSION["role"] == "admin" && $accept !== "true") { ?>
				<div class="alert alert-danger" role="alert">
					<i class="fas fa-exclamation"></i>
					<p><?= htmlify_trans(sprintf(_("It is strongly advised to {create a standard user account} before adding %s to the server due to the increased privileges the admin account possesses and potential security risks."), _('a web domain')), '</a>', '<a href="/add/user/">') ?></p>
				</div>
			<?php } ?>
			<?php if ($_SESSION["role"] == "admin" && empty($accept)) { ?>
				<div class="u-side-by-side u-mt20">
					<a href="/add/user/" class="button u-width-full u-mr10"><?= tohtml( _("Add User")) ?></a>
					<a href="/add/web/?<?= tohtml(http_build_query(["accept" => 'true'])) ?>" class="button button-danger u-width-full u-ml10"><?= tohtml( _("Continue")) ?></a>
				</div>
			<?php } ?>
			<?php if (($_SESSION["role"] == "admin" && $accept === "true") || $_SESSION["role"] !== "admin") { ?>
				<?php if ($add_mode === 'subdomain' && !empty($user_domains)) { ?>
					<input type="hidden" name="type" value="subdomain">
					<div class="u-mb10">
						<label for="v_parent_domain" class="form-label"><?= tohtml( _("Parent Domain")) ?></label>
						<select class="form-select" name="v_parent_domain" id="v_parent_domain" required>
							<?php foreach ($user_domains_data as $existing_domain => $existing_domain_data) {
								$depth = (int) ($existing_domain_data['SUBDOMAIN_DEPTH'] ?? 0);
								$prefix = $depth > 0 ? str_repeat('&nbsp;&nbsp;&nbsp;', $depth) . '&#8618; ' : '';
								$selected = ($_POST['v_parent_domain'] ?? '') === $existing_domain ? ' selected' : '';
							?>
								<option value="<?= tohtml($existing_domain) ?>"<?= $selected ?>><?= $prefix . tohtml($existing_domain) ?></option>
							<?php } ?>
						</select>
					</div>
					<div class="u-mb10">
						<label for="v_domain" class="form-label"><?= tohtml( _("Subdomain")) ?></label>
						<input
							type="text"
							class="form-control"
							name="v_domain"
							id="v_domain"
							value="<?= tohtml(trim($v_domain, "'")) ?>"
							placeholder="<?= tohtml( _("e.g. shop")) ?>"
							required
						>
					</div>
				<?php } else { ?>
					<div class="u-mb10">
						<label for="v_domain" class="form-label"><?= tohtml( _("Domain")) ?></label>
						<input type="text" class="form-control" name="v_domain" id="v_domain" value="<?= tohtml(trim($v_domain, "'")) ?>" required>
					</div>
				<?php } ?>
				<div class="u-mb20">
					<label for="v_ip" class="form-label"><?= tohtml( _("IP Address")) ?></label>
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
								<?= tohtml( _("DNS Support")) ?>
							</label>
						</div>
					<?php } ?>
				<?php } ?>
				<?php if (isset($_SESSION["IMAP_SYSTEM"]) && !empty($_SESSION["IMAP_SYSTEM"])) { ?>
					<?php if ($panel[$user_plain]["MAIL_DOMAINS"] != "0") { ?>
						<div class="form-check">
							<input class="form-check-input" type="checkbox" name="v_mail" id="v_mail" <?php if (empty($v_mail) && $panel[$user_plain]["MAIL_DOMAINS"] != "0"); ?>>
							<label for="v_mail">
								<?= tohtml( _("Mail Support")) ?>
							</label>
						</div>
					<?php } ?>
				<?php } ?>
			<?php } ?>
		</div>

	</form>

</div>
