App.Actions.WEB.update_custom_doc_root = function () {
	var prepath = $('input[name="v-custom-doc-root_prepath"]').val();
	var domain = $('select[name="v-custom-doc-domain"]').val();
	var folder = $('input[name="v-custom-doc-folder"]').val();

	$('.js-custom-docroot-hint').html(prepath + domain + '/public_html/' + folder);
};
App.Listeners.DB.keypress_custom_folder = function () {
	var ref = $('input[name="v-custom-doc-folder"]');
	var current_rec = ref.val();
	App.Actions.WEB.update_custom_doc_root(ref, current_rec);

	ref.bind('keypress input', function (evt) {
		clearTimeout(window.frpUserTimeout);
		window.frpUserTimeout = setTimeout(function () {
			var elm = $(evt.target);
			App.Actions.WEB.update_custom_doc_root(elm, $(elm).val());
		});
	});
};

App.Listeners.DB.change_custom_doc = function () {
	var ref = $('select[name="v-custom-doc-domain"]');
	// var current_rec = ref.val();

	ref.bind('change select', function (evt) {
		clearTimeout(window.frpUserTimeout);
		window.frpUserTimeout = setTimeout(function () {
			var elm = $(evt.target);
			App.Actions.WEB.update_custom_doc_root(elm, $(elm).val());
		});
	});
};

// Page entry point
// Trigger listeners
App.Listeners.DB.keypress_custom_folder();
App.Listeners.DB.change_custom_doc();

App.Actions.WEB.update_ftp_username_hint = function (elm, hint) {
	if (hint.trim() == '') {
		$(elm).parent().find('.hint').html('');
	}

	hint = hint.replace(/[^\w\d]/gi, '');

	$(elm).parent().find('.js-ftp-user').val(hint);
	$(elm)
		.parent()
		.find('.hint')
		.text(Alpine.store('globals').USER_PREFIX + hint);
};

App.Listeners.WEB.keypress_ftp_username = function () {
	var ftp_user_inputs = $('.js-ftp-user');
	$.each(ftp_user_inputs, function (i, ref) {
		var $ref = $(ref);
		var current_val = $ref.val();
		if (current_val.trim() != '') {
			App.Actions.WEB.update_ftp_username_hint($ref, current_val);
		}

		$ref.bind('keypress input', function (evt) {
			clearTimeout(window.frpUserTimeout);
			window.frpUserTimeout = setTimeout(function () {
				var elm = $(evt.target);
				App.Actions.WEB.update_ftp_username_hint(elm, $(elm).val());
			}, 100);
		});
	});
};

//
//

App.Actions.WEB.update_ftp_path_hint = function (elm, hint) {
	if (hint.trim() == '') {
		$(elm).parent().find('.js-ftp-path-hint').html('');
	}

	if (hint[0] != '/') {
		hint = '/' + hint;
	}

	hint = hint.replace(/\/(\/+)/g, '/');

	$(elm).parent().find('.js-ftp-path-hint').text(hint);
};

App.Listeners.WEB.keypress_ftp_path = function () {
	var ftp_path_inputs = $('.js-ftp-path');
	$.each(ftp_path_inputs, function (i, ref) {
		var $ref = $(ref);
		var current_val = $ref.val();
		if (current_val.trim() != '') {
			App.Actions.WEB.update_ftp_path_hint($ref, current_val);
		}

		$ref.bind('keypress input', function (evt) {
			clearTimeout(window.frpUserTimeout);
			window.frpUserTimeout = setTimeout(function () {
				var elm = $(evt.target);
				App.Actions.WEB.update_ftp_path_hint(elm, $(elm).val());
			}, 100);
		});
	});
};

//
//
App.Actions.WEB.add_ftp_user_form = function () {
	const template = $('#templates').find('.js-ftp-account-nrm').clone(true);
	const ftpAccounts = $('.form-container .js-ftp-account');
	const newIndex = ftpAccounts.length + 1;

	template.find('input').each((_, elm) => {
		const $elm = $(elm);
		const name = $elm.attr('name');
		const id = $elm.attr('id');
		$elm.attr('name', name.replace('%INDEX%', newIndex));
		if (id) {
			$elm.attr('id', id.replace('%INDEX%', newIndex));
		}
	});

	template
		.find('input')
		.prev('label')
		.each((_, elm) => {
			const $elm = $(elm);
			const forAttr = $elm.attr('for');
			$elm.attr('for', forAttr.replace('%INDEX%', newIndex));
		});

	template.find('.js-ftp-user-number').text(newIndex);
	$('#ftp_users').append(template);

	let counter = 1;
	$('.form-container .js-ftp-user-number:visible').each((_, o) => {
		$(o).text(counter);
		counter += 1;
	});
};

App.Actions.WEB.remove_ftp_user = function (elm) {
	var ref = $(elm).parents('.js-ftp-account');
	ref.find('.js-ftp-user-deleted').val('1');
	if (ref.find('.js-ftp-user-is-new').val() == 1) {
		ref.remove();
		return true;
	}
	ref.removeClass('js-ftp-account-nrm');
	ref.hide();

	var index = 1;
	$('.form-container .js-ftp-user-number:visible').each(function (i, o) {
		$(o).text(index);
		index += 1;
	});

	if ($('.js-ftp-account-nrm:visible').length == 0) {
		$('.js-add-new-ftp-user-button').hide();
		$('input[name="v_ftp"]').prop('checked', false);
	}
};

App.Actions.WEB.toggle_additional_ftp_accounts = function (elm) {
	if ($(elm).prop('checked')) {
		$('.js-ftp-account-nrm, .v-add-new-user, .js-add-new-ftp-user-button').show();
		$('.js-ftp-account-nrm').each(function (i, elm) {
			var login = $(elm).find('.js-ftp-user');
			if (login.val().trim() != '') {
				$(elm).find('.js-ftp-user-deleted').val(0);
			}
		});
	} else {
		$('.js-ftp-account-nrm, .v-add-new-user, .js-add-new-ftp-user-button').hide();
		$('.js-ftp-account-nrm').each(function (i, elm) {
			var login = $(elm).find('.js-ftp-user');
			if (login.val().trim() != '') {
				$(elm).find('.js-ftp-user-deleted').val(1);
			}
		});
	}
};

App.Actions.WEB.randomPasswordGenerated = function (elm) {
	return App.Actions.WEB.passwordChanged(elm);
};

App.Actions.WEB.passwordChanged = function (elm) {
	var ref = $(elm).parents('.js-ftp-account');
	if (ref.find('.js-email-alert-on-psw').length == 0) {
		var inp_name = ref.find('.js-ftp-user-is-new').prop('name');
		inp_name = inp_name.replace('is_new', 'v_ftp_email');
		ref.find('div:last').after(
			`<div class="u-pl30 u-mb10">
				<label for="${inp_name}" class="form-label">
					Send FTP credentials to email
				</label>
				<input type="email" class="form-control js-email-alert-on-psw"
					value="" name="${inp_name}" id="${inp_name}">
			</div>`
		);
	}
};

//
// Page entry point
App.Listeners.WEB.keypress_ftp_username();
App.Listeners.WEB.keypress_ftp_path();

$(function () {
	$('.js-ftp-user-psw').on('keypress', function (evt) {
		var elm = $(evt.target);
		App.Actions.WEB.passwordChanged(elm);
	});
	$('input[name=v_letsencrypt]').change(function (evt) {
		var input = $(evt.target);
		if (input.prop('checked')) {
			$('#ssl-details').hide();
		} else {
			$('#ssl-details').show();
		}
	});

	$('select[name="v_stats"]').change(function (evt) {
		var select = $(evt.target);

		if (select.val() == 'none') {
			$('.stats-auth').hide();
		} else {
			$('.stats-auth').show();
		}
	});

	$('select[name="v_nginx_cache"]').change(function (evt) {
		var select = $(evt.target);

		if (select.val() != 'yes') {
			$('#v-clear-cache').hide();
			$('#v_nginx_cache_length').hide();
		} else {
			$('#v-clear-cache').show();
			$('#v_nginx_cache_length').show();
		}
	});

	$('select[name="v_proxy_template"]').change(function (evt) {
		var select = $(evt.target);

		if (select.val() != 'caching') {
			const re = new RegExp('caching-');
			if (re.test(select.val())) {
				$('#v-clear-cache').show();
			} else {
				$('#v-clear-cache').hide();
			}
		} else {
			$('#v-clear-cache').show();
		}
	});
});

// eslint-disable-next-line @typescript-eslint/no-unused-vars
function FTPrandom(elm) {
	$(elm).parents('.js-ftp-account').find('.js-ftp-user-psw').val(Hestia.helpers.randomPassword());
	App.Actions.WEB.randomPasswordGenerated && App.Actions.WEB.randomPasswordGenerated(elm);
}

$('.js-redirect-custom-value').change(function () {
	if (this.value == 'custom') {
		$('#custom_redirect').show();
	} else {
		$('#custom_redirect').hide();
	}
});
