<?php
	$nsValues = ['v_ns3', 'v_ns4', 'v_ns5', 'v_ns6', 'v_ns7', 'v_ns8'];
	foreach ($nsValues as $nsValue) {
		if ($$nsValue) {
			?>
			<div class="u-side-by-side u-mb5">
				<input type="text" class="form-control" name="<?php echo $nsValue; ?>" value="<?php echo htmlentities(trim($$nsValue, "'")); ?>">
				<button type="button" class="u-button-reset u-ml10 js-remove-ns" title="<?= _("Remove") ?>">
					<i class="fas fa-trash icon-dim icon-red"></i>
				</button>
			</div>
			<?php
		}
	}
?>
