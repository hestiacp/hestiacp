<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/package/">
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
		id="main-form"
		name="v_add_package"
		method="post"
	>
		<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
		<input type="hidden" name="ok" value="Add">

		<div class="form-container">
			<h1 class="u-mb20"><?= _("Add Package") ?></h1>
			<?php show_alert_message($_SESSION); ?>
			<div class="u-mb10">
				<label for="v_package" class="form-label"><?= _("Package Name") ?></label>
				<input type="text" class="form-control" name="v_package" id="v_package" value="<?= htmlentities(trim($v_package, "'")) ?>" required>
			</div>
			<div class="u-mb10">
				<label for="v_disk_quota" class="form-label">
					<?= _("Quota") ?> <span class="optional">(<?= _("in MB") ?>)</span>
				</label>
				<div class="u-pos-relative">
					<input type="text" class="form-control" name="v_disk_quota" id="v_disk_quota" value="<?= htmlentities(trim($v_disk_quota, "'")) ?>">
					<button type="button" class="unlimited-toggle js-unlimited-toggle" title="<?= _("Unlimited") ?>">
						<i class="fas fa-infinity"></i>
					</button>
				</div>
			</div>
			<div class="u-mb10">
				<label for="v_bandwidth" class="form-label">
					<?= _("Bandwidth") ?> <span class="optional">(<?= _("in MB") ?>)</span>
				</label>
				<div class="u-pos-relative">
					<input type="text" class="form-control" name="v_bandwidth" id="v_bandwidth" value="<?= htmlentities(trim($v_bandwidth, "'")) ?>">
					<button type="button" class="unlimited-toggle js-unlimited-toggle" title="<?= _("Unlimited") ?>">
						<i class="fas fa-infinity"></i>
					</button>
				</div>
			</div>
			<div class="u-mb10">
				<label for="v_backups" class="form-label"><?= _("Backups") ?></label>
				<input type="text" class="form-control" name="v_backups" id="v_backups" value="<?= htmlentities(trim($v_backups, "'")) ?>">
			</div>
			<details class="collapse" id="web-options">
				<summary class="collapse-header">
					<?= _("WEB") ?>
				</summary>
				<div class="collapse-content">
					<div class="u-mb10">
						<label for="v_web_domains" class="form-label"><?= _("Web Domains") ?></label>
						<div class="u-pos-relative">
							<input type="text" class="form-control" name="v_web_domains" id="v_web_domains" value="<?= htmlentities(trim($v_web_domains, "'")) ?>">
							<button type="button" class="unlimited-toggle js-unlimited-toggle" title="<?= _("Unlimited") ?>">
								<i class="fas fa-infinity"></i>
							</button>
						</div>
					</div>
					<div class="u-mb10">
						<label for="v_web_aliases" class="form-label">
							<?= _("Web Aliases") ?> <span class="optional">(<?= _("per domain") ?>)</span>
						</label>
						<div class="u-pos-relative">
							<input type="text" class="form-control" name="v_web_aliases" id="v_web_aliases" value="<?= htmlentities(trim($v_web_aliases, "'")) ?>">
							<button type="button" class="unlimited-toggle js-unlimited-toggle" title="<?= _("Unlimited") ?>">
								<i class="fas fa-infinity"></i>
							</button>
						</div>
					</div>
					<div class="u-mb10">
						<label for="v_web_template" class="form-label">
							<?= _("Web Template") . " <span class='optional'> " . strtoupper($_SESSION["WEB_SYSTEM"]) . "</span>" ?>
						</label>
						<select class="form-select" name="v_web_template" id="v_web_template">
							<?php
								foreach ($web_templates as $key => $value) {
									echo "\t\t\t\t<option value=\"".htmlentities($value)."\"";
									if ((!empty($v_web_template)) && ( $value == trim($v_web_template, "'"))){
										echo ' selected' ;
									}
									echo ">".htmlentities($value)."</option>\n";
								}
							?>
						</select>
					</div>
					<?php if (!empty($_SESSION['WEB_BACKEND'])) { echo ""; ?>
						<div class="u-mb10">
							<label for="v_backend_template" class="form-label">
								<?= _("Backend Template") . "<span class='optional'>" . strtoupper($_SESSION["WEB_BACKEND"]) . "</span>" ?>
							</label>
							<select class="form-select" name="v_backend_template" id="v_backend_template">
								<?php
									foreach ($backend_templates as $key => $value) {
										echo "\t\t\t\t<option value=\"".$value."\"";
										if ((!empty($v_backend_template)) && ( $value == trim($v_backend_template, "'"))){
											echo ' selected' ;
										}
										echo ">".htmlentities($value)."</option>\n";
									}
								?>
							</select>
						</div>
					<?=""; }?>
					<?php if (!empty($_SESSION['PROXY_SYSTEM'])) { echo ""; ?>
						<div class="u-mb10">
							<label for="v_proxy_template" class="form-label">
								<?= _("Proxy Template") . "<span class='optional'>" . strtoupper($_SESSION["PROXY_SYSTEM"]) . "</span>" ?>
							</label>
							<select class="form-select" name="v_proxy_template" id="v_proxy_template">
								<?php
									foreach ($proxy_templates as $key => $value) {
										echo "\t\t\t\t<option value=\"".htmlentities($value)."\"";
										if ((!empty($v_proxy_template)) && ( $value == trim($v_proxy_template, "'"))){
											echo ' selected' ;
										}
										echo ">".htmlentities($value)."</option>\n";
									}
								?>
							</select>
						</div>
					<?=""; }?>
				</div>
			</details>
			<details class="collapse" id="dns-options">
				<summary class="collapse-header">
					<?= _("DNS") ?>
				</summary>
				<div class="collapse-content">
					<div class="u-mb10">
						<label for="v_dns_template" class="form-label">
							<?= _("DNS Template") . "<span class='optional'>" . strtoupper($_SESSION["DNS_SYSTEM"]) . "</span>" ?>
						</label>
						<select class="form-select" name="v_dns_template" id="v_dns_template">
							<?php
								foreach ($dns_templates as $key => $value) {
									echo "\t\t\t\t<option value=\"".htmlentities($value)."\"";
									if ((!empty($v_dns_template)) && ( $value == trim($v_dns_template, "'"))){
										echo ' selected' ;
									}
									echo ">".htmlentities($value)."</option>\n";
								}
							?>
						</select>
					</div>
					<div class="u-mb10">
						<label for="v_dns_domains" class="form-label"><?= _("DNS Zones") ?></label>
						<div class="u-pos-relative">
							<input type="text" class="form-control" name="v_dns_domains" id="v_dns_domains" value="<?= htmlentities(trim($v_dns_domains, "'")) ?>">
							<button type="button" class="unlimited-toggle js-unlimited-toggle" title="<?= _("Unlimited") ?>">
								<i class="fas fa-infinity"></i>
							</button>
						</div>
					</div>
					<div class="u-mb10">
						<label for="v_dns_records" class="form-label">
							<?= _("DNS Records") ?> <span class="optional">(<?= _("per domain") ?>)</span>
						</label>
						<div class="u-pos-relative">
							<input type="text" class="form-control" name="v_dns_records" id="v_dns_records" value="<?= htmlentities(trim($v_dns_records, "'")) ?>">
							<button type="button" class="unlimited-toggle js-unlimited-toggle" title="<?= _("Unlimited") ?>">
								<i class="fas fa-infinity"></i>
							</button>
						</div>
					</div>
					<?php if (isset($_SESSION["DNS_SYSTEM"]) && !empty($_SESSION["DNS_SYSTEM"])) { ?>
						<p class="form-label u-mb10"><?= _("Name Servers") ?></p>
						<div class="u-mb5">
							<input type="text" class="form-control" name="v_ns1" value="<?= htmlentities(trim($v_ns1, "'")) ?>">
						</div>
						<div class="u-mb5">
							<input type="text" class="form-control" name="v_ns2" value="<?= htmlentities(trim($v_ns2, "'")) ?>">
						</div>
						<?php require $_SERVER["HESTIA"] . "/web/templates/includes/extra-ns-fields.php"; ?>
						<button type="button" class="form-link u-mt20 js-add-ns" <?php if ($v_ns8) echo 'style="display:none;"'; ?>>
							<?= _("Add Name Server") ?>
						</button>
					<?php } ?>
				</div>
			</details>
			<details class="collapse" id="mail-options">
				<summary class="collapse-header">
					<?= _("MAIL") ?>
				</summary>
				<div class="collapse-content">
					<div class="u-mb10">
						<label for="v_mail_domains" class="form-label"><?= _("Mail Domains") ?></label>
						<div class="u-pos-relative">
							<input type="text" class="form-control" name="v_mail_domains" id="v_mail_domains" value="<?= htmlentities(trim($v_mail_domains, "'")) ?>">
							<button type="button" class="unlimited-toggle js-unlimited-toggle" title="<?= _("Unlimited") ?>">
								<i class="fas fa-infinity"></i>
							</button>
						</div>
					</div>
					<div class="u-mb10">
						<label for="v_mail_accounts" class="form-label">
							<?= _("Mail Accounts") ?> <span class="optional">(<?= _("per domain") ?>)</span>
						</label>
						<div class="u-pos-relative">
							<input type="text" class="form-control" name="v_mail_accounts" id="v_mail_accounts" value="<?= htmlentities(trim($v_mail_accounts, "'")) ?>">
							<button type="button" class="unlimited-toggle js-unlimited-toggle" title="<?= _("Unlimited") ?>">
								<i class="fas fa-infinity"></i>
							</button>
						</div>
					</div>
					<div class="u-mb10">
						<label for="v_ratelimit" class="form-label">
							<?= _("Rate Limit") ?> <span class="optional">(<?= _("per account / hour") ?>)</span>
						</label>
						<input type="text" class="form-control" name="v_ratelimit" id="v_ratelimit" value="<?= htmlentities(trim($v_ratelimit, "'")) ?>">
					</div>
				</div>
			</details>
			<details class="collapse" id="database-options">
				<summary class="collapse-header">
					<?= _("DB") ?>
				</summary>
				<div class="collapse-content">
					<div class="u-mb10">
						<label for="v_databases" class="form-label"><?= _("Databases") ?></label>
						<div class="u-pos-relative">
							<input type="text" class="form-control" name="v_databases" id="v_databases" value="<?= htmlentities(trim($v_databases, "'")) ?>">
							<button type="button" class="unlimited-toggle js-unlimited-toggle" title="<?= _("Unlimited") ?>">
								<i class="fas fa-infinity"></i>
							</button>
						</div>
					</div>
				</div>
			</details>
			<details class="collapse" id="system-options">
				<summary class="collapse-header">
					<?= _("System") ?>
				</summary>
				<div class="collapse-content">
					<div class="u-mb10">
						<label for="v_cron_jobs" class="form-label"><?= _("Cron Jobs") ?></label>
						<div class="u-pos-relative">
							<input type="text" class="form-control" name="v_cron_jobs" id="v_cron_jobs" value="<?= htmlentities(trim($v_cron_jobs, "'")) ?>">
							<button type="button" class="unlimited-toggle js-unlimited-toggle" title="<?= _("Unlimited") ?>">
								<i class="fas fa-infinity"></i>
							</button>
						</div>
					</div>
					<div class="u-mb10">
						<label for="v_shell" class="form-label"><?= _("SSH Access") ?></label>
						<select class="form-select" name="v_shell" id="v_shell">
							<?php foreach ($shells as $key => $value): ?>
								<option value="<?= htmlentities($value) ?>"
									<?php if (!empty($v_shell) && $value == trim($v_shell, "''")): ?>
										selected
									<?php endif; ?>
								>
									<?= htmlentities($value) ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="form-check u-mb10">
						<input class="form-check-input" type="checkbox" name="v_shell_jail_enabled" id="v_shell_jail_enabled" value="yes" <?php if (htmlentities(trim($v_shell_jail_enabled, "'")) == "yes") echo 'checked' ?>>
						<label for="v_shell_jail_enabled">
							<?= _("Jail User Shell") ?>
						</label>
					</div>
				</div>
			</details>
		</div>

	</form>

</div>
