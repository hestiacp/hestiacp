<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/firewall/banlist/">
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

	<form id="main-form" name="v_add_ip" method="post">
		<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
		<input type="hidden" name="ok" value="Add">

		<div class="form-container">
			<h1 class="u-mb20"><?= tohtml( _("Add IP Address to Banlist")) ?></h1>
			<?php show_alert_message($_SESSION); ?>
			<div class="u-mb20">
				<label for="v_ip" class="form-label">
					<?= tohtml( _("IP Address")) ?> <span class="optional">(<?= tohtml( _("Support CIDR format")) ?>)</span>
				</label>
				<input type="text" class="form-control" name="v_ip" id="v_ip" value="<?= tohtml( htmlentities(trim($v_ip, "'"))) ?>" required>
			</div>
			<div class="u-mb10">
				<label for="v_chain" class="form-label"><?= tohtml( _("Banlist")) ?></label>
				<select class="form-select" name="v_chain" id="v_chain">
					<option value="SSH" <?php if ((!empty($v_chain)) && ( $v_chain == "'SSH'" )) echo 'selected'?>><?= tohtml( _("SSH")) ?></option>
					<option value="WEB" <?php if ((!empty($v_chain)) && ( $v_chain == "'WEB'" )) echo 'selected'?>><?= tohtml( _("WEB")) ?></option>
					<option value="FTP" <?php if ((!empty($v_chain)) && ( $v_chain == "'FTP'" )) echo 'selected'?>><?= tohtml( _("FTP")) ?></option>
					<option value="DNS" <?php if ((!empty($v_chain)) && ( $v_chain == "'DNS'" )) echo 'selected'?>><?= tohtml( _("DNS")) ?></option>
					<option value="MAIL" <?php if ((!empty($v_chain)) && ( $v_chain == "'MAIL'" )) echo 'selected'?>><?= tohtml( _("MAIL")) ?></option>
					<option value="DB" <?php if ((!empty($v_chain)) && ( $v_chain == "'DB'" )) echo 'selected'?>><?= tohtml( _("DB")) ?></option>
					<option value="HESTIA" <?php if ((!empty($v_chain)) && ( $v_chain == "'HESTIA'" )) echo 'selected'?>><?= tohtml( _("HESTIA")) ?></option>
				</select>
			</div>
		</div>

	</form>

</div>
