<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-right">
			<?php show_alert_message($_SESSION); ?>
		</div>
	</div>
</div>

<div class="container">

	<form id="main-form" name="v_generate_csr" method="post">
		<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
		<input type="hidden" name="generate" value="generate">

		<div class="form-container">
			<h1 class="u-mb20"><?= _("Generate Self-Signed SSL Certificate") ?></h1>
			<div class="u-mb10">
				<label for="v_domain" class="form-label"><?= _("Domain") ?></label>
				<input type="text" class="form-control" name="v_domain" id="v_domain" value="<?= htmlentities(trim($v_domain, "'")) ?>">
			</div>
			<div class="u-mb10">
				<label for="v_aliases" class="form-label"><?= _("Aliases") ?></label>
				<textarea class="form-control" name="v_aliases" id="v_aliases"><?= htmlentities(trim($v_aliases, "'")) ?></textarea>
			</div>
			<div class="u-mb10">
				<label for="v_email" class="form-label">
					<?= _("Email") ?>
					<span class="optional">(<?php print _("Optional"); ?>)</span>
				</label>
				<input type="email" class="form-control" name="v_email" id="v_email" value="<?= htmlentities(trim($v_email, "'")) ?>">
			</div>
			<div class="u-mb10">
				<label for="v_country" class="form-label">
					<?= _("Country") ?>
					<span class="optional">(<?= _("ISO 3166-1 alpha-2 two-letter code") ?>)</span>
				</label>
				<input type="text" class="form-control" name="v_country" id="v_country" value="<?= htmlentities(trim($v_country, "'")) ?>">
			</div>
			<div class="u-mb10">
				<label for="v_state" class="form-label">
					<?= _("State / Province") ?>
				</label>
				<input type="text" class="form-control" name="v_state" id="v_state" value="<?= htmlentities(trim($v_state, "'")) ?>">
			</div>
			<div class="u-mb10">
				<label for="v_locality" class="form-label">
					<?= _("City / Locality") ?>
				</label>
				<input type="text" class="form-control" name="v_locality" id="v_locality" value="<?= htmlentities(trim($v_locality, "'")) ?>">
			</div>
			<div class="u-mb20">
				<label for="v_org" class="form-label">
					<?= _("Organization") ?>
				</label>
				<input type="text" class="form-control" name="v_org" id="v_org" value="<?= htmlentities(trim($v_org, "'")) ?>">
			</div>
			<div class="u-side-by-side">
				<button type="submit" class="button" name="generate">
					<?= _("Generate") ?>
				</button>
			</div>
		</div>

	</form>

</div>
