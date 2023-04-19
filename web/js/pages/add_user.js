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

applyRandomPassword = function (min_length = 16) {
	const passwordInput = document.querySelector('.js-password-input');
	if (passwordInput) {
		passwordInput.value = randomString(min_length);
		VE.helpers.recalculatePasswordStrength(passwordInput);
	}
};
