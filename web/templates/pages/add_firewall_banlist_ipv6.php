<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/firewall/banlist/ipv6/">
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
	<form id="main-form" name="v_add_ipv6_ban" method="post">
		<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
		<input type="hidden" name="ok" value="Add">

		<div class="form-container">
			<h1 class="u-mb20"><?= tohtml( _("Ban IPv6 Address")) ?></h1>
			<?php show_alert_message($_SESSION); ?>
			<div class="u-mb20">
				<label for="v_ip" class="form-label">
					<?= tohtml( _("IPv6 Address")) ?> <span class="optional">(<?= tohtml( _("Support CIDR format")) ?>)</span>
				</label>
				<input type="text" class="form-control" name="v_ip" id="v_ip"
					value="<?= tohtml(trim($v_ip, "'")) ?>"
					placeholder="e.g. 2001:db8::1 or 2001:db8::/32"
					required>
			</div>
			<div class="u-mb10">
				<label for="v_chain" class="form-label"><?= tohtml( _("Banlist")) ?></label>
				<select class="form-select" name="v_chain" id="v_chain">
					<option value="SSH"    <?php if (trim($v_chain,"'") == "SSH")    echo 'selected'; ?>><?= tohtml( _("SSH")) ?></option>
					<option value="MAIL"   <?php if (trim($v_chain,"'") == "MAIL")   echo 'selected'; ?>><?= tohtml( _("MAIL")) ?></option>
					<option value="HESTIA" <?php if (trim($v_chain,"'") == "HESTIA") echo 'selected'; ?>><?= tohtml( _("HESTIA")) ?></option>
					<option value="RECIDIVE" <?php if (trim($v_chain,"'") == "RECIDIVE") echo 'selected'; ?>><?= tohtml( _("RECIDIVE")) ?></option>
				</select>
			</div>
		</div>
	</form>
</div>
