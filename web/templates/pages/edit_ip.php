<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/ip/">
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

	<form
		x-data="{
			showUserTable: <?= tohtml(empty($v_dedicated) ? "true" : "false") ?>
		}"
		id="main-form"
		name="v_edit_ip"
		method="post"
	>
		<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
		<input type="hidden" name="save" value="save">

		<div class="form-container">
			<h1 class="u-mb20"><?= tohtml( _("Edit IP Address")) ?></h1>
			<?php show_alert_message($_SESSION); ?>
			<div class="u-mb10">
				<label for="v_ip" class="form-label"><?= tohtml( _("IP Address")) ?></label>
				<input type="text" class="form-control" name="v_ip" id="v_ip" value="<?= tohtml( htmlentities(trim($v_ip, "'"))) ?>" disabled>
				<input type="hidden" name="v_ip" value="<?= tohtml( htmlentities(trim($v_ip, "'"))) ?>">
			</div>
			<div class="u-mb10">
				<label for="v_netmask" class="form-label"><?= tohtml( _("Netmask")) ?></label>
				<input type="text" class="form-control" name="v_netmask" id="v_netmask" value="<?= tohtml( htmlentities(trim($v_netmask, "'"))) ?>" disabled>
			</div>
			<div class="u-mb10">
				<label for="v_interface" class="form-label"><?= tohtml( _("Interface")) ?></label>
				<input type="text" class="form-control" name="v_interface" id="v_interface" value="<?= tohtml( htmlentities(trim($v_interface, "'"))) ?>" disabled>
			</div>
			<div class="form-check u-mb10">
				<input x-model="showUserTable" class="form-check-input" type="checkbox" name="v_shared" id="v_shared">
				<label for="v_shared">
					<?= tohtml( _("Shared")) ?>
				</label>
			</div>
			<div x-cloak x-show="!showUserTable" id="usrtable">
				<div class="u-mb10">
					<label for="v_owner" class="form-label"><?= tohtml( _("Assigned User")) ?></label>
					<select class="form-select" name="v_owner" id="v_owner">
						<?php
							foreach ($users as $key => $value) {
								echo "\t\t\t\t<option value=\"".htmlentities($value)."\"";
								if ((!empty($v_owner)) && ( $value == $v_owner )) echo ' selected';
								echo ">".htmlentities($value)."</option>\n";
							}
						?>
					</select>
				</div>
			</div>
			<div class="u-mb10">
				<label for="v_name" class="form-label">
					<?= tohtml( _("Assigned Domain")) ?> <span class="optional">(<?= tohtml( _("Optional")) ?>)</span>
				</label>
				<input type="text" class="form-control" name="v_name" id="v_name" value="<?= tohtml( htmlentities(trim($v_name, "'"))) ?>">
			</div>
			<div class="u-mb10">
				<label for="v_nat" class="form-label">
					<?= tohtml( _("NAT IP Association")) ?> <span class="optional">(<?= tohtml( _("Optional")) ?>)</span>
				</label>
				<input type="text" class="form-control" name="v_nat" id="v_nat" value="<?= tohtml( htmlentities(trim($v_nat, "'"))) ?>">
			</div>
		</div>

	</form>

</div>
