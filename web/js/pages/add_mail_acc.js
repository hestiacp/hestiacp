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
$('form[name="v_quota"]').on('submit', function () {
	$('input:disabled').each(function (i, elm) {
		$(elm).attr('disabled', false);
		if (Alpine.store('globals').isUnlimitedValue($(elm).val())) {
			$(elm).val(Alpine.store('globals').UNLIM_VALUE);
		}
	});
});

applyRandomPassword = function (min_length = 16) {
	const randomPassword = randomString(min_length);
	const passwordInput = document.querySelector('.js-password-input');
	if (passwordInput) {
		passwordInput.value = randomPassword;
		VE.helpers.recalculatePasswordStrength(passwordInput);
		const passwordOutput = document.querySelector('.js-password-output');
		if (passwordOutput) {
			if (passwordInput.getAttribute('type') === 'text') {
				passwordOutput.textContent = randomPassword;
			} else {
				passwordOutput.textContent = Array(randomPassword.length + 1).join('*');
			}
		}
		generate_mail_credentials();
	}
};

generate_mail_credentials = function () {
	var div = $('.js-mail-info').clone();
	div.find('#mail_configuration').remove();
	var output = div.text();
	output = output.replace(/(?:\r\n|\r|\n|\t)/g, '|');
	output = output.replace(/ {2}/g, '');
	output = output.replace(/\|\|/g, '|');
	output = output.replace(/\|\|/g, '|');
	output = output.replace(/\|\|/g, '|');
	output = output.replace(/^\|+/g, '');
	output = output.replace(/\|$/, '');
	output = output.replace(/ $/, '');
	output = output.replace(/:\|/g, ': ');
	output = output.replace(/\|/g, '\n');
	$('.js-hidden-credentials').val(output);
};

$(document).ready(function () {
	$('.js-account-output').text($('input[name=v_account]').val());
	$('.js-password-output').text($('.js-password-input').val());
	generate_mail_credentials();

	$('input[name=v_account]').change(function () {
		$('.js-account-output').text($(this).val());
		generate_mail_credentials();
	});

	$('.js-password-input').change(function () {
		if ($('.js-password-input').attr('type') == 'text')
			$('.js-password-output').text($(this).val());
		else $('.js-password-output').text(Array($(this).val().length + 1).join('*'));
		generate_mail_credentials();
	});

	$('.toggle-psw-visibility-icon').click(function () {
		$('.js-password-output').text($('.js-password-input').val());
		generate_mail_credentials();
	});

	$('#mail_configuration').change(function (evt) {
		var opt = $(evt.target).find('option:selected');

		switch (opt.attr('v_type')) {
			case 'hostname':
				$('#td_imap_hostname').text(opt.attr('domain'));
				$('#td_smtp_hostname').text(opt.attr('domain'));
				break;
			case 'starttls':
				$('#td_imap_port').text('143');
				$('#td_imap_encryption').text('STARTTLS');
				$('#td_smtp_port').text('587');
				$('#td_smtp_encryption').text('STARTTLS');
				break;
			case 'ssl':
				$('#td_imap_port').text('993');
				$('#td_imap_encryption').text('SSL / TLS');
				$('#td_smtp_port').text('465');
				$('#td_smtp_encryption').text('SSL / TLS');
				break;
			case 'no_encryption':
				$('#td_imap_hostname').text(opt.attr('domain'));
				$('#td_smtp_hostname').text(opt.attr('domain'));

				$('#td_imap_port').text('143');
				$('#td_imap_encryption').text(opt.attr('no_encryption'));
				$('#td_smtp_port').text('25');
				$('#td_smtp_encryption').text(opt.attr('no_encryption'));
				break;
		}
		generate_mail_credentials();
	});
});
