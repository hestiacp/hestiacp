<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a href="/list/server/" class="button button-secondary button-back js-button-back">
				<i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
			</a>
			<a href="/list/ip/" class="button button-secondary">
				<i class="fas fa-ethernet icon-blue"></i><?= _("Network") ?>
			</a>
			<a href="/edit/server/whitelabel/" class="button button-secondary">
				<i class="fas fa-paint-brush icon-blue"></i><?= _("White Label") ?>
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

<!-- Begin form -->
<div class="container">
	<form
		x-data="{
			timezone: '<?= $v_timezone ?? "" ?>',
			theme: '<?= $_SESSION["THEME"] ?>',
			language: '<?= $_SESSION["LANGUAGE"] ?>',
			hasSmtpRelay: <?= $v_smtp_relay == "true" ? "true" : "false" ?>,
			remoteBackupEnabled: <?= !empty($v_backup_remote_adv) ? "true" : "false" ?>,
			backupType: '<?= !empty($v_backup_type) ? trim($v_backup_type, "'") : "" ?>',
			webmailAlias: '<?= $_SESSION["WEBMAIL_ALIAS"] ?? "" ?>',
			apiSystem: '<?= $_SESSION["API_SYSTEM"] ?>',
			legacyApi: '<?= $_SESSION["API"] ?>',
			showSystemOptions: false,
			showProtectionOptions: false,
			showPolicyOptions: false,
		}"
		id="main-form"
		name="v_configure_server"
		method="post"
	>
		<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
		<input type="hidden" name="save" value="save">

		<div class="form-container">
			<h1 class="u-mb20">
				<?= _("Configure Server") ?>
			</h1>
			<?php show_alert_message($_SESSION); ?>

			<!-- Basic options section -->
			<details class="collapse u-mb10">
				<summary class="collapse-header">
					<i class="fas fa-gear u-mr10"></i><?= _("Basic Options") ?>
				</summary>
				<div class="collapse-content">
					<div class="u-mb10">
						<label for="v_hostname" class="form-label">
							<?= _("Hostname") ?>
						</label>
						<input
							type="text"
							class="form-control"
							name="v_hostname"
							id="v_hostname"
							value="<?= htmlentities(trim($v_hostname, "'")) ?>"
						>
					</div>
					<div class="u-mb10">
						<label for="v_timezone" class="form-label">
							<?= _("Time Zone") ?>
						</label>
						<select x-model="timezone" class="form-select" name="v_timezone" id="v_timezone">
							<?php foreach ($v_timezones as $key => $value) { ?>
								<option value="<?= $value ?>">
									<?= $value ?>
								</option>
							<?php } ?>
						</select>
					</div>
					<div class="u-mb10">
						<label for="v_theme" class="form-label">
							<?= _("Theme") ?>
						</label>
						<select x-model="theme" class="form-select" name="v_theme" id="v_theme">
							<?php foreach ($theme as $key => $value) { ?>
								<option value="<?= $value ?>">
									<?= $value ?>
								</option>
							<?php } ?>
						</select>
					</div>
					<div class="form-check u-mb20">
						<input
							class="form-check-input"
							type="checkbox"
							name="v_policy_user_change_theme"
							id="v_policy_user_change_theme"
							<?= $_SESSION["POLICY_USER_CHANGE_THEME"] == "no" ? "checked" : "" ?>
						>
						<label for="v_policy_user_change_theme">
							<?= _("Set as selected theme for all users") ?>
						</label>
					</div>
					<div class="u-mb10">
						<label for="v_language" class="form-label"><?= _("Default Language") ?></label>
						<select x-model="language" class="form-select" name="v_language" id="v_language">
							<?php foreach ($languages as $key => $value) { ?>
								<option value="<?= $key ?>">
									<?= $value ?>
								</option>
							<?php } ?>
						</select>
					</div>
					<div class="form-check u-mb10">
						<input
							class="form-check-input"
							type="checkbox"
							name="v_language_update"
							id="v_language_update"
						>
						<label for="v_language_update">
							<?= _("Set as default language for all users") ?>
						</label>
					</div>
					<div class="form-check">
						<input
							class="form-check-input"
							type="checkbox"
							name="v_debug_mode"
							id="v_debug_mode"
							<?= $_SESSION["DEBUG_MODE"] == "true" ? "checked" : "" ?>
						>
						<label for="v_debug_mode">
							<?= _("Enable debug mode") ?>
						</label>
					</div>
				</div>
			</details>

			<!-- Updates section -->
			<details class="collapse u-mb10">
				<summary class="collapse-header">
					<i class="fas fa-code-branch u-mr10"></i><?= _("Updates") ?>
				</summary>
				<div class="collapse-content">
					<p class="u-mb10">
						<?= _("Version") ?>:
						<span class="optional">
							<?= $_SESSION["VERSION"] ?>
						</span>
					</p>
					<?php if ($_SESSION["RELEASE_BRANCH"] !== "release") { ?>
						<p class="u-mb10">
							<?= _("Release") ?>:
							<span class="optional">
								<?= $_SESSION["RELEASE_BRANCH"] ?>
							</span>
						</p>
					<?php } ?>
					<p class="u-mb5">
						<?= _("Options") ?>
					</p>
					<div class="form-check">
						<input
							class="form-check-input"
							type="checkbox"
							name="v_experimental_features"
							id="v_experimental_features"
							<?= $_SESSION["POLICY_SYSTEM_ENABLE_BACON"] == "true" ? "checked" : "" ?>
						>
						<label for="v_experimental_features">
							<?= _("Enable preview features") ?>
						</label>
						<span class="hint">
							<a href="/list/server/preview/">
								(<?= _("View") ?>)
							</a>
						</span>
					</div>
					<div class="form-check">
						<input
							class="form-check-input"
							type="checkbox"
							name="v_upgrade_send_notification_email"
							id="v_upgrade_send_notification_email"
							<?= $_SESSION["UPGRADE_SEND_EMAIL"] == "true" ? "checked" : "" ?>
						>
						<label for="v_upgrade_send_notification_email">
							<?= _("Send email notification when an update has been installed") ?>
						</label>
					</div>
					<div class="form-check">
						<input
							class="form-check-input"
							type="checkbox"
							name="v_upgrade_send_email_log"
							id="v_upgrade_send_email_log"
							<?= $_SESSION["UPGRADE_SEND_EMAIL_LOG"] == "true" ? "checked" : "" ?>
						>
						<label for="v_upgrade_send_email_log">
							<?= _("Send update installation log by email") ?>
						</label>
					</div>
				</div>
			</details>

			<!-- Web Server section -->
			<details class="collapse u-mb10">
				<summary class="collapse-header">
					<i class="fas fa-earth-americas u-mr10"></i><?= _("Web Server") ?>
				</summary>
				<div class="collapse-content">
					<?php if (!empty($_SESSION["PROXY_SYSTEM"])) { ?>
						<p>
							<?= _("Proxy Server") ?>:
							<span class="u-ml5">
								<?= $_SESSION["PROXY_SYSTEM"] ?>
							</span>
							<a href="/edit/server/<?= $_SESSION["PROXY_SYSTEM"] ?>/" class="u-ml5">
								<i class="fas fa-pencil icon-orange"></i>
							</a>
						</p>
					<?php } ?>
					<?php if (!empty($_SESSION["WEB_SYSTEM"])) { ?>
						<p>
							<?= _("Web Server") ?>:
							<span class="u-ml5">
								<?= $_SESSION["WEB_SYSTEM"] ?>
							</span>
							<a href="/edit/server/<?= $_SESSION["WEB_SYSTEM"] ?>/" class="u-ml5">
								<i class="fas fa-pencil icon-orange"></i>
							</a>
						</p>
					<?php } ?>
					<?php if (!empty($_SESSION["WEB_BACKEND"])) { ?>
						<p>
							<?= _("Backend Server") ?>:
							<span class="u-ml5">
								<?= $_SESSION["WEB_BACKEND"] ?>
							</span>
							<a href="/edit/server/<? echo $_SESSION["WEB_BACKEND"] ?>/" class="u-ml5">
								<i class="fas fa-pencil icon-orange"></i>
							</a>
						</p>
					<?php } ?>
					<?php if (!empty($_SESSION["WEB_BACKEND_POOL"])) { ?>
						<p>
							<?= _("Backend Pool Mode") ?>:
							<span class="u-ml5">
								<?= $_SESSION["WEB_BACKEND_POOL"] ?>
							</span>
						</p>
					<?php } ?>
					<?php if (count($v_php_versions)) { ?>
						<div class="u-mt15">
							<p class="u-mb10">
								<?= _("Enabled PHP Versions") ?>
							</p>
							<div class="alert alert-info u-mb10" role="alert">
								<i class="fas fa-info"></i>
								<p><?= _("It may take a few minutes to save your changes. Please wait until the process has completed and do not refresh the page.") ?></p>
							</div>
						</div>
						<?php foreach ($v_php_versions as $php_version) { ?>
							<div class="form-check">
								<input
									class="form-check-input"
									type="checkbox"
									id="<?= $php_version->name ?>"
									name="v_php_versions[<?= $php_version->tpl ?>]"
									<?= $php_version->installed ? "checked" : "" ?>
									<?= $php_version->protected ? "disabled" : "" ?>
								>
								<label for="<?= $php_version->name ?>">
									<?= $php_version->name ?>
								</label>
							</div>
							<?php foreach ($php_version->usedby as $wd_user => $wd_domains) { ?>
								<?php foreach ($wd_domains as $wd_domain) { ?>
									<p class="u-side-by-side" style="padding: 0 10px">
										<span>
											<i class="fas fa-user"></i>
											<?= $wd_user ?>
										</span>
										<span class="optional"><?= $wd_domain ?></span>
									</p>
								<?php } ?>
							<?php } ?>
						<?php } ?>
					<?php } ?>
					<?php if (!empty($_SESSION["WEB_BACKEND"])) { ?>
						<div class="u-mt10">
							<label for="v_php_default_version" class="form-label">
								<?= _("System PHP Version") ?>
							</label>
							<select class="form-select" name="v_php_default_version" id="v_php_default_version">
								<?php foreach ($v_php_versions as $php_version) { ?>
									<?php if ($php_version->installed) { ?>
										<option
											value="<?= $php_version->version ?>"
											<?= $php_version->name == DEFAULT_PHP_VERSION ? "selected" : "" ?>
										>
											<?= $php_version->name ?>
										</option>
									<?php } ?>
								<?php } ?>
							</select>
						</div>
					<?php } ?>
				</div>
			</details>

			<!-- DNS Server section -->
			<?php if (!empty($_SESSION["DNS_SYSTEM"])) { ?>
				<details class="collapse u-mb10">
					<summary class="collapse-header">
						<i class="fas fa-book-atlas u-mr10"></i><?= _("DNS Server") ?>
					</summary>
					<div class="collapse-content">
						<p>
							<?= _("DNS Server") ?>:
							<span class="u-ml5">
								<?= $_SESSION["DNS_SYSTEM"] ?>
							</span>
							<a href="/edit/server/<? echo $_SESSION["DNS_SYSTEM"] ?>/" class="u-ml5">
								<i class="fas fa-pencil icon-orange"></i>
							</a>
						</p>
						<p>
							<?= _("DNS Cluster") ?>:
							<span class="u-ml5">
								<?= $v_dns_cluster == "yes" ? _("Yes") : _("No") ?>
							</span>
						</p>
						<?php if ($v_dns_cluster == "yes") {
							$i = 0;
							foreach ($dns_cluster as $key => $value) {
								$i++;
							?>
							<div>
								<label for="v_dns_remote_host" class="form-label">
									<?= _("Host") . " #" . $i ?>
								</label>
								<input
									type="text"
									class="form-control"
									name="v_dns_remote_host"
									id="v_dns_remote_host"
									value="<?= $key ?>"
									disabled
								>
							</div>
						<?php } } ?>
					</div>
				</details>
			<?php } ?>

			<!-- Mail Server section -->
			<?php if (!empty($_SESSION["MAIL_SYSTEM"])) { ?>
				<details class="collapse u-mb10">
					<summary class="collapse-header">
						<i class="fas fa-envelopes-bulk u-mr10"></i><?= _("Mail Server") ?>
					</summary>
					<div class="collapse-content">
						<p>
							<?= _("Mail Server") ?>:
							<span class="u-ml5">
								<?= $_SESSION["MAIL_SYSTEM"] ?>
							</span>
							<a href="/edit/server/<? echo $_SESSION["MAIL_SYSTEM"] ?>/" class="u-ml5">
								<i class="fas fa-pencil icon-orange"></i>
							</a>
						</p>
						<?php if (!empty($_SESSION["ANTIVIRUS_SYSTEM"])) { ?>
							<p>
								<?= _("Anti-Virus") ?>:
								<span class="u-ml5">
									<?= $_SESSION["ANTIVIRUS_SYSTEM"] ?>
								</span>
								<a href="/edit/server/<? echo $_SESSION["ANTIVIRUS_SYSTEM"] ?>/" class="u-ml5">
									<i class="fas fa-pencil icon-orange"></i>
								</a>
							</p>
						<?php } ?>
						<?php if (!empty($_SESSION["ANTISPAM_SYSTEM"])) { ?>
							<p>
								<?= _("Spam Filter") ?>:
								<span class="u-ml5">
									<?= $_SESSION["ANTISPAM_SYSTEM"] ?>
								</span>
								<a href="/edit/server/<?= $_SESSION["ANTISPAM_SYSTEM"] ?>/" class="u-ml5">
									<i class="fas fa-pencil icon-orange"></i>
								</a>
							</p>
						<?php } ?>
						<?php if ($_SESSION["WEBMAIL_SYSTEM"]) { ?>
							<div class="u-mt15 u-mb10">
								<label for="v_webmail_alias" class="form-label">
									<?= _("Webmail Alias") ?>
									<span x-cloak x-text="`${webmailAlias}.example.com`" class="hint"></span>
								</label>
								<input
									x-model="webmailAlias"
									type="text"
									class="form-control"
									name="v_webmail_alias"
									id="v_webmail_alias"
								>
							</div>
						<?php } ?>
						<div class="form-check u-mt20">
							<input
								x-model="hasSmtpRelay"
								class="form-check-input"
								type="checkbox"
								name="v_smtp_relay"
								id="v_smtp_relay"
							>
							<label for="v_smtp_relay">
								<?= _("Global SMTP Relay") ?>
							</label>
						</div>
						<div
							x-cloak
							x-show="hasSmtpRelay"
							class="u-pl30 u-mt20"
						>
							<div class="u-mb10">
								<label for="v_smtp_relay_host" class="form-label">
									<?= _("Host") ?>
								</label>
								<input
									type="text"
									class="form-control"
									name="v_smtp_relay_host"
									id="v_smtp_relay_host"
									value="<?= htmlentities(trim($v_smtp_relay_host, "'")) ?>"
								>
							</div>
							<div class="u-mb10">
								<label for="v_smtp_relay_port" class="form-label">
									<?= _("Port") ?>
								</label>
								<input
									type="text"
									class="form-control"
									name="v_smtp_relay_port"
									id="v_smtp_relay_port"
									value="<?= htmlentities(trim($v_smtp_relay_port, "'")) ?>"
								>
							</div>
							<div class="u-mb10">
								<label for="v_smtp_relay_user" class="form-label">
									<?= _("Username") ?>
								</label>
								<input
									type="text"
									class="form-control"
									name="v_smtp_relay_user"
									id="v_smtp_relay_user"
									value="<?= htmlentities(trim($v_smtp_relay_user, "'")) ?>"
								>
							</div>
							<div class="u-mb10">
								<label for="v_smtp_relay_pass" class="form-label">
									<?= _("Password") ?>
								</label>
								<div class="u-pos-relative">
									<input
										type="text"
										class="form-control js-password-input"
										name="v_smtp_relay_pass"
										id="v_smtp_relay_pass"
									>
								</div>
							</div>
						</div>
					</div>
				</details>
			<?php } ?>

			<!-- Databases section -->
			<?php if (!empty($_SESSION["DB_SYSTEM"])) { ?>
				<details class="collapse u-mb10">
					<summary class="collapse-header">
						<i class="fas fa-database u-mr10"></i><?= _("Databases") ?>
					</summary>
					<div class="collapse-content">
						<div class="u-mb10">
							<p>
								<?= _("MySQL Support") ?>:
								<span class="u-ml5">
									<?= $v_mysql == "yes" ? _("Yes") : _("No") ?>
								</span>
								<a href="/edit/server/mysql/" class="u-ml5">
									<i class="fas fa-pencil icon-orange"></i>
								</a>
							</p>
						</div>
						<!-- MySQL / MariaDB Options-->
						<?php if ($v_mysql == "yes") { ?>
							<div class="u-mb20">
								<label for="v_mysql_url" class="form-label">
									<?= _("phpMyAdmin Alias") ?>
								</label>
								<input
									type="text"
									class="form-control"
									name="v_mysql_url"
									id="v_mysql_url"
									value="<?= $_SESSION["DB_PMA_ALIAS"] ?>"
								>
							</div>
							<div class="u-mb10">
								<label for="v_phpmyadmin_key" class="form-label">
									<?= _("phpMyAdmin Single Sign On") ?>
									<span class="hint">
										<a
											href="https://hestiacp.com/docs/server-administration/databases.html"
											target="_blank"
										>
											(<?= _("More info") ?>)
										</a>
									</span>
								</label>
								<select
									class="form-select"
									name="v_phpmyadmin_key"
									id="v_phpmyadmin_key"
									<?php $_SESSION["API"] != "yes" ? "disabled" : ""; ?>
								>
									<option value="no">
										<?= _("Disabled") ?>
									</option>
									<option value="yes" <?= $_SESSION["PHPMYADMIN_KEY"] != "" ? "selected" : "" ?>>
										<?= _("Enabled") ?>
									</option>
								</select>
							</div>
							<?php
								$i = 0;
								foreach ($v_mysql_hosts as $value) {
									$i++;
							?>
							<div class="u-pl30">
								<div class="u-mb10">
									<label for="v_mysql_host" class="form-label">
										<?= _("Host") . " #" . $i ?>
									</label>
									<input
										type="text"
										class="form-control"
										name="v_mysql_host"
										id="v_mysql_host"
										value="<?= $value["HOST"] ?>"
										disabled
									>
								</div>
								<div class="u-mb10">
									<label for="v_mysql_password" class="form-label">
										<?= _("Password") ?>
									</label>
									<div class="u-pos-relative">
										<input
											type="text"
											class="form-control js-password-input"
											name="v_mysql_password"
											id="v_mysql_password"
										>
									</div>
								</div>
								<div class="u-mb10">
									<label for="v_mysql_max" class="form-label">
										<?= _("Maximum Number of Databases") ?>
									</label>
									<input
										type="text"
										class="form-control"
										name="v_mysql_max"
										id="v_mysql_max"
										value="<?= $value["MAX_DB"] ?>"
										disabled
									>
								</div>
								<div class="u-mb10">
									<label for="v_mysql_current" class="form-label">
										<?= _("Current Number of Databases") ?>
									</label>
									<input
										type="text"
										class="form-control"
										name="v_mysql_current"
										id="v_mysql_current"
										value="<?= $value["U_DB_BASES"] ?>"
										disabled
									>
								</div>
							</div>
						<?php } } ?>
						<!-- PostgreSQL Options-->
						<?php if ($v_pgsql == "yes") { ?>
							<div class="u-mb10">
								<p>
									<?= _("PostgreSQL Support") ?>:
									<span class="u-ml5">
										<?= $v_pgsql == "yes" ? _("Yes") : _("No") ?>
									</span>
									<a href="/edit/server/postgresql/" class="u-ml5">
										<i class="fas fa-pencil icon-orange"></i>
									</a>
								</p>
							</div>
							<div class="u-mb20">
								<label for="v_pgsql_url" class="form-label">
									<?= _("phpPgAdmin Alias") ?>
								</label>
								<input type="text" class="form-control" name="v_pgsql_url" id="v_pgsql_url" value="<?= $_SESSION["DB_PGA_ALIAS"] ?>">
							</div>
						<?php } ?>
						<?php if ($v_pgsql == "yes") {
							$i = 0;
							foreach ($v_pgsql_hosts as $value) {
								$i++;
							?>
							<div class="u-pl30">
								<div class="u-mb10">
									<label for="v_pgsql_host" class="form-label"><?= _("Host") . " #" . $i ?></label>
									<input type="text" class="form-control" name="v_pgsql_host" id="v_pgsql_host" value="<?= $value["HOST"] ?>" disabled>
								</div>
								<div class="u-mb10">
									<label for="v_psql_max" class="form-label">
										<?= _("Maximum Number of Databases") ?>
									</label>
									<input type="text" class="form-control" name="v_psql_max" id="v_psql_max" value="<?= $value["MAX_DB"] ?>" disabled>
								</div>
								<div class="u-mb10">
									<label for="v_pgsql_max" class="form-label">
										<?= _("Current Number of Databases") ?>
									</label>
									<input type="text" class="form-control" name="v_pgsql_max" id="v_pgsql_max" value="<?= $value["U_DB_BASES"] ?>" disabled>
								</div>
							</div>
						<?php }} ?>
					</div>
				</details>
			<?php } ?>

			<!-- Backups section -->
			<details class="collapse u-mb10">
				<summary class="collapse-header">
					<i class="fas fa-arrow-rotate-left u-mr10"></i><?= _("Backups") ?>
				</summary>
				<div class="collapse-content">
					<div class="u-mb10">
						<label for="v_backup" class="form-label">
							<?= _("Local Backup") ?>
						</label>
						<select class="form-select" name="v_backup" id="v_backup">
							<option value="no">
								<?= _("No") ?>
							</option>
							<option value="yes" <?= $v_backup == "yes" ? "selected" : "" ?>>
								<?= _("Yes") ?>
							</option>
						</select>
					</div>
					<div class="u-mb10">
						<label for="v_backup_mode" class="form-label">
							<?= _("Compression") ?>
							<a
								href="https://hestiacp.com/docs/server-administration/backup-restore.html#what-is-the-difference-between-zstd-and-gzip"
								target="_blank"
								class="u-ml5"
							>
								<i class="fas fa-circle-question"></i>
							</a>
						</label>
						<select class="form-select" name="v_backup_mode" id="v_backup_mode">
							<option value="gzip">
								gzip
							</option>
							<option value="zstd" <?= $v_backup_mode == "zstd" ? "selected" : "" ?>>
								zstd
							</option>
						</select>
					</div>
					<div class="u-mb10">
						<label for="v_backup_gzip" class="form-label">
							<?= _("Compression Level") ?>
							<a
								href="https://hestiacp.com/docs/server-administration/backup-restore.html#what-is-the-optimal-compression-ratio"
								target="_blank"
								class="u-ml5"
							>
								<i class="fas fa-circle-question"></i>
							</a>
						</label>
						<select class="form-select" name="v_backup_gzip" id="v_backup_gzip">
							<?php for ($level = 1; $level < 20; $level++) { ?>
								<option
									value="<?= $level ?>"
									<?= $v_backup_gzip == $level ? "selected" : "" ?>
								>
									<?= $level ?>
									<?= $level > 9 ? "(" . _("zstd only") . ")" : "" ?>
								</option>
							<?php } ?>
						</select>
					</div>
					<div class="u-mb20">
						<label for="v_backup_dir" class="form-label">
							<?= _("Directory") ?>
							<a
								href="https://hestiacp.com/docs/server-administration/backup-restore.html#how-to-change-default-backup-folder"
								target="_blank"
								class="u-ml5"
							>
								<i class="fas fa-circle-question"></i>
							</a>
						</label>
						<input
							type="text"
							class="form-control"
							name="v_backup_dir"
							id="v_backup_dir"
							value="<?= trim($v_backup_dir, "'") ?>"
							disabled
						>
					</div>
					<div class="form-check">
						<input
							x-model="remoteBackupEnabled"
							class="form-check-input"
							type="checkbox"
							name="v_backup_remote_adv"
							id="v_backup_remote_adv"
						>
						<label for="v_backup_remote_adv">
							<?= _("Remote Backup") ?>
						</label>
					</div>
					<div x-cloak x-show="remoteBackupEnabled" class="u-pl30 u-mt20">
						<div class="u-mb10">
							<label for="backup_type" class="form-label">
								<?= _("Protocol") ?>
								<a
									href="https://hestiacp.com/docs/server-administration/backup-restore.html#what-kind-of-protocols-are-currently-supported"
									target="_blank"
									class="u-ml5"
								>
									<i class="fas fa-circle-question"></i>
								</a>
							</label>
							<select
								x-model="backupType"
								class="form-select"
								name="v_backup_type"
								id="backup_type"
							>
								<option value="ftp">
									FTP
								</option>
								<option value="sftp">
									SFTP
								</option>
								<option value="b2">
									Backblaze
								</option>
								<option value="rclone">
									Rclone
								</option>
							</select>
						</div>
						<div x-cloak x-show="backupType == 'ftp' || backupType == 'sftp' || backupType == ''">
							<div class="u-mb10">
								<label for="v_backup_host" class="form-label">
									<?= _("Host") ?>
								</label>
								<input
									type="text"
									class="form-control"
									name="v_backup_host"
									id="v_backup_host"
									value="<?= trim($v_backup_host, "'") ?>"
								>
							</div>
							<div class="u-mb20">
								<label for="v_backup_port" class="form-label">
									<?= _("Port") ?>
								</label>
								<input
									type="text"
									class="form-control"
									name="v_backup_port"
									id="v_backup_port"
									value="<?= trim($v_backup_port, "'") ?>"
								>
							</div>
							<div class="u-mb10">
								<label for="v_backup_username" class="form-label">
									<?= _("Username") ?>
								</label>
								<input
									type="text"
									class="form-control"
									name="v_backup_username"
									id="v_backup_username"
									value="<?= trim($v_backup_username, "'") ?>"
								>
							</div>
							<div class="u-mb20">
								<label for="v_backup_password" class="form-label">
									<?= _("Password") ?>
								</label>
								<div class="u-pos-relative">
									<input
										type="text"
										class="form-control js-password-input"
										name="v_backup_password"
										id="v_backup_password"
										value="<?= trim($v_backup_password, "'") ?>"
									>
								</div>
							</div>
							<div class="u-mb10">
								<label for="v_backup_bpath" class="form-label">
									<?= _("Directory") ?>
								</label>
								<input
									type="text"
									class="form-control"
									name="v_backup_bpath"
									id="v_backup_bpath"
									value="<?= trim($v_backup_bpath, "'") ?>"
								>
							</div>
						</div>
						<div x-cloak x-show="backupType == 'b2'">
							<div class="u-mb10">
								<label for="v_backup_bucket" class="form-label">
									Bucket
								</label>
								<input
									type="text"
									class="form-control"
									name="v_backup_bucket"
									id="v_backup_bucket"
									value="<?= trim($v_backup_bucket, "'") ?>"
								>
							</div>
							<div class="u-mb10">
								<label for="v_backup_application_id" class="form-label">
									Key ID
								</label>
								<input
									type="text"
									class="form-control"
									name="v_backup_application_id"
									id="v_backup_application_id"
									value="<?= trim($v_backup_application_id, "'") ?>"
								>
							</div>
							<div class="u-mb10">
								<label for="v_backup_application_key" class="form-label">
									Application Key
								</label>
								<input
									type="text"
									class="form-control"
									name="v_backup_application_key"
									id="v_backup_application_key"
									value="<?= trim($v_backup_application_key, "'") ?>"
								>
							</div>
						</div>
						<div x-cloak x-show="backupType == 'rclone'">
							<div class="u-mb10">
								<label for="v_rclone_host" class="form-label">
									<?= _("Host") ?>
								</label>
								<input
									type="text"
									class="form-control"
									name="v_rclone_host"
									id="v_rclone_host"
									value="<?= trim($v_rclone_host, "'") ?>"
								>
							</div>
							<div class="u-mb10">
								<label for="v_rclone_path" class="form-label">
									<?= _("Directory") ?>
								</label>
								<input
									type="text"
									class="form-control"
									name="v_rclone_path"
									id="v_rclone_path"
									value="<?= trim($v_rclone_path, "'") ?>"
								>
							</div>
						</div>
					</div>
				</div>
			</details>

			<!-- SSL tab -->
			<details class="collapse u-mb10">
				<summary class="collapse-header">
					<i class="fas fa-lock u-mr10"></i><?= _("SSL") ?>
				</summary>
				<div class="collapse-content">
					<div class="u-mb20">
						<label for="v_ssl_crt" class="form-label">
							<?= _("SSL Certificate") ?>
							<span id="generate-csr">
								/
								<a
									class="form-link"
									href="/generate/ssl/?domain=<?= htmlentities(trim($v_hostname, '"')) ?>"
									target="_blank"
								>
									<?= _("Generate Self-Signed SSL Certificate") ?>
								</a>
							</span>
						</label>
						<textarea
							class="form-control u-min-height100 u-console"
							name="v_ssl_crt"
							id="v_ssl_crt"
						><?= htmlentities(trim($v_ssl_crt, "'")) ?></textarea>
					</div>
					<div class="u-mb20">
						<label for="v_ssl_key" class="form-label">
							<?= _("SSL Private Key") ?>
						</label>
						<textarea
							class="form-control u-min-height100 u-console"
							name="v_ssl_key"
							id="v_ssl_key"
						><?= htmlentities(trim($v_ssl_key, "'")) ?></textarea>
					</div>
					<ul class="values-list">
						<li class="values-list-item">
							<span class="values-list-label"><?= _("Issued To") ?></span>
							<span class="values-list-value"><?= $v_ssl_subject ?></span>
						</li>
						<?php if ($v_ssl_aliases) { ?>
							<li class="values-list-item">
								<span class="values-list-label"><?= _("Alternate") ?></span>
								<span class="values-list-value"><?= $v_ssl_aliases ?></span>
							</li>
						<?php } ?>
						<li class="values-list-item">
							<span class="values-list-label"><?= _("Not Before") ?></span>
							<span class="values-list-value"><?= $v_ssl_not_before ?></span>
						</li>
						<li class="values-list-item">
							<span class="values-list-label"><?= _("Not After") ?></span>
							<span class="values-list-value"><?= $v_ssl_not_after ?></span>
						</li>
						<li class="values-list-item">
							<span class="values-list-label"><?= _("Signature") ?></span>
							<span class="values-list-value"><?= $v_ssl_signature ?></span>
						</li>
						<li class="values-list-item">
							<span class="values-list-label"><?= _("Key Size") ?></span>
							<span class="values-list-value"><?= $v_ssl_pub_key ?></span>
						</li>
						<li class="values-list-item">
							<span class="values-list-label"><?= _("Issued By") ?></span>
							<span class="values-list-value"><?= $v_ssl_issuer ?></span>
						</li>
					</ul>
				</div>
			</details>

			<!-- Security tab -->
			<details class="collapse u-mb10">
				<summary class="collapse-header">
					<i class="fas fa-key u-mr10"></i><?= _("Security") ?>
				</summary>
				<div class="collapse-content">
					<h2 x-on:click="showSystemOptions = !showSystemOptions" class="section-title">
						<?= _("System") ?>
						<i
							x-bind:class="showSystemOptions ? 'fa-square-minus' : 'fa-square-plus'"
							class="fas icon-dim icon-maroon js-section-toggle-icon"
						></i>
					</h2>
					<div x-cloak x-show="showSystemOptions">
						<h3 class="u-mt20 u-mb10">
							<?= _("API") ?>
						</h3>
						<div class="u-mb10">
							<label for="v_api_system" class="form-label">
								<?= _("Enable API access") ?>
							</label>
							<select x-model="apiSystem" class="form-select" name="v_api_system" id="v_api_system">
								<option value="0">
									<?= _("Disabled") ?>
								</option>
								<option value="1">
									<?= _("Enabled for admin") ?>
								</option>
								<option value="2">
									<?= _("Enabled for all users") ?>
								</option>
							</select>
						</div>
						<div class="u-mb10">
							<label for="v_api" class="form-label">
								<?= _("Enable legacy API access") ?>
							</label>
							<select x-model="legacyApi" class="form-select" name="v_api" id="v_api">
								<option value="yes">
									<?= _("Yes") ?>
								</option>
								<option value="no">
									<?= _("No") ?>
								</option>
							</select>
						</div>
						<div x-cloak x-show="legacyApi === 'yes' || apiSystem > 0">
							<div class="u-mb10">
								<label for="v_api_allowed_ip" class="form-label u-side-by-side">
									<?= _("Allowed IP addresses for API") ?>
									<span class="optional">1 IP address per line</span>
								</label>
								<textarea class="form-control" name="v_api_allowed_ip" id="v_api_allowed_ip"><?php
										foreach (explode(",", $_SESSION["API_ALLOWED_IP"]) as $ip) {
											echo trim($ip)."\n";
										}
									?></textarea>
							</div>
						</div>
						<h3 class="u-mt20 u-mb10">
							<?= _("Login") ?>
						</h3>
						<div class="u-mb10">
							<label for="v_login_style" class="form-label">
								<?= _("Login screen style") ?>
							</label>
							<select class="form-select" name="v_login_style" id="v_login_style">
								<option value="default">
									<?= _("Default") ?>
								</option>
								<option value="old" <?= $_SESSION["LOGIN_STYLE"] == "old" ? "selected" : "" ?>>
									<?= _("Old Style") ?>
								</option>
							</select>
						</div>
						<div class="u-mb10">
							<label for="v_policy_system_password_reset" class="form-label">
								<?= _("Allow users to reset their passwords") ?>
							</label>
							<select
								class="form-select"
								name="v_policy_system_password_reset"
								id="v_policy_system_password_reset"
							>
								<option value="yes">
									<?= _("Yes") ?>
								</option>
								<option
									value="no"
									<?= $_SESSION["POLICY_SYSTEM_PASSWORD_RESET"] == "no" ? "selected" : "" ?>
								>
									<?= _("No") ?>
								</option>
							</select>
						</div>
						<div class="u-mb20">
							<label for="v_inactive_session_timeout" class="form-label">
								<?= _("Inactive session timeout") ?> (<?= _("Minutes") ?>)
							</label>
							<input
								type="text"
								class="form-control"
								name="v_inactive_session_timeout"
								id="v_inactive_session_timeout"
								value="<?= trim($_SESSION["INACTIVE_SESSION_TIMEOUT"], "'") ?>"
							>
						</div>
						<div class="u-mb10">
							<label for="v_policy_csrf_strictness" class="form-label">
								<?= _("Prevent CSRF") ?>
							</label>
							<select
								class="form-select"
								name="v_policy_csrf_strictness"
								id="v_policy_csrf_strictness"
							>
								<option value="0">
									<?= _("Disabled") ?>
								</option>
								<option value="1"	<?= $_SESSION["POLICY_CSRF_STRICTNESS"] == "1" ? "selected" : "" ?>>
									<?= _("Enabled") ?>
								</option>
								<option value="2"	<?= $_SESSION["POLICY_CSRF_STRICTNESS"] == "2" ? "selected" : "" ?>>
									<?= _("Strict") ?>
								</option>
							</select>
						</div>
					</div>

					<?php if ($_SESSION["userContext"] === "admin" && $_SESSION["user"] === "admin") { ?>
						<h2 x-on:click="showProtectionOptions = !showProtectionOptions" class="section-title">
							<?= _("System Protection") ?>
							<i
								x-bind:class="showProtectionOptions ? 'fa-square-minus' : 'fa-square-plus'"
								class="fas icon-dim icon-maroon js-section-toggle-icon"
							></i>
						</h2>
						<div x-cloak x-show="showProtectionOptions">
							<h3 class="u-mt20 u-mb10">
								<?= _("System Administrator account") ?>
							</h3>
							<div class="u-mb10">
								<label for="v_policy_system_protected_admin" class="form-label">
									<?= _("Restrict access to read-only for other administrators") ?>
								</label>
								<select
									class="form-select"
									name="v_policy_system_protected_admin"
									id="v_policy_system_protected_admin"
								>
									<option value="yes">
										<?= _("Yes") ?>
									</option>
									<option value="no" <?= $_SESSION["POLICY_SYSTEM_PROTECTED_ADMIN"] !== "yes" ? "selected" : "" ?>>
										<?= _("No") ?>
									</option>
								</select>
							</div>
							<div class="u-mb10">
								<label for="v_policy_system_hide_admin" class="form-label">
									<?= _("Hide account from other administrators") ?>
								</label>
								<select
									class="form-select"
									name="v_policy_system_hide_admin"
									id="v_policy_system_hide_admin"
								>
									<option value="yes">
										<?= _("Yes") ?>
									</option>
									<option value="no" <?= $_SESSION["POLICY_SYSTEM_HIDE_ADMIN"] !== "yes" ? "selected" : "" ?>>
										<?= _("No") ?>
									</option>
								</select>
							</div>
							<div class="u-mb10">
								<label for="v_policy_system_hide_services" class="form-label">
									<?= _("Do not allow other administrators to access Server Settings") ?>
								</label>
								<select
									class="form-select"
									name="v_policy_system_hide_services"
									id="v_policy_system_hide_services"
								>
									<option value="yes">
										<?= _("Yes") ?>
									</option>
									<option value="no" <?= $_SESSION["POLICY_SYSTEM_HIDE_SERVICES"] !== "yes" ? "selected" : "" ?>>
										<?= _("No") ?>
									</option>
								</select>
							</div>
						</div>
					<?php } ?>
					<h2 x-on:click="showPolicyOptions = !showPolicyOptions" class="section-title">
						<?= _("Policies") ?>
						<i
							x-bind:class="showPolicyOptions ? 'fa-square-minus' : 'fa-square-plus'"
							class="fas icon-dim icon-maroon js-section-toggle-icon"
						></i>
					</h2>
					<div x-cloak x-show="showPolicyOptions">
						<h3 class="u-mt20 u-mb10">
							<?= _("Users") ?>
						</h3>
						<div class="u-mb10">
							<label for="v_policy_user_edit_details" class="form-label">
								<?= _("Allow users to edit their account details") ?>
							</label>
							<select
								class="form-select"
								name="v_policy_user_edit_details"
								id="v_policy_user_edit_details"
							>
								<option value="yes">
									<?= _("Yes") ?>
								</option>
								<option value="no" <?= $_SESSION["POLICY_USER_EDIT_DETAILS"] == "no" ? "selected" : "" ?>>
									<?= _("No") ?>
								</option>
							</select>
						</div>
						<div class="u-mb10">
							<label for="v_policy_user_edit_web_templates" class="form-label">
								<?= _("Allow users to change templates when editing web domains") ?>
							</label>
							<select class="form-select" name="v_policy_user_edit_web_templates" id="v_policy_user_edit_web_templates">
								<option value="yes">
									<?= _("Yes") ?>
								</option>
								<option value="no" <?= $_SESSION["POLICY_USER_EDIT_WEB_TEMPLATES"] == "no" ? "selected" : "" ?>>
									<?= _("No") ?>
								</option>
							</select>
						</div>
						<div class="u-mb10">
							<label for="v_policy_user_edit_dns_templates" class="form-label">
								<?= _("Allow users to change templates when editing DNS zones") ?>
							</label>
							<select
								class="form-select"
								name="v_policy_user_edit_dns_templates"
								id="v_policy_user_edit_dns_templates"
							>
								<option value="yes">
									<?= _("Yes") ?>
								</option>
								<option value="no" <?= $_SESSION["POLICY_USER_EDIT_DNS_TEMPLATES"] == "no" ? "selected" : "" ?>>
									<?= _("No") ?>
								</option>
							</select>
						</div>
						<div class="u-mb10">
							<label for="v_policy_user_view_logs" class="form-label">
								<?= _("Allow users to view action and login history logs") ?>
							</label>
							<select
								class="form-select"
								name="v_policy_user_view_logs"
								id="v_policy_user_view_logs"
							>
								<option value="yes">
									<?= _("Yes") ?>
								</option>
								<option value="no" <?= $_SESSION["POLICY_USER_VIEW_LOGS"] == "no" ? "selected" : "" ?>>
									<?= _("No") ?>
								</option>
							</select>
						</div>
						<div class="u-mb10">
							<label for="v_policy_user_delete_logs" class="form-label">
								<?= _("Allow users to delete log history") ?>
							</label>
							<select
								class="form-select"
								name="v_policy_user_delete_logs"
								id="v_policy_user_delete_logs"
							>
								<option value="yes">
									<?= _("Yes") ?>
								</option>
								<option value="no" <?= $_SESSION["POLICY_USER_DELETE_LOGS"] == "no" ? "selected" : "" ?>>
									<?= _("No") ?>
								</option>
							</select>
						</div>
						<?php if ($_SESSION["POLICY_SYSTEM_ENABLE_BACON"] === "true") { ?>
							<div class="u-mb10">
								<label for="v_policy_user_view_suspended" class="form-label">
									<?= _("Allow suspended users to log in with read-only access") ?>
									<span class="hint">(<?= _("Preview") ?>)</span>
								</label>
								<select
									class="form-select"
									name="v_policy_user_view_suspended"
									id="v_policy_user_view_suspended"
								>
									<option value="yes">
										<?= _("Yes") ?>
									</option>
									<option value="no" <?= $_SESSION["POLICY_USER_VIEW_SUSPENDED"] == "no" ? "selected" : "" ?>>
										<?= _("No") ?>
									</option>
								</select>
							</div>
						<?php } ?>
						<div class="u-mb10">
							<label for="v_policy_backup_suspended_users" class="form-label">
								<?= _("Allow suspended users to create new backups") ?>
							</label>
							<select
								class="form-select"
								name="v_policy_backup_suspended_users"
								id="v_policy_backup_suspended_users"
							>
								<option value="yes">
									<?= _("Yes") ?>
								</option>
								<option value="no" <?= $_SESSION["POLICY_BACKUP_SUSPENDED_USERS"] == "no" ? "selected" : "" ?>>
									<?= _("No") ?>
								</option>
							</select>
						</div>
						<div class="u-mb10">
							<label for="v_policy_sync_error_documents" class="form-label">
								<?= _("Sync Error document templates on user rebuild") ?>
							</label>
							<select
								class="form-select"
								name="v_policy_sync_error_documents"
								id="v_policy_sync_error_documents"
							>
								<option value="yes">
									<?= _("Yes") ?>
								</option>
								<option value="no" <?= $_SESSION["POLICY_SYNC_ERROR_DOCUMENTS"] == "no" ? "selected" : "" ?>>
									<?= _("No") ?>
								</option>
							</select>
						</div>
						<div class="u-mb10">
							<label for="v_policy_sync_skeleton" class="form-label">
								<?= _("Sync Skeleton templates") ?>
							</label>
							<select
								class="form-select"
								name="v_policy_sync_skeleton"
								id="v_policy_sync_skeleton"
							>
								<option value="yes">
									<?= _("Yes") ?>
								</option>
								<option value="no" <?= $_SESSION["POLICY_SYNC_SKELETON"] == "no" ? "selected" : "" ?>>
									<?= _("No") ?>
								</option>
							</select>
						</div>
						<h3 class="u-mt20 u-mb10">
							<?= _("Domains") ?>
						</h3>
						<div class="u-mb10">
							<label for="v_enforce_subdomain_ownership" class="form-label">
								<?= _("Enforce subdomain ownership") ?>
							</label>
							<select
								class="form-select"
								name="v_enforce_subdomain_ownership"
								id="v_enforce_subdomain_ownership"
							>
								<option value="yes">
									<?= _("Yes") ?>
								</option>
								<option value="no" <?= $_SESSION["ENFORCE_SUBDOMAIN_OWNERSHIP"] == "no" ? "selected" : "" ?>>
									<?= _("No") ?>
								</option>
							</select>
						</div>
					</div>
				</div>
			</details>

			<!-- Plugins tab -->
			<details class="collapse u-mb10">
				<summary class="collapse-header">
					<i class="fas fa-puzzle-piece u-mr10"></i><?= _("Plugins") ?>
				</summary>
				<div class="collapse-content">
					<div class="u-mb10">
						<label for="v_plugin_app_installer" class="form-label">
							<?= _("Quick App Installer") ?>
						</label>
						<select class="form-select" name="v_plugin_app_installer" id="v_plugin_app_installer">
							<option value="false">
								<?= _("No") ?>
							</option>
							<option value="true" <?= $_SESSION["PLUGIN_APP_INSTALLER"] == "true" ? "selected" : "" ?>>
								<?= _("Yes") ?>
							</option>
						</select>
					</div>
					<div class="u-mb10">
						<label for="v_filemanager" class="form-label">
							<?= _("File Manager") ?>
						</label>
						<select class="form-select" name="v_filemanager" id="v_filemanager">
							<option value="false">
								<?= _("No") ?>
							</option>
							<option value="true" <?= $_SESSION["FILE_MANAGER"] == "true" ? "selected" : "" ?>>
								<?= _("Yes") ?>
							</option>
						</select>
					</div>
					<div class="u-mb10">
						<label for="v_quota" class="form-label">
							<?= _("File System Disk Quota") ?>
						</label>
						<select class="form-select" name="v_quota" id="v_quota">
							<option value="no">
								<?= _("No") ?>
							</option>
							<option value="yes" <?= $_SESSION["DISK_QUOTA"] == "yes" ? "selected" : "" ?>>
								<?= _("Yes") ?>
							</option>
						</select>
					</div>
					<div class="u-mb10">
						<label for="v_firewall" class="form-label">
							<?= _("Firewall") ?>
						</label>
						<select class="form-select" name="v_firewall" id="v_firewall">
							<option value="no">
								<?= _("No") ?>
							</option>
							<option value="yes" <?= $_SESSION["FIREWALL_SYSTEM"] == "iptables" ? "selected" : "" ?>>
								<?= _("Yes") ?>
							</option>
						</select>
					</div>
				</div>
			</details>
		</div>
	</form>
</div>
<!-- End form -->
