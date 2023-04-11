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
	const passwordInput = document.querySelector('input[name=v_password]');
	if (passwordInput) {
		passwordInput.value = randomString(min_length);
		VE.helpers.recalculatePasswordStrength(passwordInput);
	}
};

App.Listeners.WEB.keypress_v_password = function () {
	var ref = $('input[name="v_password"]');
	ref.bind('keypress input', function (evt) {
		clearTimeout(window.frp_usr_tmt);
		window.frp_usr_tmt = setTimeout(function () {
			VE.helpers.recalculatePasswordStrength(evt.target);
		}, 100);
	});
};

App.Listeners.WEB.keypress_v_password();
