<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/dns/">
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
			showAdvanced: <?= empty($v_adv) ? "false" : "true" ?>
		}"
		id="main-form"
		name="v_add_dns"
		method="post"
	>
		<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
		<input type="hidden" name="ok" value="Add">

		<div class="form-container">
			<h1 class="u-mb20"><?= _("Add DNS Zone") ?></h1>
			<?php show_alert_message($_SESSION); ?>
			<?php if ($user_plain == "admin" && $accept !== "true") { ?>
				<div class="alert alert-danger" role="alert">
					<i class="fas fa-exclamation"></i>
					<p><?= htmlify_trans(sprintf(_("It is strongly advised to {create a standard user account} before adding %s to the server due to the increased privileges the admin account possesses and potential security risks."), _('a dns domain')), '</a>', '<a href="/add/user/">'); ?></p>
				</div>
			<?php } ?>
			<?php if ($user_plain == "admin" && empty($accept)) { ?>
				<div class="u-side-by-side u-mt20">
					<a href="/add/user/" class="button u-width-full u-mr10"><?= _("Add User") ?></a>
					<a href="/add/dns/?accept=true" class="button button-danger u-width-full u-ml10"><?= _("Continue") ?></a>
				</div>
			<?php } ?>
			<?php if (($user_plain == "admin" && $accept === "true") || $user_plain !== "admin") { ?>
				<div class="u-mb10">
					<label for="v_domain" class="form-label"><?= _("Domain") ?></label>
					<input type="text" class="form-control" name="v_domain" id="v_domain" value="<?= htmlentities(trim($v_domain, "'")) ?>" required>
				</div>
				<div class="u-mb10">
					<label for="v_ip" class="form-label"><?= _("IP Address") ?></label>
					<div class="u-pos-relative">
						<select class="form-select" tabindex="-1" onchange="this.nextElementSibling.value=this.value">
							<option value="">clear</option>
							<?php
								foreach ($v_ips as $ip => $value) {
									$display_ip = empty($value['NAT']) ? $ip : "{$value['NAT']}";
									echo "<option value='{$display_ip}'>" . htmlentities($display_ip) . "</option>\n";
								}
							?>
						</select>
						<input type="text" class="form-control list-editor" name="v_ip" id="v_ip" value="<?= htmlentities(trim($v_ip, "'")) ?>">
					</div>
				</div>
				<?php if ($_SESSION["userContext"] === "admin" || ($_SESSION["userContext"] === "user" && $_SESSION["POLICY_USER_EDIT_DNS_TEMPLATES"] === "yes")) { ?>
					<div class="u-mb10">
						<label for="v_template" class="form-label">
							<?= _("Template") . "<span class='optional'>" . strtoupper($_SESSION["DNS_SYSTEM"]) . "</span>" ?>
						</label>
						<select class="form-select" name="v_template" id="v_template">
							<?php
								foreach ($templates as $key => $value) {
									echo "\t\t\t\t<option value=\"".htmlentities($value)."\"";
									$svalue = "'".$value."'";
									if ((!empty($v_template)) && ( $value == $v_template ) || ($svalue == $v_template)) {
										echo ' selected' ;
									}
									echo ">".htmlentities($value)."</option>\n";
								}
							?>
						</select>
					</div>
				<?php } ?>
				<div class="u-mb20 u-mt20">
					<button x-on:click="showAdvanced = !showAdvanced" type="button" class="button button-secondary">
						<?= _("Advanced Options") ?>
					</button>
				</div>
				<div x-cloak  x-show="showAdvanced" id="advtable">
					<?php if ($_SESSION["DNS_CLUSTER_SYSTEM"] == "hestia-zone" && $_SESSION["SUPPORT_DNSSEC"] == "yes") { ?>
						<div class="form-check u-mb10">
							<input class="form-check-input" type="checkbox" name="v_dnssec" id="v_dnssec" value="yes" <?php if ($v_dnssec === 'yes'){ echo ' checked'; } ?>>
							<label for="v_dnssec">
								<?= _("Enable DNSSEC") ?>
							</label>
						</div>
					<?php } ?>
					<div class="u-mb10">
						<label for="v_exp" class="form-label">
							<?= _("Expiration Date") ?> <span class="optional">(<?= _("YYYY-MM-DD") ?>)</span>
						</label>
						<input type="text" class="form-control" name="v_exp" id="v_exp" value="<?= htmlentities(trim($v_exp, "'")) ?>">
					</div>
					<div class="u-mb10">
						<label for="v_ttl" class="form-label"><?= _("TTL") ?></label>
						<input type="text" class="form-control" name="v_ttl" id="v_ttl" value="<?= htmlentities(trim($v_ttl, "'")) ?>">
					</div>
					<p class="form-label u-mb10"><?= _("Name Servers") ?></p>
					<div class="u-mb5">
						<input type="text" class="form-control" name="v_ns1" value="<?= htmlentities(trim($v_ns1, "'")) ?>">
					</div>
					<div class="u-mb5">
						<input type="text" class="form-control" name="v_ns2" value="<?= htmlentities(trim($v_ns2, "'")) ?>">
					</div>
					<?php
						if ($v_ns3) {
							echo '<div class="u-side-by-side u-mb5">
								<input type="text" class="form-control" name="v_ns3" value="' . htmlentities(trim($v_ns3, "'")) . '">
								<span class="form-link form-link-danger u-ml10 js-remove-ns">' . _('Delete') . '</span>
							</div>';
						}
						if ($v_ns4) {
							echo '<div class="u-side-by-side u-mb5">
								<input type="text" class="form-control" name="v_ns4" value="' . htmlentities(trim($v_ns4, "'")) . '">
								<span class="form-link form-link-danger u-ml10 js-remove-ns">' . _('Delete') . '</span>
							</div>';
						}
						if ($v_ns5) {
							echo '<div class="u-side-by-side u-mb5">
								<input type="text" class="form-control" name="v_ns5" value="' . htmlentities(trim($v_ns5, "'")) . '">
								<span class="form-link form-link-danger u-ml10 js-remove-ns">' . _('Delete') . '</span>
							</div>';
						}
						if ($v_ns6) {
							echo '<div class="u-side-by-side u-mb5">
								<input type="text" class="form-control" name="v_ns6" value="' . htmlentities(trim($v_ns6, "'")) . '">
								<span class="form-link form-link-danger u-ml10 js-remove-ns">' . _('Delete') . '</span>
							</div>';
						}
						if ($v_ns7) {
							echo '<div class="u-side-by-side u-mb5">
								<input type="text" class="form-control" name="v_ns7" value="' . htmlentities(trim($v_ns7, "'")) . '">
								<span class="form-link form-link-danger u-ml10 js-remove-ns">' . _('Delete') . '</span>
							</div>';
						}
						if ($v_ns8) {
							echo '<div class="u-side-by-side u-mb5">
								<input type="text" class="form-control" name="v_ns8" value="' . htmlentities(trim($v_ns8, "'")) . '">
								<span class="form-link form-link-danger u-ml10 js-remove-ns">' . _('Delete') . '</span>
							</div>';
						}
					?>
					<button type="button" class="form-link u-mt20 js-add-ns" <?php if ($v_ns8) echo 'style="display:none;"'; ?>>
						<?= _("Add Name Server") ?>
					</button>
				</div>
			<?php } ?>
		</div>

	</form>

</div>
