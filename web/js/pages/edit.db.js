App.Actions.DB.update_db_username_hint = function(elm, hint) {
	if (hint.trim() == '') {
		$(elm).parent().find('.hint').html('');
	} 
	if (hint.indexOf(GLOBAL.DB_USER_PREFIX) == 0) {
		hint = hint.slice(GLOBAL.DB_USER_PREFIX.length, hint.length);
	}
	$(elm).parent().find('.hint').html(GLOBAL.DB_USER_PREFIX + hint);
}

App.Actions.DB.update_db_databasename_hint = function(elm, hint) {
	if (hint.trim() == '') {
		$(elm).parent().find('.hint').html('');
	} 
	if (hint.indexOf(GLOBAL.DB_DBNAME_PREFIX) == 0) {
		hint = hint.slice(GLOBAL.DB_DBNAME_PREFIX.length, hint.length);
	}
	$(elm).parent().find('.hint').html(GLOBAL.DB_DBNAME_PREFIX + hint);
}

App.Listeners.DB.keypress_db_username = function() {
	$('input[name="v_dbuser"]').bind('keypress', function(evt) {
		clearTimeout(window.frp_usr_tmt);
		window.frp_usr_tmt = setTimeout(function() {
			var elm = $(evt.target);
			App.Actions.DB.update_db_username_hint(elm, $(elm).val());
		}, 100);
	});
}

App.Listeners.DB.keypress_db_databasename = function() {
	$('input[name="v_database"]').bind('keypress', function(evt) {
		clearTimeout(window.frp_dbn_tmt);
		window.frp_dbn_tmt = setTimeout(function() {
			var elm = $(evt.target);
			App.Actions.DB.update_db_databasename_hint(elm, $(elm).val());
		}, 100);
	});
}

//
// Page entry point
App.Listeners.DB.keypress_db_username();
App.Listeners.DB.keypress_db_databasename();
