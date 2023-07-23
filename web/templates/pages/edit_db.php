<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/db/">
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

	<form id="main-form" name="v_edit_db" method="post" class="<?= $v_status ?>">
		<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
		<input type="hidden" name="save" value="save">

		<div class="form-container">
			<h1 class="u-mb20"><?= _("Edit Database") ?></h1>
			<?php show_alert_message($_SESSION); ?>
			<div class="u-mb10">
				<label for="v_database" class="form-label"><?= _("Database") ?></label>
				<input type="text" class="form-control js-db-hint-database-name" name="v_database" id="v_database" value="<?= htmlentities(trim($v_database, "'")) ?>" disabled>
				<small class="hint"></small>
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
					<input type="text" class="form-control js-password-input" name="v_password" id="v_password" value="<?= htmlentities(trim($v_password, "'")) ?>">
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
			<div class="u-mb10">
				<label for="v_type" class="form-label"><?= _("Type") ?></label>
				<input type="text" class="form-control" name="v_type" id="v_type" value="<?= htmlentities(trim($v_type, "'")) ?>" disabled>
			</div>
			<div class="u-mb10">
				<label for="v_host" class="form-label"><?= _("Host") ?></label>
				<input type="text" class="form-control" name="v_host" id="v_host" value="<?= htmlentities(trim($v_host, "'")) ?>" disabled>
			</div>
			<div class="u-mb10">
				<label for="v_charset" class="form-label"><?= _("Charset") ?></label>
				<input type="text" class="form-control" name="v_charset" id="v_charset" value="<?= htmlentities(trim($v_charset, "'")) ?>" disabled>
			</div>
		</div>

	</form>

</div>
