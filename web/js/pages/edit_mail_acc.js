App.Actions.MAIL_ACC.enable_unlimited = function (elm, source_elm) {
	$(elm).data('checked', true);
	$(elm).data('prev_value', $(elm).val()); // save prev value in order to restore if needed
	$(elm).val(App.Constants.UNLIM_TRANSLATED_VALUE);
	$(elm).attr('disabled', true);
	$(source_elm).css('opacity', '1');
};

App.Actions.MAIL_ACC.disable_unlimited = function (elm, source_elm) {
	$(elm).data('checked', false);
	if ($(elm).data('prev_value') && $(elm).data('prev_value').trim() != '') {
		var prev_value = $(elm).data('prev_value').trim();
		$(elm).val(prev_value);
		if (App.Helpers.isUnlimitedValue(prev_value)) {
			$(elm).val('0');
		}
	} else {
		if (App.Helpers.isUnlimitedValue($(elm).val())) {
			$(elm).val('0');
		}
	}
	$(elm).attr('disabled', false);
	$(source_elm).css('opacity', '0.5');
};

//
App.Actions.MAIL_ACC.toggle_unlimited_feature = function (evt) {
	var elm = $(evt.target);
	var ref = elm.prev('.form-control');
	if (!$(ref).data('checked')) {
		App.Actions.MAIL_ACC.enable_unlimited(ref, elm);
	} else {
		App.Actions.MAIL_ACC.disable_unlimited(ref, elm);
	}
};

App.Listeners.MAIL_ACC.checkbox_unlimited_feature = function () {
	$('.unlim-trigger').on('click', App.Actions.MAIL_ACC.toggle_unlimited_feature);
};

App.Listeners.MAIL_ACC.init = function () {
	$('.unlim-trigger').each(function (i, elm) {
		var ref = $(elm).prev('.form-control');
		if (App.Helpers.isUnlimitedValue($(ref).val())) {
			App.Actions.MAIL_ACC.enable_unlimited(ref, elm);
		} else {
			$(ref).data('prev_value', $(ref).val());
			App.Actions.MAIL_ACC.disable_unlimited(ref, elm);
		}
	});
};

App.Listeners.MAIL_ACC.init();
App.Listeners.MAIL_ACC.checkbox_unlimited_feature();

App.Actions.MAIL_ACC.update_password_meter = function () {
	var password = $('input[name="v_password"]').val();
	var min_small = new RegExp(/^(?=.*[a-z]).+$/);
	var min_cap = new RegExp(/^(?=.*[A-Z]).+$/);
	var min_num = new RegExp(/^(?=.*\d).+$/);
	var min_length = 8;
	var score = 0;

	if (password.length >= min_length) {
		score = score + 1;
	}
	if (min_small.test(password)) {
		score = score + 1;
	}
	if (min_cap.test(password)) {
		score = score + 1;
	}
	if (min_num.test(password)) {
		score = score + 1;
	}
	$('.password-meter').val(score);
};

App.Listeners.MAIL_ACC.keypress_v_password = function () {
	var ref = $('input[name="v_password"]');
	ref.bind('keypress input', function (evt) {
		clearTimeout(window.frp_usr_tmt);
		window.frp_usr_tmt = setTimeout(function () {
			var elm = $(evt.target);
			App.Actions.MAIL_ACC.update_password_meter(elm, $(elm).val());
		}, 100);
	});
};

$('#v_blackhole').on('click', function (evt) {
	if ($('#v_blackhole').is(':checked')) {
		$('#v_fwd').prop('disabled', true);
		$('#v_fwd_for').prop('checked', true);
		$('#id_fwd_for').hide();
	} else {
		$('#v_fwd').prop('disabled', false);
		$('#id_fwd_for').show();
	}
});

App.Listeners.MAIL_ACC.keypress_v_password();

applyRandomString = function (min_length = 16) {
	var randomString = randomString(min_length);
	$('input[name=v_password]').val(randomString);
	if ($('input[name=v_password]').attr('type') == 'text')
		$('.js-password-output').text(randomString);
	else $('.js-password-output').text(Array(randomString.length + 1).join('*'));
	App.Actions.MAIL_ACC.update_password_meter();
	generate_mail_credentials();
};

generate_mail_credentials = function () {
	var div = $('.mail-infoblock').clone();
	div.find('#mail_configuration').remove();
	var pass = div.find('.js-password-output').text();
	if (pass == '') div.find('.js-password-output').text(' ');
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
	$('.js-password-output').text($('input[name=v_password]').val());
	generate_mail_credentials();

	$('input[name=v_account]').change(function () {
		$('.js-account-output').text($(this).val());
		generate_mail_credentials();
	});

	$('input[name=v_password]').change(function () {
		if ($('input[name=v_password]').attr('type') == 'text')
			$('.js-password-output').text($(this).val());
		else $('.js-password-output').text(Array($(this).val().length + 1).join('*'));
		generate_mail_credentials();
	});

	$('.toggle-psw-visibility-icon').click(function () {
		if ($('input[name=v_password]').attr('type') == 'text')
			$('.js-password-output').text($('input[name=v_password]').val());
		else
			$('.js-password-output').text(Array($('input[name=v_password]').val().length + 1).join('*'));
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
