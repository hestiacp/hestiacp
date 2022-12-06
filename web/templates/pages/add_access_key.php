<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary" id="btn-back" href="/list/access-key/">
				<i class="fas fa-arrow-left status-icon blue"></i><?=_('Back');?>
			</a>
		</div>
		<div class="toolbar-buttons">
			<a href="#" class="button" data-action="submit" data-id="vstobjects">
				<i class="fas fa-floppy-disk status-icon purple"></i><?=_('Save');?>
			</a>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container animate__animated animate__fadeIn">
	<form id="vstobjects" name="v_add_access_key" method="post">
		<input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
		<input type="hidden" name="ok" value="Add">

		<div class="form-container">
			<h1 class="form-title"><?=_('Adding Access Key');?></h1>
			<?php show_alert_message($_SESSION);?>
			<p class="u-mb10"><?=_('Permissions');?></p>
			<?php foreach ($apis as $api_name => $api_data) { ?>
				<div class="form-check">
					<input class="form-check-input" type="checkbox" value="<?= $api_name ?>" name="v_apis[]" id="v_apis_<?= $api_name ?>" tabindex="5">
					<label for="v_apis_<?= $api_name ?>">
						<?=_($api_name); ?>
					</label>
				</div>
			<?php } ?>
			<div class="u-mt15">
				<label for="v_comment" class="form-label">
					<?=_('Comment');?> <span class="optional">(<?=_('optional');?>)</span>
				</label>
				<input type="text" class="form-control" name="v_comment" id="v_comment" maxlength="255">
			</div>
		</div>

	</form>

</div>
