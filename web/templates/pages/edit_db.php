<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/db/">
				<i class="fas fa-arrow-left icon-blue"></i><?= tohtml( _("Back")) ?>
			</a>
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

	<form id="main-form" name="v_edit_db" method="post" class="<?= tohtml($v_status) ?>">
		<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
		<input type="hidden" name="save" value="save">

		<div class="form-container">
			<h1 class="u-mb20"><?= tohtml( _("Edit Database")) ?></h1>
			<?php show_alert_message($_SESSION); ?>
			<div class="u-mb10">
				<label for="v_database" class="form-label"><?= tohtml( _("Database")) ?></label>
				<input type="text" class="form-control js-db-hint-database-name" name="v_database" id="v_database" value="<?= tohtml(trim($v_database, "'")) ?>" disabled>
				<small class="hint"></small>
			</div>
			<div class="u-mb10">
				<label for="v_dbuser" class="form-label u-side-by-side">
					<?= tohtml( _("Username")) ?>
					<em><small>(<?= tohtml(sprintf(_("Maximum %s characters length, including prefix"), 32)) ?>)</small></em>
				</label>
				<input type="text" class="form-control js-db-hint-username" name="v_dbuser" id="v_dbuser" value="<?= tohtml(trim($v_dbuser, "'")) ?>">
				<small class="hint"></small>
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
			<?php require $_SERVER["HESTIA"] . "/web/templates/includes/password-requirements.php"; ?>
			<div class="u-mb10">
				<label for="v_type" class="form-label"><?= tohtml( _("Type")) ?></label>
				<input type="text" class="form-control" name="v_type" id="v_type" value="<?= tohtml(trim($v_type, "'")) ?>" disabled>
			</div>
			<div class="u-mb10">
				<label for="v_host" class="form-label"><?= tohtml( _("Host")) ?></label>
				<input type="text" class="form-control" name="v_host" id="v_host" value="<?= tohtml(trim($v_host, "'")) ?>" disabled>
			</div>
			<div class="u-mb10">
				<label for="v_charset" class="form-label"><?= tohtml( _("Charset")) ?></label>
				<input type="text" class="form-control" name="v_charset" id="v_charset" value="<?= tohtml(trim($v_charset, "'")) ?>" disabled>
			</div>
		</div>

	</form>

</div>
