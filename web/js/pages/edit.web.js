App.Actions.WEB.update_ftp_username_hint = function(elm, hint) {
	if (hint.trim() == '') {
		$(elm).parent().find('.ftp_username_hint').html('');
	} 
	if (hint.indexOf(GLOBAL.FTP_USER_PREFIX) == 0) {
		hint = hint.slice(GLOBAL.FTP_USER_PREFIX.length, hint.length);
	}
	$(elm).parent().find('.ftp_username_hint').html(GLOBAL.FTP_USER_PREFIX + hint);
}

App.Listeners.WEB.keypress_ftp_username = function() {
	$('input[name="v_ftp_user"]').bind('keypress', function(evt) {
		clearTimeout(window.frp_usr_tmt);
		window.frp_usr_tmt = setTimeout(function() {
			var elm = $(evt.target);
			App.Actions.WEB.update_ftp_username_hint(elm, $(elm).val());
		}, 100);
	});
}

//
// Page entry point
App.Listeners.WEB.keypress_ftp_username();
