//
//
// Updates database username dynamically, showing its prefix
App.Actions.DB.update_db_username_hint = function(elm, hint) {
    if (hint.trim() == '') {
        $(elm).parent().find('.hint').text('');
    } 
    $(elm).parent().find('.hint').text(GLOBAL.DB_USER_PREFIX + hint);
}

//
//
// Updates database name dynamically, showing its prefix
App.Actions.DB.update_db_databasename_hint = function(elm, hint) {
    if (hint.trim() == '') {
        $(elm).parent().find('.hint').text('');
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
    if (current_val.indexOf(GLOBAL.DB_DBNAME_PREFIX) == 0) {
        current_val = current_val.slice(GLOBAL.DB_DBNAME_PREFIX.length, current_val.length);
        ref.val(current_val);
    }
    if (current_val.trim() != '') {
        App.Actions.DB.update_db_username_hint(ref, current_val);
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
    var shitty_but_secure_rng = function(min, max) {
        if (min < 0 || min > 0xFFFF) {
            throw new Error("minimum supported number is 0, this shitty generator can only make numbers between 0-65535 inclusive.");
        }
        if (max > 0xFFFF || max < 0) {
            throw new Error("max supported number is 65535, this shitty generator can only make numbers between 0-65535 inclusive.");
        }
        if (min > max) {
            throw new Error("dude min>max wtf");
        }
        // micro-optimization
        let randArr = (max > 255 ? new Uint16Array(1) : new Uint8Array(1));
        let ret;
        let attempts = 0;
        for(;;){
            crypto.getRandomValues(randArr);
            ret = randArr[0];
            if(ret >= min && ret <= max) {
                return ret;
            }
            ++attempts;
            if (attempts > 1000000) {
                // should basically never happen with max 0xFFFF/Uint16Array. 
                throw new Error("tried a million times, something is wrong");
            }
        }
    };
    for (var i = 0; i < string_length; i++) {
        randomstring += chars.substr(shitty_but_secure_rng(0, chars.length - 1), 1);
    }
    var regex = new RegExp(/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\d)[a-zA-Z\d]{8,}$/);
    if(!regex.test(randomstring)){
        randomString();
    }else{
        $('input[name=v_password]').val(randomstring);
        App.Actions.DB.update_v_password();
    }    
}

     