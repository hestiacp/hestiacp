<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/user/">
				<i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
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
			<a href="<?= $ssh_key_url; ?>" class="button button-secondary js-button-create" title="<?= _("Manage SSH Keys") ?>">
				<i class="fas fa-key icon-orange"></i><?= _("Manage SSH Keys") ?>
			</a>
			<?php if ($_SESSION["userContext"] == "admin" || ($_SESSION["userContext"] !== "admin" && $_SESSION["POLICY_USER_VIEW_LOGS"] !== "no")) { ?>
				<a href="<?= $log_url ?>" class="button button-secondary js-button-create" title="<?= _("Logs") ?>">
					<i class="fas fa-clock-rotate-left icon-maroon"></i><?= _("Logs") ?>
				</a>
			<?php } ?>
			<?php
				$api_status = (!empty($_SESSION['API_SYSTEM']) && is_numeric($_SESSION['API_SYSTEM'])) ? $_SESSION['API_SYSTEM'] : 0;
				if (($user_plain == 'admin' && $api_status > 0) || ($user_plain != 'admin' && $api_status > 1)) { ?>
				<a href="<?= $keys_url; ?>" class="button button-secondary js-button-create" title="<?= _("Access Keys") ?>">
					<i class="fas fa-key icon-purple"></i><?= _("Access Keys") ?>
				</a>
			<?php } ?>
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
			loginDisabled: <?= $v_login_disabled === "yes" ? "true" : "false" ?>,
			useIpAllowList: <?= $v_login_use_iplist === "yes" ? "true" : "false" ?>,
			showAdvanced: false,
		}"
		id="main-form"
		method="post"
		name="v_edit_user"
		class="<?= $v_status ?>"
	>
		<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
		<input type="hidden" name="save" value="save">

		<div class="form-container">
			<h1 class="u-mb20"><?= _("Edit User") ?></h1>
			<?php show_alert_message($_SESSION); ?>
			<div class="u-mb10">
				<label for="v_user" class="form-label"><?= _("Username") ?></label>
				<input type="text" class="form-control" name="v_user" id="v_user" value="<?= htmlentities(trim($v_username, "'")) ?>" disabled required>
				<input type="hidden" name="v_username" value="<?= htmlentities(trim($v_username, "'")) ?>">
			</div>
			<div class="u-mb10">
				<label for="v_name" class="form-label"><?= _("Contact Name") ?></label>
				<input type="text" class="form-control" name="v_name" id="v_name" value="<?= htmlentities(trim($v_name, "'")) ?>" <?php if (($_SESSION['userContext'] !=='admin' ) && ($_SESSION['POLICY_USER_EDIT_DETAILS'] !=='yes' )) { echo 'disabled' ; }?> required>
				<?php if (($_SESSION['userContext'] !== 'admin') && ($_SESSION['POLICY_USER_EDIT_DETAILS'] !== 'yes')) { ?>
					<input type="hidden" name="v_name" value="<?= htmlentities(trim($v_name, "'")) ?>">
				<?php } ?>
			</div>
			<div class="u-mb10">
				<label for="v_email" class="form-label"><?= _("Email") ?></label>
				<input type="email" class="form-control" name="v_email" id="v_email" value="<?= htmlentities(trim($v_email, "'")) ?>" <?php if (($_SESSION['userContext'] !=='admin' ) && ($_SESSION['POLICY_USER_EDIT_DETAILS'] !=='yes' )) { echo 'disabled' ; }?> required>
				<?php if (($_SESSION['userContext'] !== 'admin') && ($_SESSION['POLICY_USER_EDIT_DETAILS'] !== 'yes')) { ?>
					<input type="hidden" name="v_email" value="<?= htmlentities(trim($v_email, "'")) ?>">
				<?php } ?>
			</div>
			<div class="u-mb10">
				<label for="v_password" class="form-label">
					<?= _("Password") ?>
					<button type="button" title="<?= _("Generate") ?>" class="u-unstyled-button u-ml5 js-generate-password">
						<i class="fas fa-arrows-rotate icon-green"></i>
					</button>
				</label>
				<div class="u-pos-relative u-mb10">
					<input type="text" class="form-control js-password-input" name="v_password" id="v_password" value="<?= htmlentities(trim($v_password, "'")) ?>">
					<div class="password-meter">
						<meter max="4" class="password-meter-input js-password-meter"></meter>
					</div>
				</div>
			</div>
			<div id="password-details" class="u-mb20">
				<p class="u-mb10"><?= _("Your password must have at least") ?>:</p>
				<ul class="u-list-bulleted u-mb10">
					<li><?= _("8 characters long") ?></li>
					<li><?= _("1 uppercase & 1 lowercase character") ?></li>
					<li><?= _("1 number") ?></li>
				</ul>
				<?php if ($_SESSION["userContext"] === "admin") { ?>
					<div class="form-check">
						<input x-model="loginDisabled" class="form-check-input" type="checkbox" name="v_login_disabled" id="v_login_disabled">
						<label for="v_login_disabled">
							<?= _("Do not allow user to log in to Control Panel") ?>
						</label>
					</div>
				<?php } ?>
				<div x-cloak x-show="!loginDisabled" id="password-options">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" name="v_twofa" id="v_twofa" <?php if (!empty($v_twofa)) echo 'checked' ?>>
						<label for="v_twofa">
							<?= _("Enable two-factor authentication") ?>
						</label>
					</div>
					<?php if (!empty($v_twofa)) { ?>
						<p class="u-mb10"><?= _("Account Recovery Code") . ": " . $v_twofa ?></p>
						<p class="u-mb10"><?= _("Please scan the code below in your 2FA application") ?>:</p>
						<div class="u-mb10">
							<img class="qr-code" src="<?= htmlentities($v_qrcode) ?>" alt="<?= _("2FA QR Code") ?>">
						</div>
					<?php } ?>
				</div>
				<div x-cloak x-show="!loginDisabled" id="password-options-ip">
					<div class="form-check">
						<input x-model="useIpAllowList" class="form-check-input" type="checkbox" name="v_login_use_iplist" id="v_login_use_iplist">
						<label for="v_login_use_iplist">
							<?= _("Use IP address allow list for login attempts") ?>
						</label>
					</div>
				</div>
				<div x-cloak x-show="useIpAllowList" id="ip-allowlist" class="u-mt10">
					<input type="text" class="form-control" name="v_login_allowed_ips" value="<?= htmlentities(trim($v_login_allowed_ips, "'")) ?>" placeholder="<?= _("For example") ?>: 127.0.0.1,192.168.1.100">
				</div>
			</div>
			<div class="u-mb10">
				<label for="v_language" class="form-label"><?= _("Language") ?></label>
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
					<label for="v_role" class="form-label"><?= _("Role") ?></label>
					<select class="form-select" name="v_role" id="v_role" required>
						<option value="user"><?= _("User") ?></option>
						<option value="admin" <?= $v_role == "admin" ? "selected" : "" ?>><?= _("Administrator") ?></option>
						<option value="dns-cluster" <?= $v_role == "dns-cluster" ? "selected" : "" ?>><?= _("DNS Sync User") ?></option>
					</select>
				</div>
			<?php endif; ?>
			<?php if ($_SESSION["POLICY_USER_CHANGE_THEME"] !== "no") { ?>
			<div class="u-mb10">
				<label for="v_user_theme" class="form-label"><?= _("Theme") ?></label>
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
					<label for="v_sort_order" class="form-label"><?= _("Default List Sort Order") ?></label>
					<select class="form-select" name="v_sort_order" id="v_sort_order">
						<option value='date' <?php if ($v_sort_order === 'date') echo 'selected' ?>><?= _("Date") ?></option>
						<option value='name' <?php if ($v_sort_order === 'name') echo 'selected' ?>><?= _("Name") ?></option>
					</select>
				</div>
			<?php if ($_SESSION['userContext'] === 'admin') { ?>
				<div class="u-mb20">
					<label for="v_package" class="form-label"><?= _("Package") ?></label>
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
						<?= _("Advanced Options") ?>
					</button>
				</div>
				<div x-cloak x-show="showAdvanced">
					<div class="u-mb10">
						<label for="v_shell" class="form-label"><?= _("SSH Access") ?></label>
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
						<label for="v_phpcli" class="form-label"><?= _("PHP CLI Version") ?></label>
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
						<p class="form-label u-mb10"><?= _("Default Name Servers") ?></p>
						<div class="u-mb5">
							<input type="text" class="form-control" name="v_ns1" value="<?= htmlentities(trim($v_ns1, "'")) ?>">
						</div>
						<div class="u-mb5">
							<input type="text" class="form-control" name="v_ns2" value="<?= htmlentities(trim($v_ns2, "'")) ?>">
						</div>
						<?php
							if ($v_ns3) {
								echo '<div class="u-side-by-side u-mb5">
									<input type="text" class="form-control" name="v_ns3" value="'.htmlentities(trim($v_ns3, "'")).'">
									<span class="u-ml10 js-remove-ns"><i class="fas fa-trash icon-dim icon-red"></i></span>
								</div>';
							}
							if ($v_ns4) {
								echo '<div class="u-side-by-side u-mb5">
									<input type="text" class="form-control" name="v_ns4" value="'.htmlentities(trim($v_ns4, "'")).'">
									<span class="u-ml10 js-remove-ns"><i class="fas fa-trash icon-dim icon-red"></i></span>
								</div>';
							}
							if ($v_ns5) {
								echo '<div class="u-side-by-side u-mb5">
									<input type="text" class="form-control" name="v_ns5" value="'.htmlentities(trim($v_ns5, "'")).'">
									<span class="u-ml10 js-remove-ns"><i class="fas fa-trash icon-dim icon-red"></i></span>
								</div>';
							}
							if ($v_ns6) {
								echo '<div class="u-side-by-side u-mb5">
									<input type="text" class="form-control" name="v_ns6" value="'.htmlentities(trim($v_ns6, "'")).'">
									<span class="u-ml10 js-remove-ns"><i class="fas fa-trash icon-dim icon-red"></i></span>
								</div>';
							}
							if ($v_ns7) {
								echo '<div class="u-side-by-side u-mb5">
									<input type="text" class="form-control" name="v_ns7" value="'.htmlentities(trim($v_ns7, "'")).'">
									<span class="u-ml10 js-remove-ns"><i class="fas fa-trash icon-dim icon-red"></i></span>
								</div>';
							}
							if ($v_ns8) {
								echo '<div class="u-side-by-side u-mb5">
									<input type="text" class="form-control" name="v_ns8" value="'.htmlentities(trim($v_ns8, "'")).'">
									<span class="u-ml10 js-remove-ns"><i class="fas fa-trash icon-dim icon-red"></i></span>
								</div>';
							}
						?>
						<button type="button" class="form-link u-mt20 js-add-ns" <?php if ($v_ns8) echo 'style="display:none;"'; ?>>
							<?= _("Add Name Server") ?>
						</button>
					<?php } ?>
				</div>
			<?php } ?>
		</div>

	</form>

</div>
