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


$(document).ready(function(){
    $('.add-ns-button').click(function(){
        var n = $('input[name^=v_ns]').length;
        if(n < 8){
            var t = $($('input[name=v_ns1]').parents('tr')[0]).clone(true, true);
            t.find('input').attr({value:'', name:'v_ns'+(n+1)});
            t.find('span').show();
            $('tr.add-ns').before(t);
        }
        if( n == 7 ) {
            $('.add-ns').hide();
        }
    });

    $('.remove-ns').click(function(){
        $(this).parents('tr')[0].remove();
        $('input[name^=v_ns]').each(function(i, ns){
            $(ns).attr({name: 'v_ns'+(i+1)});
            i < 2 ? $(ns).parent().find('span').hide() : $(ns).parent().find('span').show();
        });
        $('.add-ns').show();
    });

    $('input[name^=v_ns]').each(function(i, ns){
        i < 2 ? $(ns).parent().find('span').hide() : $(ns).parent().find('span').show();
    });

});