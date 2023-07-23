<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/backup/exclusions/">
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

	<form id="main-form" name="v_edit_backup_exclusions" method="post">
		<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
		<input type="hidden" name="save" value="save">

		<div class="form-container">
			<h1 class="u-mb20"><?= _("Edit Backup Exclusions") ?></h1>
			<?php show_alert_message($_SESSION); ?>
			<div class="u-mb10">
				<label for="v_web" class="form-label"><?= _("Web Domains") ?></label>
				<textarea class="form-control" name="v_web" id="v_web" placeholder="<?= _("Type domain name, one per line. To exclude all domains use *. To exclude specific dirs use following format: domain.tld:public_html/cache:public_html/tmp") ?>"><?= htmlentities(trim($v_web, "'")) ?></textarea>
			</div>
			<div class="u-mb10">
				<label for="v_mail" class="form-label"><?= _("Mail Domains") ?></label>
				<textarea class="form-control" name="v_mail" id="v_mail" placeholder="<?= _("Type domain name, one per line. To exclude all domains use *. To exclude specific accounts use following format: domain.tld:info:support:postmaster") ?>"><?= htmlentities(trim($v_mail, "'")) ?></textarea>
			</div>
			<div class="u-mb10">
				<label for="v_db" class="form-label"><?= _("Databases") ?></label>
				<textarea class="form-control" name="v_db" id="v_db" placeholder="<?= _("Type full database name, one per line. To exclude all databases use *") ?>"><?= htmlentities(trim($v_db, "'")) ?></textarea>
			</div>
			<div class="u-mb10">
				<label for="v_userdir" class="form-label"><?= _("User Directory") ?></label>
				<textarea class="form-control" name="v_userdir" id="v_userdir" placeholder="<?= _("Type directory name, one per line. To exlude all dirs use *") ?>"><?= htmlentities(trim($v_userdir, "'")) ?></textarea>
			</div>
		</div>

	</form>

</div>
