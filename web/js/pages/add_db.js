//
//
// Updates database username dynamically, showing its prefix
App.Actions.DB.update_db_username_hint = function(elm, hint) {
    if (hint.trim() == '') {
        $(elm).parent().find('.hint').html('');
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

App.Actions.DB.update_v_password = function (){
    var password = $('input[name="v_password"]').val();
    var min_small = new RegExp(/^(?=.*[a-z]).+$/);
    var min_cap = new RegExp(/^(?=.*[A-Z]).+$/);
    var min_num = new RegExp(/^(?=.*\d).+$/); 
    var min_length = 8;
    var score = 0;
    
    if(password.length >= min_length) { score = score + 1; }
    if(min_small.test(password)) { score = score + 1;}
    if(min_cap.test(password)) { score = score + 1;}
    if(min_num.test(password)) { score = score+ 1; }
    $('#meter').val(score);   
}

App.Listeners.DB.keypress_v_password = function() {
    var ref = $('input[name="v_password"]');
    ref.bind('keypress input', function(evt) {
        clearTimeout(window.frp_usr_tmt);
        window.frp_usr_tmt = setTimeout(function() {
            var elm = $(evt.target);
            App.Actions.DB.update_v_password(elm, $(elm).val());
        }, 100);
    });
}

App.Listeners.DB.keypress_v_password();

//
// Page entry point
// Trigger listeners
App.Listeners.DB.keypress_db_username();
App.Listeners.DB.keypress_db_databasename();

randomString = function(min_length = 16) {
    var chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz';
    var string_length = min_length;
    var randomstring = '';
    for (var i = 0; i < string_length; i++) {
        var rnum = Math.floor(Math.random() * chars.length);
        randomstring += chars.substr(rnum, 1);
    }
    var regex = new RegExp(/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\d)[a-zA-Z\d]{8,}$/);
    if(!regex.test(randomstring)){
        randomString();
    }else{
        $('input[name=v_password]').val(randomstring);
        App.Actions.DB.update_v_password();
    }    
}
