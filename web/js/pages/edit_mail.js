document.getElementById('vstobjects').addEventListener('submit', function () {
	$('input[disabled]').each(function (i, elm) {
		var copy_elm = $(elm).clone(true);
		$(copy_elm).attr('type', 'hidden');
		$(copy_elm).removeAttr('disabled');
		$(elm).after(copy_elm);
	});
});
