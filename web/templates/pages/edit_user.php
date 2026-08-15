<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/user/">
				<i class="fas fa-arrow-left icon-blue"></i><?= tohtml( _("Back")) ?>
			</a>
			<?php
				if (($_SESSION['userContext'] === 'admin') && ($_SESSION['look'] === '' ) && ($_SESSION['user'] !== $v_username)) {
					$ssh_key_url = "/list/key/?user=".htmlentities($_GET['user'])."&token=".$_SESSION['token']."";
					$log_url = "/list/log/?user=".htmlentities($_GET['user'])."&token=".$_SESSION['token']."";
					$keys_url = "/list/access-key/?user=".htmlentities($_GET['user'])."&token=".$_SESSION['token']."";
				}else{
					$ssh_key_url = "/list/key/";
					$log_url = "/list/log/";
					$keys_url = "/list/access-key/";
				}
			?>
			<a href="<?= tohtml($ssh_key_url) ?>" class="button button-secondary js-button-create" title="<?= tohtml( _("Manage SSH Keys")) ?>">
				<i class="fas fa-key icon-orange"></i><?= tohtml( _("Manage SSH Keys")) ?>
			</a>
			<?php if ($_SESSION["userContext"] == "admin" || ($_SESSION["userContext"] !== "admin" && $_SESSION["POLICY_USER_VIEW_LOGS"] !== "no")) { ?>
				<a href="<?= tohtml($log_url) ?>" class="button button-secondary js-button-create" title="<?= tohtml( _("Logs")) ?>">
					<i class="fas fa-clock-rotate-left icon-maroon"></i><?= tohtml( _("Logs")) ?>
				</a>
			<?php } ?>
			<?php
				$api_status = (!empty($_SESSION['API_SYSTEM']) && is_numeric($_SESSION['API_SYSTEM'])) ? $_SESSION['API_SYSTEM'] : 0;
				if (($user_plain == $_SESSION['ROOT_USER'] && $api_status > 0) || ($user_plain != $_SESSION['ROOT_USER'] && $api_status > 1)) { ?>
				<a href="<?= tohtml($keys_url) ?>" class="button button-secondary js-button-create" title="<?= tohtml( _("Access Keys")) ?>">
					<i class="fas fa-key icon-purple"></i><?= tohtml( _("Access Keys")) ?>
				</a>
			<?php } ?>
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
			loginDisabled: <?= tohtml($v_login_disabled === "yes" ? "true" : "false") ?>,
			useIpAllowList: <?= tohtml($v_login_use_iplist === "yes" ? "true" : "false") ?>,
			showAdvanced: false,
		}"
		id="main-form"
		method="post"
		name="v_edit_user"
		class="<?= tohtml($v_status) ?>"
	>
		<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
		<input type="hidden" name="save" value="save">

		<div class="form-container">
			<h1 class="u-mb20"><?= tohtml( _("Edit User")) ?></h1>
			<?php show_alert_message($_SESSION); ?>
			<div class="u-mb10">
				<label for="v_user" class="form-label"><?= tohtml( _("Username")) ?></label>
				<input type="text" class="form-control" name="v_user" id="v_user" value="<?= tohtml(trim($v_username, "'")) ?>" disabled required>
				<input type="hidden" name="v_username" value="<?= tohtml(trim($v_username, "'")) ?>">
			</div>
			<div class="u-mb10">
				<label for="v_name" class="form-label"><?= tohtml( _("Contact Name")) ?></label>
				<input type="text" class="form-control" name="v_name" id="v_name" value="<?= tohtml(trim($v_name, "'")) ?>" <?php if (($_SESSION['userContext'] !=='admin' ) && ($_SESSION['POLICY_USER_EDIT_DETAILS'] !=='yes' )) { echo 'disabled' ; }?> required>
				<?php if (($_SESSION['userContext'] !== 'admin') && ($_SESSION['POLICY_USER_EDIT_DETAILS'] !== 'yes')) { ?>
					<input type="hidden" name="v_name" value="<?= tohtml(trim($v_name, "'")) ?>">
				<?php } ?>
			</div>
			<div class="u-mb10">
				<label for="v_email" class="form-label"><?= tohtml( _("Email")) ?></label>
				<input type="email" class="form-control" name="v_email" id="v_email" value="<?= tohtml(trim($v_email, "'")) ?>" <?php if (($_SESSION['userContext'] !=='admin' ) && ($_SESSION['POLICY_USER_EDIT_DETAILS'] !=='yes' )) { echo 'disabled' ; }?> required>
				<?php if (($_SESSION['userContext'] !== 'admin') && ($_SESSION['POLICY_USER_EDIT_DETAILS'] !== 'yes')) { ?>
					<input type="hidden" name="v_email" value="<?= tohtml(trim($v_email, "'")) ?>">
				<?php } ?>
			</div>
			<div class="u-mb10">
				<label for="v_password" class="form-label">
					<?= tohtml( _("Password")) ?>
					<button type="button" title="<?= tohtml( _("Generate")) ?>" class="u-unstyled-button u-ml5 js-generate-password">
						<i class="fas fa-arrows-rotate icon-green"></i>
					</button>
				</label>
				<div class="u-pos-relative u-mb10">
					<input type="text" class="form-control js-password-input" name="v_password" id="v_password" value="<?= tohtml(trim($v_password, "'")) ?>">
					<div class="password-meter">
						<meter max="4" class="password-meter-input js-password-meter"></meter>
					</div>
				</div>
			</div>
			<div id="password-details" class="u-mb20">
				<?php require $_SERVER["HESTIA"] . "/web/templates/includes/password-requirements.php"; ?>
				<?php if ($_SESSION["userContext"] === "admin") { ?>
					<div class="form-check">
						<input x-model="loginDisabled" class="form-check-input" type="checkbox" name="v_login_disabled" id="v_login_disabled">
						<label for="v_login_disabled">
							<?= tohtml( _("Do not allow user to log in to Control Panel")) ?>
						</label>
					</div>
				<?php } ?>
				<div x-cloak x-show="!loginDisabled" id="password-options">
					<?php if ($twofa_state === "off" && !$is_own_account) { ?>
						<p class="u-mb10 form-label"><?= tohtml( _("Two-factor authentication has not been enabled by this user.")) ?></p>
					<?php } elseif (!$is_own_account) { ?>
						<div class="form-check">
							<input class="form-check-input" type="checkbox" name="v_twofa" id="v_twofa" checked>
							<label for="v_twofa">
								<?= tohtml( _("Enable two-factor authentication")) ?>
							</label>
						</div>
					<?php } else { ?>
						<div
							x-data="twofaSetup({
								checked: <?= $twofa_state !== 'off' ? 'true' : 'false' ?>,
								pending: <?= $twofa_state === 'pending' ? 'true' : 'false' ?>,
								qrcode: <?= tohtml(json_encode($twofa_state === 'pending' ? $v_twofa_setup_qrcode : '')) ?>,
								secret: <?= tohtml(json_encode($twofa_state === 'pending' ? $v_twofa_setup_secret : '')) ?>,
							})"
						>
							<div class="form-check">
								<input class="form-check-input" type="checkbox" name="v_twofa" id="v_twofa" x-model="checked" x-on:change="onToggle()">
								<label for="v_twofa">
									<?= tohtml( _("Enable two-factor authentication")) ?>
								</label>
							</div>

							<p x-cloak x-show="checked && loading" class="u-mb10 u-mt10"><?= tohtml( _("Generating setup code…")) ?></p>
							<p x-cloak x-show="checked && error" x-text="error" class="error u-mb10 u-mt10"></p>

							<div x-cloak x-show="checked && pending" class="u-mb10 u-mt10">
								<p class="u-mb10"><?= tohtml( _("Scan the code below in your authenticator app, or enter the setup key manually")) ?>:</p>
								<div class="u-mb10">
									<img class="qr-code" :src="qrcode" alt="<?= tohtml( _("2FA QR Code")) ?>">
								</div>
								<p class="u-mb10"><?= tohtml( _("Setup key")) ?>: <span x-text="secret"></span></p>
								<div class="u-mb10">
									<label for="v_twofa_device_name" class="form-label"><?= tohtml( _("Device name (optional)")) ?></label>
									<input type="text" class="form-control" name="v_twofa_device_name" id="v_twofa_device_name" maxlength="64" placeholder="<?= tohtml( _("e.g. Phone")) ?>">
								</div>
								<label for="v_twofa_confirm" class="form-label"><?= tohtml( _("Confirmation code")) ?></label>
								<input type="text" class="form-control" name="v_twofa_confirm" id="v_twofa_confirm" inputmode="numeric" pattern="[0-9]{6}" autocomplete="one-time-code" placeholder="123456">
								<p class="u-mb10 u-mt10"><?= tohtml( _("Enter the code currently shown in your authenticator app, then save to confirm and activate two-factor authentication. Uncheck the box above and save to cancel.")) ?></p>
							</div>
						</div>

						<?php if ($twofa_state === "on" && !empty($v_twofa_backup_codes)) { ?>
							<dialog x-data x-init="$el.showModal()" class="modal">
								<h2 class="modal-title"><?= tohtml( _("Save your backup codes now")) ?></h2>
								<div class="modal-message">
									<p class="u-mb10"><?= tohtml( _("These codes are shown only once. Each one can be used a single time to sign in if you lose access to your authenticator app. Store them somewhere safe.")) ?></p>
									<textarea class="form-control js-copy-input u-mb10" rows="10" readonly><?= tohtml(implode("\n", $v_twofa_backup_codes)) ?></textarea>
									<button type="button" class="button button-secondary js-copy-button"><?= tohtml( _("Copy codes")) ?></button>
								</div>
								<div class="modal-options">
									<button type="button" class="button" x-on:click="$root.close()"><?= tohtml( _("I've saved my codes")) ?></button>
								</div>
							</dialog>
						<?php } elseif ($twofa_state === "on") { ?>
							<p class="u-mb10">
								<?php if (!empty($v_twofa_device_name)) { ?>
									<?= tohtml( sprintf(_("Authenticator: %s"), $v_twofa_device_name)) ?><br>
								<?php } ?>
								<?= tohtml( sprintf(_("Two-factor authentication is active. %d backup codes remaining."), (int) $v_twofa_backup_count)) ?>
							</p>
							<?php if ((int) $v_twofa_backup_count === 0) { ?>
								<div class="inline-alert inline-alert-warning u-mb10" role="alert">
									<i class="fas fa-triangle-exclamation"></i>
									<p><?= tohtml( _("You have no backup codes left. If you lose access to your authenticator app, an administrator will need to disable two-factor authentication for you.")) ?></p>
								</div>
							<?php } ?>
						<?php } ?>
					<?php } ?>
				</div>
				<div x-cloak x-show="!loginDisabled" id="password-options-ip">
					<div class="form-check">
						<input x-model="useIpAllowList" class="form-check-input" type="checkbox" name="v_login_use_iplist" id="v_login_use_iplist">
						<label for="v_login_use_iplist">
							<?= tohtml( _("Use IP address allow list for login attempts")) ?>
						</label>
					</div>
				</div>
				<div x-cloak x-show="useIpAllowList" id="ip-allowlist" class="u-mt10">
					<input type="text" class="form-control" name="v_login_allowed_ips" value="<?= tohtml(trim($v_login_allowed_ips, "'")) ?>" placeholder="<?= tohtml( _("For example")) ?>: 127.0.0.1,192.168.1.100">
				</div>
			</div>
			<div class="u-mb10">
				<label for="v_language" class="form-label"><?= tohtml( _("Language")) ?></label>
				<select class="form-select" name="v_language" id="v_language" required>
					<?php
						foreach ($languages as $key => $value) {
							echo "\n\t\t\t\t\t\t\t\t\t<option value=\"".$key."\"";
							$skey = "'".$key."'";
							if (( $key == $v_language ) || ( $skey == $v_language)){
								echo 'selected' ;
							}
							if (( $key == detect_user_language() ) && (empty($v_language))){
								echo 'selected' ;
							}
							echo ">".htmlentities($value)."</option>\n";
						}
					?>
				</select>
			</div>
			<?php if ($v_username != "admin" && $_SESSION["userContext"] === "admin" && $_SESSION["user"] != $v_username): ?>
				<div class="u-mb10">
					<label for="v_role" class="form-label"><?= tohtml( _("Role")) ?></label>
					<select class="form-select" name="v_role" id="v_role" required>
						<option value="user"><?= tohtml( _("User")) ?></option>
						<option value="admin" <?= tohtml($v_role == "admin" ? "selected" : "") ?>><?= tohtml( _("Administrator")) ?></option>
						<option value="dns-cluster" <?= tohtml($v_role == "dns-cluster" ? "selected" : "") ?>><?= tohtml( _("DNS Sync User")) ?></option>
					</select>
				</div>
			<?php endif; ?>
			<?php if ($_SESSION["POLICY_USER_CHANGE_THEME"] !== "no") { ?>
			<div class="u-mb10">
				<label for="v_user_theme" class="form-label"><?= tohtml( _("Theme")) ?></label>
				<select class="form-select" name="v_user_theme" id="v_user_theme">
					<?php
						foreach ($themes as $key => $value) {
							echo "\t\t\t\t<option value=\"".$value."\"";
							if ((!empty($_SESSION['userTheme'])) && ( $value == $v_user_theme )) {
								echo ' selected' ;
							}
							if ((empty($v_user_theme) && (!empty($_SESSION['THEME']))) && ( $value == $_SESSION['THEME'] )) {
								echo ' selected' ;
							}
							echo ">".$value."</option>\n";
						}
					?>
				</select>
			</div>
			<?php } ?>
				<div class="u-mb10">
					<label for="v_sort_order" class="form-label"><?= tohtml( _("Default List Sort Order")) ?></label>
					<select class="form-select" name="v_sort_order" id="v_sort_order">
						<option value='date' <?php if ($v_sort_order === 'date') echo 'selected' ?>><?= tohtml( _("Date")) ?></option>
						<option value='name' <?php if ($v_sort_order === 'name') echo 'selected' ?>><?= tohtml( _("Name")) ?></option>
					</select>
				</div>
			<?php if ($_SESSION['userContext'] === 'admin') { ?>
				<div class="u-mb20">
					<label for="v_package" class="form-label"><?= tohtml( _("Package")) ?></label>
					<select class="form-select" name="v_package" id="v_package" required>
						<?php
							foreach ($packages as $key => $value) {
								echo "\n\t\t\t\t\t\t\t\t\t<option value=\"".htmlentities($key)."\"";
								$skey = "'".$key."'";
								if (( $key == $v_package ) || ( $skey == $v_package)){
									echo 'selected' ;
								}
								echo ">".htmlentities($key)."</option>\n";
							}
						?>
					</select>
				</div>
				<div class="u-mb20">
					<button x-on:click="showAdvanced = !showAdvanced" type="button" class="button button-secondary">
						<?= tohtml( _("Advanced Options")) ?>
					</button>
				</div>
				<div x-cloak x-show="showAdvanced">
					<div class="u-mb10">
						<label for="v_shell" class="form-label"><?= tohtml( _("SSH Access")) ?></label>
						<select class="form-select" name="v_shell" id="v_shell">
							<?php
								foreach ($shells as $key => $value) {
									echo "\t\t\t\t<option value=\"".htmlentities($value)."\"";
									$svalue = "'".$value."'";
									if (( $value == $v_shell ) || ($svalue == $v_shell )){
										echo 'selected' ;
									}
									echo ">".htmlentities($value)."</option>\n";
								}
							?>
						</select>
					</div>
					<div class="u-mb10">
						<label for="v_phpcli" class="form-label"><?= tohtml( _("PHP CLI Version")) ?></label>
						<select class="form-select" name="v_phpcli" id="v_phpcli">
							<?php
								foreach ($php_versions as $key => $value) {
									$php = explode('-',$value);
									echo "\t\t\t\t<option value=\"".$value."\"";
									$svalue = "'".$value."'";
									if ((!empty($v_phpcli)) && ( $value == $v_phpcli ) || ($svalue == $v_phpcli)){
										echo ' selected' ;
									}
									if ((empty($v_phpcli)) && ($value == DEFAULT_PHP_VERSION)){
										echo ' selected' ;
									}
									echo ">".htmlentities($value)."</option>\n";
								}
							?>
						</select>
					</div>
					<?php if ((isset($_SESSION['DNS_SYSTEM'])) && (!empty($_SESSION['DNS_SYSTEM']))) { ?>
						<p class="form-label u-mb10"><?= tohtml( _("Default Name Servers")) ?></p>
						<div class="u-mb5">
							<input type="text" class="form-control" name="v_ns1" value="<?= tohtml(trim($v_ns1, "'")) ?>">
						</div>
						<div class="u-mb5">
							<input type="text" class="form-control" name="v_ns2" value="<?= tohtml(trim($v_ns2, "'")) ?>">
						</div>
						<?php require $_SERVER["HESTIA"] . "/web/templates/includes/extra-ns-fields.php"; ?>
						<button type="button" class="form-link u-mt20 js-add-ns" <?php if ($v_ns8) echo 'style="display:none;"'; ?>>
							<?= tohtml( _("Add Name Server")) ?>
						</button>
					<?php } ?>
				</div>
			<?php } ?>
		</div>

	</form>

</div>
