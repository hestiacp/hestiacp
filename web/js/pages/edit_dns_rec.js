//
//
// Updates database dns record dynamically, showing its full domain path
App.Actions.DB.update_dns_record_hint = function(elm, hint) {
    // clean hint
    if (hint.trim() == '') {
        $(elm).parent().find('.hint').html('');
    }

    // set domain name without rec in case of @ entries
    if (hint == '@') {
        hint = '';
    }

    // dont show pregix if domain name = rec value
    if (hint == GLOBAL.DNS_REC_PREFIX + '.') {
        hint = '';
    }

    // add dot at the end if needed
    if (hint != '' && hint.slice(-1) != '.') {
        hint += '.';
    }

    $(elm).parent().find('.hint').text(hint + GLOBAL.DNS_REC_PREFIX);
}

//
// listener that triggers dns record name hint updating
App.Listeners.DB.keypress_dns_rec_entry = function() {
    var ref = $('input[name="v_rec"]');
    var current_rec = ref.val();
    if (current_rec.trim() != '') {
        App.Actions.DB.update_dns_record_hint(ref, current_rec);
    }

    ref.bind('keypress input', function(evt) {
        clearTimeout(window.frp_usr_tmt);
        window.frp_usr_tmt = setTimeout(function() {
            var elm = $(evt.target);
            App.Actions.DB.update_dns_record_hint(elm, $(elm).val());
        }, 100);
    });
}

//
// Page entry point
// Trigger listeners
App.Listeners.DB.keypress_dns_rec_entry();
