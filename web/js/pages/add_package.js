$('form[name="v_add_package"]').on('submit', function () {
	$('input:disabled').each(function (i, elm) {
		$(elm).attr('disabled', false);
		if (Alpine.store('globals').isUnlimitedValue($(elm).val())) {
			$(elm).val(Alpine.store('globals').UNLIM_VALUE);
		}
	});
});
