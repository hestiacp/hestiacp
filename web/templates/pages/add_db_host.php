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

	<form
		x-data="{
			selectedType: '<?= tohtml($v_type) ?>',
			syncDefaults() {
				if (this.selectedType === 'pgsql') {
					if (!this.$refs.port.value || this.$refs.port.value === '3306') this.$refs.port.value = '5432';
					if (!this.$refs.template.value) this.$refs.template.value = 'template1';
				} else {
					if (!this.$refs.port.value || this.$refs.port.value === '5432') this.$refs.port.value = '3306';
				}
			}
		}"
		x-init="syncDefaults()"
		id="main-form"
		name="v_add_db_host"
		method="post"
	>
		<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
		<input type="hidden" name="ok" value="Add">

		<div class="form-container">
			<h1 class="u-mb20"><?= tohtml( _("Add Database Server")) ?></h1>
			<?php show_alert_message($_SESSION); ?>
			<div class="u-mb10">
				<label for="v_type" class="form-label"><?= tohtml( _("Type")) ?></label>
				<select class="form-select" name="v_type" id="v_type" x-model="selectedType" x-on:change="syncDefaults()">
					<?php foreach ($db_types as $db_type) { ?>
						<option value="<?= tohtml($db_type) ?>" <?= tohtml($v_type === $db_type ? "selected" : "") ?>>
							<?= tohtml($db_type) ?>
						</option>
					<?php } ?>
				</select>
			</div>
			<div class="u-mb10">
				<label for="v_host" class="form-label"><?= tohtml( _("Host")) ?></label>
				<input type="text" class="form-control" name="v_host" id="v_host" value="<?= tohtml($v_host) ?>">
			</div>
			<div class="u-mb10">
				<label for="v_port" class="form-label"><?= tohtml( _("Port")) ?></label>
				<input type="text" class="form-control" name="v_port" id="v_port" x-ref="port" value="<?= tohtml($v_port) ?>">
			</div>
			<div class="u-mb10">
				<label for="v_dbuser" class="form-label"><?= tohtml( _("Username")) ?></label>
				<input type="text" class="form-control" name="v_dbuser" id="v_dbuser" value="<?= tohtml($v_dbuser) ?>">
			</div>
			<div class="u-mb10">
				<label for="v_password" class="form-label"><?= tohtml( _("Password")) ?></label>
				<div class="u-pos-relative">
					<input type="text" class="form-control js-password-input" name="v_password" id="v_password" value="">
				</div>
			</div>
			<div class="u-mb10">
				<label for="v_max_db" class="form-label"><?= tohtml( _("Maximum Number of Databases")) ?></label>
				<input type="text" class="form-control" name="v_max_db" id="v_max_db" value="<?= tohtml($v_max_db) ?>">
			</div>
			<div class="u-mb10" x-show="selectedType === 'mysql'">
				<label for="v_charsets" class="form-label"><?= tohtml( _("Charsets")) ?></label>
				<input type="text" class="form-control" name="v_charsets" id="v_charsets" value="<?= tohtml($v_charsets) ?>">
			</div>
			<div class="u-mb10" x-show="selectedType === 'pgsql'">
				<label for="v_template" class="form-label"><?= tohtml( _("Template")) ?></label>
				<input type="text" class="form-control" name="v_template" id="v_template" x-ref="template" value="<?= tohtml($v_template) ?>">
			</div>
		</div>

	</form>

</div>
