$('#v_blackhole').on('click', function () {
	if ($('#v_blackhole').is(':checked')) {
		$('#v_fwd').prop('disabled', true);
		$('#v_fwd_for').prop('checked', true);
		$('#id_fwd_for').hide();
	} else {
		$('#v_fwd').prop('disabled', false);
		$('#id_fwd_for').show();
	}
});

VE.helpers.monitorAndUpdate('.js-account-input', '.js-account-output');
VE.helpers.monitorAndUpdate('.js-password-input', '.js-password-output');
