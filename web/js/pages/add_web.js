// Page entry point
// Trigger listeners
App.Listeners.DB.keypress_custom_folder();
App.Listeners.DB.change_custom_doc();

App.Actions.WEB.update_ftp_username_hint = function (elm, hint) {
	if (hint.trim() == '') {
		$(elm).parent().find('.hint').text('');
	}

	hint = hint.replace(/[^\w\d]/gi, '');

	$(elm).parent().find('.v-ftp-user').val(hint);
	$(elm)
		.parent()
		.find('.hint')
		.text(GLOBAL.FTP_USER_PREFIX + hint);
};

App.Listeners.WEB.keypress_ftp_username = function () {
	var ftp_user_inputs = $('.v-ftp-user');
	$.each(ftp_user_inputs, function (i, ref) {
		var ref = $(ref);
		var current_val = ref.val();
		if (current_val.trim() != '') {
			App.Actions.WEB.update_ftp_username_hint(ref, current_val);
		}

		ref.bind('keypress input', function (evt) {
			clearTimeout(window.frp_usr_tmt);
			window.frp_usr_tmt = setTimeout(function () {
				var elm = $(evt.target);
				App.Actions.WEB.update_ftp_username_hint(elm, $(elm).val());
			}, 100);
		});
	});
};

App.Listeners.WEB.keypress_domain_name = function () {
	$('#v_domain').bind('keypress input', function (evt) {
		clearTimeout(window.frp_usr_tmt);
		window.frp_usr_tmt = setTimeout(function () {
			//var elm = $(evt.target);
			//App.Actions.WEB.update_ftp_username_hint(elm, $(elm).val());
			var domain = $('.js-ftp-path-prefix').text(
				GLOBAL.FTP_USER_PREPATH + '/' + $('#v_domain').val()
			);
			$('#v-custom-doc-domain-main').text($('#v_domain').val());
			$('#v-custom-doc-domain-main').val($('#v_domain').val());
			App.Actions.WEB.update_custom_doc_root(13, 12);
		}, 100);
	});
};

App.Actions.WEB.toggle_letsencrypt = function (elm) {
	if ($(elm).prop('checked')) {
		$(
			'#ssltable textarea[name=v_ssl_crt],#ssltable textarea[name=v_ssl_key], #ssltable textarea[name=v_ssl_ca]'
		).attr('disabled', 'disabled');
		$('#generate-csr').hide();
		$('.lets-encrypt-note').show();
	} else {
		$(
			'#ssltable textarea[name=v_ssl_crt],#ssltable textarea[name=v_ssl_key], #ssltable textarea[name=v_ssl_ca]'
		).removeAttr('disabled');
		$('#generate-csr').show();
		$('.lets-encrypt-note').hide();
	}
};

//
// Page entry point
App.Listeners.WEB.keypress_ftp_username();
App.Listeners.WEB.keypress_ftp_path();
App.Listeners.WEB.keypress_domain_name();

$(function () {
	$('#v_domain').change(function () {
		var prefix = 'www.';
		if (document.getElementById('v_domain').value.split('.').length > 2) {
			document.getElementById('v_aliases').value = '';
		} else {
			document.getElementById('v_aliases').value =
				prefix + document.getElementById('v_domain').value;
		}
	});
	App.Actions.WEB.toggle_letsencrypt($('input[name=v_letsencrypt]'));

	$('select[name="v_stats"]').change(function (evt) {
		var select = $(evt.target);

		if (select.val() == 'none') {
			$('.stats-auth').hide();
		} else {
			$('.stats-auth').show();
		}
	});
});

function WEBrandom() {
	document.v_add_web.v_stats_password.value = randomString(16);
}

$('#vstobjects').on('submit', function (evt) {
	$('input[disabled]').each(function (i, elm) {
		$(elm).removeAttr('disabled');
	});
});
