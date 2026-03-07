<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/backup/exclusions/">
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

	<form id="main-form" name="v_edit_backup_exclusions" method="post">
		<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
		<input type="hidden" name="save" value="save">

		<div class="form-container">
			<h1 class="u-mb20"><?= tohtml( _("Edit Backup Exclusions")) ?></h1>
			<?php show_alert_message($_SESSION); ?>
			<div class="u-mb10">
				<label for="v_web" class="form-label"><?= tohtml( _("Web Domains")) ?></label>
				<textarea class="form-control" name="v_web" id="v_web" placeholder="<?= tohtml( _("Type domain name, one per line. To exclude all domains use *. To exclude specific dirs use following format: domain.tld:public_html/cache:public_html/tmp")) ?>"><?= tohtml(trim($v_web, "'")) ?></textarea>
			</div>
			<div class="u-mb10">
				<label for="v_mail" class="form-label"><?= tohtml( _("Mail Domains")) ?></label>
				<textarea class="form-control" name="v_mail" id="v_mail" placeholder="<?= tohtml( _("Type domain name, one per line. To exclude all domains use *. To exclude specific accounts use following format: domain.tld:info:support:postmaster")) ?>"><?= tohtml(trim($v_mail, "'")) ?></textarea>
			</div>
			<div class="u-mb10">
				<label for="v_db" class="form-label"><?= tohtml( _("Databases")) ?></label>
				<textarea class="form-control" name="v_db" id="v_db" placeholder="<?= tohtml( _("Type full database name, one per line. To exclude all databases use *")) ?>"><?= tohtml(trim($v_db, "'")) ?></textarea>
			</div>
			<div class="u-mb10">
				<label for="v_userdir" class="form-label"><?= tohtml( _("User Directory")) ?></label>
				<textarea class="form-control" name="v_userdir" id="v_userdir" placeholder="<?= tohtml( _("Type directory name, one per line. To exlude all dirs use *")) ?>"><?= tohtml(trim($v_userdir, "'")) ?></textarea>
			</div>
		</div>

	</form>

</div>
