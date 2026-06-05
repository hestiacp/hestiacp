<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/db-host/">
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

	<form id="main-form" name="v_edit_db_host" method="post" class="<?= tohtml($v_suspended === "yes" ? "suspended" : "active") ?>">
		<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
		<input type="hidden" name="save" value="save">

		<div class="form-container">
			<h1 class="u-mb20"><?= tohtml( _("Edit Database Server")) ?></h1>
			<?php show_alert_message($_SESSION); ?>
			<div class="u-mb10">
				<label for="v_endpoint" class="form-label"><?= tohtml( _("Endpoint")) ?></label>
				<input type="text" class="form-control" name="v_endpoint" id="v_endpoint" value="<?= tohtml($v_endpoint) ?>" disabled>
			</div>
			<div class="u-mb10">
				<label for="v_type" class="form-label"><?= tohtml( _("Type")) ?></label>
				<input type="text" class="form-control" name="v_type" id="v_type" value="<?= tohtml($v_type) ?>" disabled>
			</div>
			<div class="u-mb10">
				<label for="v_dbuser" class="form-label"><?= tohtml( _("Username")) ?></label>
				<input type="text" class="form-control" name="v_dbuser" id="v_dbuser" value="<?= tohtml($v_dbuser) ?>" disabled>
			</div>
			<div class="u-mb10">
				<label for="v_password" class="form-label"><?= tohtml( _("Password")) ?></label>
				<div class="u-pos-relative">
					<input type="text" class="form-control js-password-input" name="v_password" id="v_password" value="">
				</div>
			</div>
			<div class="u-mb10">
				<label for="v_max_db" class="form-label"><?= tohtml( _("Maximum Number of Databases")) ?></label>
				<input type="text" class="form-control" name="v_max_db" id="v_max_db" value="<?= tohtml($v_max_db) ?>" disabled>
			</div>
			<div class="u-mb10">
				<label for="v_charsets" class="form-label"><?= tohtml($v_type === "pgsql" ? _("Template") : _("Charsets")) ?></label>
				<input type="text" class="form-control" name="v_charsets" id="v_charsets" value="<?= tohtml($v_type === "pgsql" ? $v_template : $v_charsets) ?>" disabled>
			</div>
		</div>

	</form>

</div>
