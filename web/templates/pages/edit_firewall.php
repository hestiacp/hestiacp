<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/firewall/">
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

	<form id="main-form" name="v_edit_firewall" method="post" class="<?= $v_status ?>">
		<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
		<input type="hidden" name="save" value="save">

		<div class="form-container">
			<h1 class="u-mb20"><?= _("Edit Firewall Rule") ?></h1>
			<?php show_alert_message($_SESSION); ?>
			<div class="u-mb10">
				<label for="v_action" class="form-label"><?= _("Action") ?></label>
				<select class="form-select" name="v_action" id="v_action">
					<option value="DROP" <?php if ((!empty($v_action)) && ( $v_action == "DROP" )) echo 'selected'?>><?= _("DROP") ?></option>
					<option value="ACCEPT" <?php if ((!empty($v_action)) && ( $v_action == "ACCEPT" )) echo 'selected'?>><?= _("ACCEPT") ?></option>
				</select>
			</div>
			<div class="u-mb10">
				<label for="v_protocol" class="form-label"><?= _("Protocol") ?></label>
				<select class="form-select" name="v_protocol" id="v_protocol">
					<option value="TCP" <?php if ((!empty($v_protocol)) && ( $v_protocol == "TCP" )) echo 'selected'?>>TCP</option>
					<option value="UDP" <?php if ((!empty($v_protocol)) && ( $v_protocol == "UDP" )) echo 'selected'?>>UDP</option>
					<option value="ICMP" <?php if ((!empty($v_protocol)) && ( $v_protocol == "ICMP" )) echo 'selected'?>>ICMP</option>
				</select>
			</div>
			<div class="u-mb10">
				<label for="v_port" class="form-label">
					<?= _("Port") ?> <span class="optional">(<?= _("Ranges and lists are acceptable") ?>)</span>
				</label>
				<input type="text" class="form-control" name="v_port" id="v_port" value="<?= htmlentities(trim($v_port, "'")) ?>" placeholder="<?= _("All ports: 0, Range: 80-82, List: 80,443,8080,8443") ?>">
			</div>
			<div class="u-mb10">
				<label for="v_ip" class="form-label">
					<?= _("IP Address / IPset IP List") ?> <span class="optional">(<?= _("Support CIDR format") ?>)</span>
				</label>
				<div class="u-pos-relative">
					<select
						class="form-select js-ip-list-select"
						tabindex="-1"
						onchange="this.nextElementSibling.value=this.value"
						data-ipset-lists="<?= htmlspecialchars($ipset_lists_json, ENT_QUOTES, "UTF-8") ?>"
					>
						<option value=""><?= _("Clear") ?></option>
					</select>
					<input type="text" class="form-control list-editor" name="v_ip" id="v_ip" value="<?= htmlentities(trim($v_ip, "'")) ?>">
				</div>
			</div>
			<div class="u-mb10">
				<label for="v_comment" class="form-label">
					<?= _("Comment") ?> <span class="optional">(<?= _("Optional") ?>)</span>
				</label>
				<input type="text" class="form-control" name="v_comment" id="v_comment" maxlength="255" value="<?= htmlentities(trim($v_comment, "'")) ?>">
			</div>
		</div>

	</form>

</div>
