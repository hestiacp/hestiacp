<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/db/">
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
		name="v_add_db"
		method="post"
	>
		<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
		<input type="hidden" name="ok" value="Add">

		<div class="form-container">
			<h1 class="u-mb20"><?= _("Add Database") ?></h1>
			<?php show_alert_message($_SESSION); ?>
			<?php if ($user_plain == "admin" && $accept !== "true") { ?>
				<div class="alert alert-danger" role="alert">
					<i class="fas fa-exclamation"></i>
					<p><?= htmlify_trans(sprintf(_("It is strongly advised to {create a standard user account} before adding %s to the server due to the increased privileges the admin account possesses and potential security risks."), _('a database')), '</a>', '<a href="/add/user/">'); ?></p>
				</div>
			<?php } ?>
			<?php if ($user_plain == "admin" && empty($accept)) { ?>
				<div class="u-side-by-side u-mt20">
					<a href="/add/user/" class="button u-width-full u-mr10"><?= _("Add User") ?></a>
					<a href="/add/db/?accept=true" class="button button-danger u-width-full u-ml10"><?= _("Continue") ?></a>
				</div>
			<?php } ?>
			<?php if (($user_plain == "admin" && $accept === "true") || $user_plain !== "admin") { ?>
				<p class="hint u-mb20">
					<?= sprintf(_("Prefix %s will be automatically added to database name and database user"), "<span class=\"u-text-bold\">" . $user_plain . "_</span>") ?>
				</p>
				<div class="u-mb10">
					<label for="v_database" class="form-label"><?= _("Database") ?></label>
					<input type="text" class="form-control js-db-hint-database-name" name="v_database" id="v_database" value="<?= htmlentities(trim($v_database, "'")) ?>">
					<small class="hint"></small>
				</div>
				<div class="u-mb10">
					<label for="v_type" class="form-label"><?= _("Type") ?></label>
					<select class="form-select" name="v_type" id="v_type">
						<?php
							foreach ($db_types as $key => $value) {
								echo "\n\t\t\t\t\t\t\t\t\t\t<option value=\"".htmlentities($value)."\"";
								if ((!empty($v_type)) && ( $value == $v_type )) echo ' selected';
								echo ">".htmlentities($value)."</option>";
							}
						?>
					</select>
				</div>
				<div class="u-mb10">
					<label for="v_dbuser" class="form-label u-side-by-side">
						<?= _("Username") ?>
						<em><small>(<?= sprintf(_("Maximum %s characters length, including prefix"), 32) ?>)</small></em>
					</label>
					<input type="text" class="form-control js-db-hint-username" name="v_dbuser" id="v_dbuser" value="<?= htmlentities(trim($v_dbuser, "'")) ?>">
					<small class="hint"></small>
				</div>
				<div class="u-mb10">
					<label for="v_password" class="form-label">
						<?= _("Password") ?>
						<button type="button" title="<?= _("Generate") ?>" class="u-unstyled-button u-ml5 js-generate-password">
							<i class="fas fa-arrows-rotate icon-green"></i>
						</button>
					</label>
					<div class="u-pos-relative u-mb10">
						<input type="text" class="form-control js-password-input" name="v_password" id="v_password">
						<div class="password-meter">
							<meter max="4" class="password-meter-input js-password-meter"></meter>
						</div>
					</div>
				</div>
				<p class="u-mb10"><?= _("Your password must have at least") ?>:</p>
				<ul class="u-list-bulleted u-mb10">
					<li><?= _("8 characters long") ?></li>
					<li><?= _("1 uppercase & 1 lowercase character") ?></li>
					<li><?= _("1 number") ?></li>
				</ul>
				<div class="u-mb20">
					<label for="v_db_email" class="form-label">
						<?= _("Email login credentials to:") ?>
					</label>
					<input type="email" class="form-control" name="v_db_email" id="v_db_email" value="<?= htmlentities(trim($v_db_email, "'")) ?>">
				</div>
				<div class="u-mb20">
					<button x-on:click="showAdvanced = !showAdvanced" type="button" class="button button-secondary">
						<?= _("Advanced Options") ?>
					</button>
				</div>
				<div x-cloak x-show="showAdvanced">
					<div class="u-mb10">
						<label for="v_host" class="form-label"><?= _("Host") ?></label>
						<select class="form-select" name="v_host" id="v_host">
							<?php
								foreach ($db_hosts as $value) {
									echo "\n\t\t\t\t\t\t\t\t\t\t<option value=\"".htmlentities($value)."\"";
									if ((!empty($v_host)) && ( $value == $v_host )) echo ' selected';
									echo ">".htmlentities($value)."</option>";
								}
							?>
						</select>
					</div>
					<div class="u-mb10">
						<label for="v_charset" class="form-label"><?= _("Charset") ?></label>
						<select class="form-select" name="v_charset" id="v_charset">
							<option value=big5 <?php if ((!empty($v_charset)) && ( $v_charset == 'big5')) echo 'selected'; ?>>big5</option>
							<option value=dec8 <?php if ((!empty($v_charset)) && ( $v_charset == 'dec8')) echo 'selected'; ?>>dec8</option>
							<option value=cp850 <?php if ((!empty($v_charset)) && ( $v_charset == 'cp850')) echo 'selected'; ?>>cp850</option>
							<option value=hp8 <?php if ((!empty($v_charset)) && ( $v_charset == 'hp8')) echo 'selected'; ?>>hp8</option>
							<option value=koi8r <?php if ((!empty($v_charset)) && ( $v_charset == 'koi8r')) echo 'selected'; ?>>koi8r</option>
							<option value=latin1 <?php if ((!empty($v_charset)) && ( $v_charset == 'latin1')) echo 'selected'; ?>>latin1</option>
							<option value=latin2 <?php if ((!empty($v_charset)) && ( $v_charset == 'latin2')) echo 'selected'; ?>>latin2</option>
							<option value=swe7 <?php if ((!empty($v_charset)) && ( $v_charset == 'swe7')) echo 'selected'; ?>>swe7</option>
							<option value=ascii <?php if ((!empty($v_charset)) && ( $v_charset == 'ascii')) echo 'selected'; ?>>ascii</option>
							<option value=ujis <?php if ((!empty($v_charset)) && ( $v_charset == 'ujis')) echo 'selected'; ?>>ujis</option>
							<option value=sjis <?php if ((!empty($v_charset)) && ( $v_charset == 'sjis')) echo 'selected'; ?>>sjis</option>
							<option value=hebrew <?php if ((!empty($v_charset)) && ( $v_charset == 'hebrew')) echo 'selected'; ?>>hebrew</option>
							<option value=tis620 <?php if ((!empty($v_charset)) && ( $v_charset == 'tis620')) echo 'selected'; ?>>tis620</option>
							<option value=euckr <?php if ((!empty($v_charset)) && ( $v_charset == 'euckr')) echo 'selected'; ?>>euckr</option>
							<option value=koi8u <?php if ((!empty($v_charset)) && ( $v_charset == 'koi8u')) echo 'selected'; ?>>koi8u</option>
							<option value=gb2312 <?php if ((!empty($v_charset)) && ( $v_charset == 'gb2312')) echo 'selected'; ?>>gb2312</option>
							<option value=greek <?php if ((!empty($v_charset)) && ( $v_charset == 'greek')) echo 'selected'; ?>>greek</option>
							<option value=cp1250 <?php if ((!empty($v_charset)) && ( $v_charset == 'cp1250')) echo 'selected'; ?>>cp1250</option>
							<option value=gbk <?php if ((!empty($v_charset)) && ( $v_charset == 'gbk')) echo 'selected'; ?>>gbk</option>
							<option value=latin5 <?php if ((!empty($v_charset)) && ( $v_charset == 'latin5')) echo 'selected'; ?>>latin5</option>
							<option value=armscii8 <?php if ((!empty($v_charset)) && ( $v_charset == 'armscii8')) echo 'selected'; ?>>armscii8</option>
							<option value=utf8 <?php if ((!empty($v_charset)) && ( $v_charset == 'utf8')) echo 'selected'; ?>>utf8</option>
							<option value=utf8mb4 <?php if ((!empty($v_charset)) && ( $v_charset == 'utf8mb4')) echo 'selected'; ?> <?php if (empty($v_charset)) echo 'selected'; ?>>utf8mb4</option>
							<option value=ucs2 <?php if ((!empty($v_charset)) && ( $v_charset == 'ucs2')) echo 'selected'; ?>>ucs2</option>
							<option value=cp866 <?php if ((!empty($v_charset)) && ( $v_charset == 'cp866')) echo 'selected'; ?>>cp866</option>
							<option value=keybcs2 <?php if ((!empty($v_charset)) && ( $v_charset == 'keybcs2')) echo 'selected'; ?>>keybcs2</option>
							<option value=macce <?php if ((!empty($v_charset)) && ( $v_charset == 'macce')) echo 'selected'; ?>>macce</option>
							<option value=macroman <?php if ((!empty($v_charset)) && ( $v_charset == 'macroman')) echo 'selected'; ?>>macroman</option>
							<option value=cp852 <?php if ((!empty($v_charset)) && ( $v_charset == 'cp852')) echo 'selected'; ?>>cp852</option>
							<option value=latin7 <?php if ((!empty($v_charset)) && ( $v_charset == 'latin7')) echo 'selected'; ?>>latin7</option>
							<option value=cp1251 <?php if ((!empty($v_charset)) && ( $v_charset == 'cp1251')) echo 'selected'; ?>>cp1251</option>
							<option value=cp1256 <?php if ((!empty($v_charset)) && ( $v_charset == 'cp1256')) echo 'selected'; ?>>cp1256</option>
							<option value=cp1257 <?php if ((!empty($v_charset)) && ( $v_charset == 'cp1257')) echo 'selected'; ?>>cp1257</option>
							<option value=binary <?php if ((!empty($v_charset)) && ( $v_charset == 'binary')) echo 'selected'; ?>>binary</option>
							<option value=geostd8 <?php if ((!empty($v_charset)) && ( $v_charset == 'geostd8')) echo 'selected'; ?>>geostd8</option>
							<option value=cp932 <?php if ((!empty($v_charset)) && ( $v_charset == 'cp932')) echo 'selected'; ?>>cp932</option>
							<option value=eucjpms <?php if ((!empty($v_charset)) && ( $v_charset == 'eucjpms')) echo 'selected'; ?>>eucjpms</option>
						</select>
					</div>
				</div>
			<?php } ?>
		</div>

	</form>

</div>
