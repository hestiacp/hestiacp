$(function() {
    $('#v_email').change(function() {
        if($('#v_email_notify').attr('checked')){
            document.getElementById('v_notify').value = document.getElementById('v_email').value;
        }
    });
    $('#v_email_notify').change(function() {
        if($('#v_email_notify').attr('checked')){
            document.getElementById('v_notify').value = document.getElementById('v_email').value;
        }else{
            document.getElementById('v_notify').value = '';
        }
    });    
});


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
        App.Actions.WEB.update_v_password();
    }    
}

App.Actions.WEB.update_v_password = function (){
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

App.Listeners.WEB.keypress_v_password = function() {
    var ref = $('input[name="v_password"]');
    ref.bind('keypress input', function(evt) {
        clearTimeout(window.frp_usr_tmt);
        window.frp_usr_tmt = setTimeout(function() {
            var elm = $(evt.target);
            App.Actions.WEB.update_v_password(elm, $(elm).val());
        }, 100);
    });
}

App.Listeners.WEB.keypress_v_password();