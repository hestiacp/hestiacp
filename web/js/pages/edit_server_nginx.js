// eslint-disable-next-line @typescript-eslint/no-unused-vars
function toggleOptions() {
	if ($('#advanced-options').is(':visible')) {
		$('#advanced-options').hide();
		$('#basic-options').show();
	} else {
		$('#advanced-options').show();
		$('#basic-options').hide();

		var advance_options = $('#advanced-options textarea');

		$('#vstobjects input[type=text]').each(function (i, elm) {
			var search = $(elm).attr('regexp');
			var prev_value = $(elm).attr('prev_value');
			$(elm).attr('prev_value', $(elm).val());
			var regexp = new RegExp('(' + search + ')(.+)(' + prev_value + ')');
			advance_options.val(advance_options.val().replace(regexp, '$1$2' + $(elm).val()));
		});
	}
}

$('#vstobjects').submit(function () {
	if ($('#basic-options').is(':visible')) {
		var advance_options = $('#advanced-options textarea');

		$('#vstobjects input[type=text]').each(function (i, elm) {
			var search = $(elm).attr('regexp');
			var prev_value = $(elm).attr('prev_value');
			$(elm).attr('prev_value', $(elm).val());
			var regexp = new RegExp('(' + search + ')(.+)(' + prev_value + ')');
			advance_options.val(advance_options.val().replace(regexp, '$1$2' + $(elm).val()));
		});
	}
});
