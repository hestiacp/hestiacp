<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/firewall/">
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
			<h1 class="u-mb20"><?= tohtml( _("Add Firewall Rule")) ?></h1>
			<?php show_alert_message($_SESSION); ?>
			<div class="u-mb10">
				<label for="v_action" class="form-label"><?= tohtml( _("Action")) ?></label>
				<select class="form-select" name="v_action" id="v_action">
					<option value="DROP" <?php if ((!empty($v_action)) && ( $v_action == "'DROP'" )) echo 'selected'?>><?= tohtml( _("DROP")) ?></option>
					<option value="ACCEPT" <?php if ((!empty($v_action)) && ( $v_action == "'ACCEPT'" )) echo 'selected'?>><?= tohtml( _("ACCEPT")) ?></option>
				</select>
			</div>
			<div class="u-mb10">
				<label for="v_protocol" class="form-label"><?= tohtml( _("Protocol")) ?></label>
				<select class="form-select" name="v_protocol" id="v_protocol">
					<option value="TCP" <?php if ((!empty($v_protocol)) && ( $v_protocol == "'TCP'" )) echo 'selected'?>>TCP</option>
					<option value="UDP" <?php if ((!empty($v_protocol)) && ( $v_protocol == "'UDP'" )) echo 'selected'?>>UDP</option>
					<option value="ICMP" <?php if ((!empty($v_protocol)) && ( $v_protocol == "'ICMP'" )) echo 'selected'?>>ICMP</option>
				</select>
			</div>
			<div class="u-mb10">
				<label for="v_port" class="form-label">
					<?= tohtml( _("Port")) ?> <span class="optional">(<?= tohtml( _("Ranges and lists are acceptable")) ?>)</span>
				</label>
				<input type="text" class="form-control" name="v_port" id="v_port" value="<?= tohtml( htmlentities(trim($v_port, "'"))) ?>" placeholder="<?= tohtml( _("All ports: 0, Range: 80-82, List: 80,443,8080,8443")) ?>">
			</div>
			<div class="u-mb10">
				<label for="v_ip" class="form-label">
					<?= tohtml( _("IP Address / IPset IP List")) ?> <span class="optional">(<?= tohtml( _("Support CIDR format")) ?>)</span>
				</label>
				<div class="u-pos-relative">
					<select
						class="form-select js-ip-list-select"
						tabindex="-1"
						onchange="this.nextElementSibling.value=this.value"
						data-ipset-lists="<?= tohtml( htmlspecialchars($ipset_lists_json, ENT_QUOTES, "UTF-8")) ?>"
					>
						<option value=""><?= tohtml( _("Clear")) ?></option>
					</select>
					<input type="text" class="form-control list-editor" name="v_ip" id="v_ip" value="<?= tohtml( htmlentities(trim($v_ip, "'"))) ?>">
				</div>
			</div>
			<div class="u-mb10">
				<label for="v_comment" class="form-label">
					<?= tohtml( _("Comment")) ?> <span class="optional">(<?= tohtml( _("Optional")) ?>)</span>
				</label>
				<input type="text" class="form-control" name="v_comment" id="v_comment" maxlength="255" value="<?= tohtml( htmlentities(trim($v_comment, "'"))) ?>">
			</div>
		</div>

	</form>

</div>
