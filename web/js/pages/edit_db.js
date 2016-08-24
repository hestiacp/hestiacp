//
//
// Updates database username dynamically, showing its prefix
App.Actions.DB.update_db_username_hint = function(elm, hint) {
    if (hint.trim() == '') {
        $(elm).parent().find('.hint').html('');
    } 
    // remove prefix from value in order to eliminate duplicates
    if (hint.indexOf(GLOBAL.DB_USER_PREFIX) == 0) {
        hint = hint.slice(GLOBAL.DB_USER_PREFIX.length, hint.length);
    }
    
    $(elm).parent().find('.hint').text(GLOBAL.DB_USER_PREFIX + hint);
}

//
//
// Updates database name dynamically, showing its prefix
App.Actions.DB.update_db_databasename_hint = function(elm, hint) {
    if (hint.trim() == '') {
        $(elm).parent().find('.hint').html('');
    } 
    // remove prefix from value in order to eliminate duplicates
    if (hint.indexOf(GLOBAL.DB_DBNAME_PREFIX) == 0) {
        hint = hint.slice(GLOBAL.DB_DBNAME_PREFIX.length, hint.length);
    }
    $(elm).parent().find('.hint').text(GLOBAL.DB_DBNAME_PREFIX + hint);
}

//
// listener that triggers database user hint updating
App.Listeners.DB.keypress_db_username = function() {
    var ref = $('input[name="v_dbuser"]');
    var current_val = ref.val();
    if (current_val.trim() != '') {
        App.Actions.DB.update_db_username_hint(ref, current_val);
    }
    
    ref.bind('keypress input', function(evt) {
        clearTimeout(window.frp_usr_tmt);
        window.frp_usr_tmt = setTimeout(function() {
            var elm = $(evt.target);
            App.Actions.DB.update_db_username_hint(elm, $(elm).val());
        }, 100);
    });
}

//
// listener that triggers database name hint updating
App.Listeners.DB.keypress_db_databasename = function() {
    var ref = $('input[name="v_database"]');
    var current_val = ref.val();
    if (current_val.trim() != '') {
        App.Actions.DB.update_db_databasename_hint(ref, current_val);
    }
    
    ref.bind('keypress input', function(evt) {
        clearTimeout(window.frp_dbn_tmt);
        window.frp_dbn_tmt = setTimeout(function() {
            var elm = $(evt.target);
            App.Actions.DB.update_db_databasename_hint(elm, $(elm).val());
        }, 100);
    });
}

//
// Page entry point
// Trigger listeners
App.Listeners.DB.keypress_db_username();
App.Listeners.DB.keypress_db_databasename();

randomString = function() {
    var chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz';
    var string_length = 10;
    var randomstring = '';
    for (var i = 0; i < string_length; i++) {
        var rnum = Math.floor(Math.random() * chars.length);
        randomstring += chars.substr(rnum, 1);
    }
    document.v_edit_db.v_password.value = randomstring;
}
     