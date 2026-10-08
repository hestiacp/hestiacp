<div class="u-mb10">
	<label for="v_description" class="form-label">
		<?= tohtml( _("Description")) ?> <span class="optional">(<?= tohtml( _("Optional")) ?>)</span>
	</label>
	<input type="text" class="form-control" name="v_description" id="v_description" maxlength="255" value="<?= tohtml($v_description ?? "") ?>">
</div>
