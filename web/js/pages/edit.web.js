App.Actions.WEB.update_ftp_username_hint = function(elm, hint) {
    if (hint.trim() == '') {
        $(elm).parent().find('.hint').html('');
    } 
    if (hint.indexOf(GLOBAL.FTP_USER_PREFIX) == 0) {
        hint = hint.slice(GLOBAL.FTP_USER_PREFIX.length, hint.length);
    }
    $(elm).parent().find('.hint').text(GLOBAL.FTP_USER_PREFIX + hint);
}

App.Listeners.WEB.keypress_ftp_username = function() {
    var ref = $('input[name="v_ftp_user"]');
    var current_val = ref.val();
    if (current_val.trim() != '') {
        App.Actions.DB.update_ftp_username_hint(ref, current_val);
    }
    
    ref.bind('keypress', function(evt) {
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
