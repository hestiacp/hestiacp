<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/firewall/banlist/">
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

	<form id="main-form" name="v_add_ip" method="post">
		<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
		<input type="hidden" name="ok" value="Add">

		<div class="form-container">
			<h1 class="u-mb20"><?= _("Add IP Address to Banlist") ?></h1>
			<?php show_alert_message($_SESSION); ?>
			<div class="u-mb20">
				<label for="v_ip" class="form-label">
					<?= _("IP Address") ?> <span class="optional">(<?= _("Support CIDR format") ?>)</span>
				</label>
				<input type="text" class="form-control" name="v_ip" id="v_ip" value="<?= htmlentities(trim($v_ip, "'")) ?>" required>
			</div>
			<div class="u-mb10">
				<label for="v_chain" class="form-label"><?= _("Banlist") ?></label>
				<select class="form-select" name="v_chain" id="v_chain">
					<option value="SSH" <?php if ((!empty($v_chain)) && ( $v_chain == "'SSH'" )) echo 'selected'?>><?= _("SSH") ?></option>
					<option value="WEB" <?php if ((!empty($v_chain)) && ( $v_chain == "'WEB'" )) echo 'selected'?>><?= _("WEB") ?></option>
					<option value="FTP" <?php if ((!empty($v_chain)) && ( $v_chain == "'FTP'" )) echo 'selected'?>><?= _("FTP") ?></option>
					<option value="DNS" <?php if ((!empty($v_chain)) && ( $v_chain == "'DNS'" )) echo 'selected'?>><?= _("DNS") ?></option>
					<option value="MAIL" <?php if ((!empty($v_chain)) && ( $v_chain == "'MAIL'" )) echo 'selected'?>><?= _("MAIL") ?></option>
					<option value="DB" <?php if ((!empty($v_chain)) && ( $v_chain == "'DB'" )) echo 'selected'?>><?= _("DB") ?></option>
					<option value="HESTIA" <?php if ((!empty($v_chain)) && ( $v_chain == "'HESTIA'" )) echo 'selected'?>><?= _("HESTIA") ?></option>
				</select>
			</div>
		</div>

	</form>

</div>
