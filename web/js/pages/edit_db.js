//
//
// Updates database username dynamically, showing its prefix
App.Actions.DB.update_db_username_hint = function (elm, hint) {
	if (hint.trim() == '') {
		$(elm).parent().find('.hint').text('');
	}
	$(elm)
		.parent()
		.find('.hint')
		.text(Alpine.store('globals').DB_USER_PREFIX + hint);
};

//
//
// Updates database name dynamically, showing its prefix
App.Actions.DB.update_db_databasename_hint = function (elm, hint) {
	if (hint.trim() == '') {
		$(elm).parent().find('.hint').text('');
	}
	$(elm)
		.parent()
		.find('.hint')
		.text(Alpine.store('globals').DB_DBNAME_PREFIX + hint);
};

//
// listener that triggers database user hint updating
App.Listeners.DB.keypress_db_username = function () {
	var ref = $('input[name="v_dbuser"]');
	var current_val = ref.val();
	if (current_val.trim() != '') {
		App.Actions.DB.update_db_username_hint(ref, current_val);
	}

	ref.bind('keypress input', function (evt) {
		clearTimeout(window.frp_usr_tmt);
		window.frp_usr_tmt = setTimeout(function () {
			var elm = $(evt.target);
			App.Actions.DB.update_db_username_hint(elm, $(elm).val());
		}, 100);
	});
};

//
// listener that triggers database name hint updating
App.Listeners.DB.keypress_db_databasename = function () {
	var ref = $('input[name="v_database"]');
	var current_val = ref.val();
	if (current_val.indexOf(Alpine.store('globals').DB_DBNAME_PREFIX) == 0) {
		current_val = current_val.slice(
			Alpine.store('globals').DB_DBNAME_PREFIX.length,
			current_val.length
		);
		ref.val(current_val);
	}
	if (current_val.trim() != '') {
		App.Actions.DB.update_db_username_hint(ref, current_val);
	}

	ref.bind('keypress input', function (evt) {
		clearTimeout(window.frp_dbn_tmt);
		window.frp_dbn_tmt = setTimeout(function () {
			var elm = $(evt.target);
			App.Actions.DB.update_db_databasename_hint(elm, $(elm).val());
		}, 100);
	});
};

App.Listeners.DB.keypress_v_password = function () {
	var ref = $('input[name="v_password"]');
	ref.bind('keypress input', function (evt) {
		clearTimeout(window.frp_usr_tmt);
		window.frp_usr_tmt = setTimeout(function () {
			VE.helpers.recalculatePasswordStrength(evt.target);
		}, 100);
	});
};

App.Listeners.DB.keypress_v_password();

//
// Page entry point
// Trigger listeners
App.Listeners.DB.keypress_db_username();
App.Listeners.DB.keypress_db_databasename();

applyRandomPassword = function (min_length = 16) {
	const passwordInput = document.querySelector('input[name=v_password]');
	if (passwordInput) {
		passwordInput.value = randomString(min_length);
		VE.helpers.recalculatePasswordStrength(passwordInput);
	}
};
