$(function () {
	$('#v_email').change(function () {
		if ($('#v_email_notify').prop('checked')) {
			document.getElementById('v_notify').value = document.getElementById('v_email').value;
		}
	});
	$('#v_email_notify').change(function () {
		if ($('#v_email_notify').prop('checked')) {
			document.getElementById('v_notify').value = document.getElementById('v_email').value;
		} else {
			document.getElementById('v_notify').value = '';
		}
	});
});
