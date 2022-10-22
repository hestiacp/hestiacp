App.Actions.WEB.update_custom_doc_root = function(elm, hint) {
    var prepath = $('input[name="v-custom-doc-root_prepath"]').val();
    var domain = $('select[name="v-custom-doc-domain"]').val();
    var folder = $('input[name="v-custom-doc-folder"]').val();

    $('.custom_docroot_hint').html(prepath+domain+'/public_html/'+folder);
}
App.Listeners.DB.keypress_custom_folder = function() {
    var ref = $('input[name="v-custom-doc-folder"]');
    var current_rec = ref.val();
    App.Actions.WEB.update_custom_doc_root(ref, current_rec);


    ref.bind('keypress input', function(evt) {
        clearTimeout(window.frp_usr_tmt);
        window.frp_usr_tmt = setTimeout(function() {
            var elm = $(evt.target);
            App.Actions.WEB.update_custom_doc_root(elm, $(elm).val());
        });
    });
}

App.Listeners.DB.change_custom_doc = function() {
    var ref = $('select[name="v-custom-doc-domain"]');
    var current_rec = ref.val();
    ref.bind('change select', function(evt) {
        clearTimeout(window.frp_usr_tmt);
        window.frp_usr_tmt = setTimeout(function() {
            var elm = $(evt.target);
            App.Actions.WEB.update_custom_doc_root(elm, $(elm).val());
        });
    });
}

// Page entry point
// Trigger listeners
App.Listeners.DB.keypress_custom_folder();
App.Listeners.DB.change_custom_doc();

App.Actions.WEB.update_ftp_username_hint = function(elm, hint) {
    if (hint.trim() == '') {
        $(elm).parent().find('.hint').html('');
    }

    hint = hint.replace(/[^\w\d]/gi, '');

    $(elm).parent().find('.v-ftp-user').val(hint);
    $(elm).parent().find('.hint').text(GLOBAL.FTP_USER_PREFIX + hint);
}

App.Listeners.WEB.keypress_ftp_username = function() {
    var ftp_user_inputs = $('.v-ftp-user');
    $.each(ftp_user_inputs, function(i, ref) {
        var ref = $(ref);
        var current_val = ref.val();
        if (current_val.trim() != '') {
            App.Actions.WEB.update_ftp_username_hint(ref, current_val);
        }

        ref.bind('keypress input', function(evt) {
            clearTimeout(window.frp_usr_tmt);
            window.frp_usr_tmt = setTimeout(function() {
                var elm = $(evt.target);
                App.Actions.WEB.update_ftp_username_hint(elm, $(elm).val());
            }, 100);
        });
    });
}

//
//

App.Actions.WEB.update_ftp_path_hint = function(elm, hint) {
    if (hint.trim() == '') {
        $(elm).parent().find('.v-ftp-path-hint').html('');
    }

    if (hint[0] != '/') {
        hint = '/' + hint;
    }

    hint = hint.replace(/\/(\/+)/g, '/');

    $(elm).parent().find('.v-ftp-path-hint').text(hint);
}

App.Listeners.WEB.keypress_ftp_path = function() {
    var ftp_path_inputs = $('.v-ftp-path');
    $.each(ftp_path_inputs, function(i, ref) {
        var ref = $(ref);
        var current_val = ref.val();
        if (current_val.trim() != '') {
            App.Actions.WEB.update_ftp_path_hint(ref, current_val);
        }

        ref.bind('keypress input', function(evt) {
            clearTimeout(window.frp_usr_tmt);
            window.frp_usr_tmt = setTimeout(function() {
                var elm = $(evt.target);
                App.Actions.WEB.update_ftp_path_hint(elm, $(elm).val());
            }, 100);
        });
    });
}

//
//
App.Actions.WEB.add_ftp_user_form = function() {
    var ref = $('#templates').find('.js-ftp-account-nrm').clone(true);
    var index = $('.app-form .js-ftp-account').length + 1;

    ref.find('input').each(function(i, elm) {
        var name = $(elm).attr('name');
        var id = $(elm).attr('id');
        $(elm).attr('name', name.replace('%INDEX%', index));
        if (id) {
          $(elm).attr('id', id.replace('%INDEX%', index));
        }
    });

    ref.find('input').prev('label').each(function(i, elm) {
        var for_attr = $(elm).attr('for');
        $(elm).attr('for', for_attr.replace('%INDEX%', index));
    });

    ref.find('.ftp-user-number').text(index);

    $('#ftp_users').append(ref);

    var index = 1;
    $('.app-form .ftp-user-number:visible').each(function(i, o) {
        $(o).text(index);
        index += 1;
    });
}

App.Actions.WEB.remove_ftp_user = function(elm) {
    var ref = $(elm).parents('.js-ftp-account');
    ref.find('.v-ftp-user-deleted').val('1');
    if (ref.find('.v-ftp-user-is-new').val() == 1) {
        ref.remove();
        return true;
    }
    ref.removeClass('js-ftp-account-nrm');
    ref.hide();

    var index = 1;
    $('.app-form .ftp-user-number:visible').each(function(i, o) {
        $(o).text(index);
        index += 1;
    });

    if ($('.js-ftp-account-nrm:visible').length == 0) {
        $('.add-new-ftp-user-button').hide();
        $('input[name="v_ftp"]').prop('checked', false);
    }
}

App.Actions.WEB.toggle_additional_ftp_accounts = function(elm) {
    if ($(elm).prop('checked')) {
        $('.js-ftp-account-nrm, .v-add-new-user, .add-new-ftp-user-button').show();
        $('.js-ftp-account-nrm').each(function(i, elm) {
            var login = $(elm).find('.v-ftp-user');
            if (login.val().trim() != '') {
                $(elm).find('.v-ftp-user-deleted').val(0);
            }
        });
    }
    else {
        $('.js-ftp-account-nrm, .v-add-new-user, .add-new-ftp-user-button').hide();
        $('.js-ftp-account-nrm').each(function(i, elm) {
            var login = $(elm).find('.v-ftp-user');
            if (login.val().trim() != '') {
                $(elm).find('.v-ftp-user-deleted').val(1);
            }
        });
    }
}

App.Actions.WEB.toggle_ssl = function (elm){
    elementHideShow('ssltable');
    if($('#ssl_crt').val().length > 0 || $('#ssl_hsts').prop('checked') || $('#letsencrypt').prop('checked')){
        return false;
    }
    $('#v_ssl_forcessl').prop('checked', true);
}

App.Actions.WEB.toggle_letsencrypt = function(elm) {
    if ($(elm).prop('checked')) {
        $('#ssl-details').hide();
        $('#ssltable textarea[name=v_ssl_crt],#ssltable textarea[name=v_ssl_key], #ssltable textarea[name=v_ssl_ca]').attr('disabled', 'disabled');
        $('#generate-csr').hide();
	if(!$('.lets-encrypt-note').hasClass('enabled')){
	    $('.lets-encrypt-note').show();
	}
    }
    else {
        $('#ssltable textarea[name=v_ssl_crt],#ssltable textarea[name=v_ssl_key], #ssltable textarea[name=v_ssl_ca]').removeAttr('disabled');
        $('#generate-csr').show();
        $('#ssl-details').show();
	$('.lets-encrypt-note').hide();
    }
}

App.Actions.WEB.randomPasswordGenerated = function(elm) {
    return App.Actions.WEB.passwordChanged(elm);
}

App.Actions.WEB.passwordChanged = function(elm) {
    var ref = $(elm).parents('.js-ftp-account');
    if (ref.find('.vst-email-alert-on-psw').length == 0) {
        var inp_name = ref.find('.v-ftp-user-is-new').prop('name');
        inp_name = inp_name.replace('is_new', 'v_ftp_email');
        ref.find('div:last').after('<div class="u-pl50 u-mb10">\
                                      <label for="' + inp_name + '" class="form-label">Send FTP credentials to email</label>\
                                      <input type="email" class="form-control vst-email-alert-on-psw" value="" name="' + inp_name + '" id="' + inp_name + '">\
                                   </div>');
    }
}

//
// Page entry point
App.Listeners.WEB.keypress_ftp_username();
App.Listeners.WEB.keypress_ftp_path();


$(function() {
    $('.v-ftp-user-psw').on('keypress', function (evt) {
        var elm = $(evt.target);
        App.Actions.WEB.passwordChanged(elm);
    });
    App.Actions.WEB.toggle_letsencrypt($('input[name=v_letsencrypt]'));

    $('select[name="v_stats"]').change(function(evt){
        var select = $(evt.target);

        if(select.val() == 'none'){
            $('.stats-auth').hide();
        } else {
            $('.stats-auth').show();
        }
    });

    $('select[name="v_nginx_cache"]').change(function(evt){
        var select = $(evt.target);

        if(select.val() != 'yes'){
            $('#v-clear-cache').hide();
            $('#v_nginx_cache_length').hide();
        } else {
            $('#v-clear-cache').show();
            $('#v_nginx_cache_length').show();
        }
    });

    $('select[name="v_proxy_template"]').change(function(evt){
        var select = $(evt.target);

        if(select.val() != 'caching'){
            const re = new RegExp('caching-');
            if(re.test(select.val())){
                $('#v-clear-cache').show();
            }else{
                $('#v-clear-cache').hide();
            }
        } else {
            $('#v-clear-cache').show();
        }
    });

    $('#vstobjects').on('submit', function(evt) {
        $('input[disabled]').each(function(i, elm) {
            var copy_elm = $(elm).clone(true);
            $(copy_elm).attr('type', 'hidden');
            $(copy_elm).removeAttr('disabled');
            $(elm).after(copy_elm);
        });
    });
});

function WEBrandom() {
    document.v_edit_web.v_stats_password.value = randomString2(16);
}

function FTPrandom(elm) {
    $(elm).parents('.js-ftp-account').find('.v-ftp-user-psw').val(randomString2(16));
    App.Actions.WEB.randomPasswordGenerated && App.Actions.WEB.randomPasswordGenerated(elm);
}

function elementHideShow(element){
    if ( document.getElementById(element)){
        var el = document.getElementById(element);
        el.style.display = el.style.display === 'none' ? 'block' : 'none';
    }
}

$('.v-redirect-custom-value').change( function(){

    if(this.value == "custom"){
        $('#custom_redirect').show();
    }else{
        $('#custom_redirect').hide();
    }
})
