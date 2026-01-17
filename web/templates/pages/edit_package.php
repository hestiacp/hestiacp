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
		name="v_edit_package"
		method="post"
		class="<?= $v_status ?>"
	>
		<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
		<input type="hidden" name="save" value="save">

		<div class="form-container">
			<h1 class="u-mb20"><?= _("Edit Package") ?></h1>
			<?php show_alert_message($_SESSION); ?>
			<div class="u-mb10">
				<label for="v_package_new" class="form-label"><?= _("Package Name") ?></label>
				<input type="text" class="form-control" name="v_package_new" id="v_package_new" value="<?= htmlentities(trim($v_package_new, "'")) ?>" required>
				<input type="hidden" name="v_package" value="<?= htmlentities(trim($v_package, "'")) ?>">
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
			<div class="u-mb10">
				<label for="v_backups_incremental" class="form-label"><?= _("Incremental Backups") ?></label>
				<select class="form-select" name="v_backups_incremental" id="v_backups_incremental">
					<option value="no"><?=_('Disabled')?></option>
					<option value="yes" <?php if (!empty($v_backups_incremental) && 'yes' == trim($v_backups_incremental, "''")): ?>
						selected
					<?php endif; ?>><?=_('Enabled')?></option>
				</select>
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
								echo "\t\t\t\t<option value=\"" . htmlentities($value) . "\"";
								if ((!empty($v_web_template)) && ($value == trim($v_web_template, "'"))) {
									echo ' selected';
								}
								echo ">" . htmlentities($value) . "</option>\n";
							}
							?>
						</select>
					</div>
					<?php if (!empty($_SESSION['WEB_BACKEND'])) {
						echo ""; ?>
						<div class="u-mb10">
							<label for="v_backend_template" class="form-label">
								<?= _("Backend Template") . "<span class='optional'>" . strtoupper($_SESSION["WEB_BACKEND"]) . "</span>" ?>
							</label>
							<select class="form-select" name="v_backend_template" id="v_backend_template">
								<?php
								foreach ($backend_templates as $key => $value) {
									echo "\t\t\t\t<option value=\"" . $value . "\"";
									if ((!empty($v_backend_template)) && ($value == trim($v_backend_template, "'"))) {
										echo ' selected';
									}
									echo ">" . htmlentities($value) . "</option>\n";
								}
								?>
							</select>
						</div>
						<?= "";
					} ?>
					<?php if (!empty($_SESSION['PROXY_SYSTEM'])) {
						echo ""; ?>
						<div class="u-mb10">
							<label for="v_proxy_template" class="form-label">
								<?= _("Proxy Template") . "<span class='optional'>" . strtoupper($_SESSION["PROXY_SYSTEM"]) . "</span>" ?>
							</label>
							<select class="form-select" name="v_proxy_template" id="v_proxy_template">
								<?php
								foreach ($proxy_templates as $key => $value) {
									echo "\t\t\t\t<option value=\"" . htmlentities($value) . "\"";
									if ((!empty($v_proxy_template)) && ($value == trim($v_proxy_template, "'"))) {
										echo ' selected';
									}
									echo ">" . htmlentities($value) . "</option>\n";
								}
								?>
							</select>
						</div>
						<?= "";
					} ?>
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
								echo "\t\t\t\t<option value=\"" . htmlentities($value) . "\"";
								if ((!empty($v_dns_template)) && ($value == $v_dns_template)) {
									echo ' selected';
								}
								if ((!empty($v_dns_template)) && ($value == trim($v_dns_template, "'"))) {
									echo ' selected';
								}
								echo ">" . htmlentities($value) . "</option>\n";
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
				</div>
			</details>

			<?php if ($_SESSION['RESOURCES_LIMIT'] == 'yes') { ?>
				<details class="collapse" id="system-resources-options">
					<summary class="collapse-header">
						<?= _("System Resources") ?>
					</summary>
					<div class="collapse-content">
						<div class="u-mb10">
							<label for="cfs_quota" class="form-label">
								<?= _("CPU Quota (in %)") ?>
							</label>
							<div class="u-pos-relative">
								<input type="text" class="form-control" name="v_cpu_quota" id="v_cpu_quota" value="<?= htmlentities(trim($v_cpu_quota, "'")) ?>">
								<button type="button" class="unlimited-toggle js-unlimited-toggle" title="<?= _("Unlimited") ?>">
									<i class="fas fa-infinity"></i>
								</button>
							</div>
							<small class="form-text text-muted"><?= _("CPUQuota=20% ensures that the executed processes will never get more than 20% CPU time on one CPU.") ?></small>
						</div>

						<div class="u-mb10">
							<label for="cfs_period" class="form-label">
								<?= _("CPU Quota Period (in ms for milliseconds or s for seconds.)") ?>
							</label>
							<div class="u-pos-relative">
								<input type="text" class="form-control" name="v_cpu_quota_period" id="v_cpu_quota_period" value="<?= htmlentities(trim($v_cpu_quota_period, "'")) ?>">
								<button type="button" class="unlimited-toggle js-unlimited-toggle" title="<?= _("Unlimited") ?>">
									<i class="fas fa-infinity"></i>
								</button>
							</div>
							<small class="form-text text-muted"><?= _("CPUQuotaPeriodSec=10ms to request that the CPU quota is measured in periods of 10ms.") ?></small>
						</div>

						<div class="u-mb10">
							<label for="memory_limit" class="form-label">
								<?= _("Memory Limit (in bytes or with units like '2G')") ?>
							</label>
							<div class="u-pos-relative">
								<input type="text" class="form-control" name="v_memory_limit" id="v_memory_limit" value="<?= htmlentities(trim($v_memory_limit, "'")) ?>">
								<button type="button" class="unlimited-toggle js-unlimited-toggle" title="<?= _("Unlimited") ?>">
									<i class="fas fa-infinity"></i>
								</button>
							</div>
							<small class="form-text text-muted"><?= _("Takes a memory size in bytes. If the value is suffixed with K, M, G or T, the specified memory size is parsed as Kilobytes, Megabytes, Gigabytes, or Terabytes (with the base 1024), respectively") ?></small>
						</div>

						<div class="u-mb10">
							<label for="swap_limit" class="form-label">
								<?= _("Swap Limit (in bytes or with units like '2G')") ?>
							</label>
							<div class="u-pos-relative">
								<input type="text" class="form-control" name="v_swap_limit" id="v_swap_limit" value="<?= htmlentities(trim($v_swap_limit, "'")) ?>">
								<button type="button" class="unlimited-toggle js-unlimited-toggle" title="<?= _("Unlimited") ?>">
									<i class="fas fa-infinity"></i>
								</button>
							</div>
							<small class="form-text text-muted"><?= _("Takes a swap size in bytes. If the value is suffixed with K, M, G or T, the specified swap size is parsed as Kilobytes, Megabytes, Gigabytes, or Terabytes (with the base 1024), respectively") ?></small>
						</div>
					</div>
				</details>
			<?php } ?>

		</div>

	</form>

</div>
