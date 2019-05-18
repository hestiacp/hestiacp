App.Actions.MAIL.toggle_letsencrypt = function(elm) {
    if ($(elm).attr('checked')) {
        $('#ssltable textarea[name=v_ssl_crt],#ssltable textarea[name=v_ssl_key], #ssltable textarea[name=v_ssl_ca]').attr('disabled', 'disabled');
        $('#generate-csr').hide();
	if(!$('.lets-encrypt-note').hasClass('enabled')){
	    $('.lets-encrypt-note').show();
	}
    }
    else {
        $('#ssltable textarea[name=v_ssl_crt],#ssltable textarea[name=v_ssl_key], #ssltable textarea[name=v_ssl_ca]').removeAttr('disabled');
        $('#generate-csr').show();
	$('.lets-encrypt-note').hide();
    }
}

//
// Page entry point
$(function() {

    App.Actions.MAIL.toggle_letsencrypt($('input[name=v_letsencrypt]'));

});

function elementHideShow(elementToHideOrShow){
    var el = document.getElementById(elementToHideOrShow);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

$('#vstobjects').on('submit', function(evt) {
    $('input[disabled]').each(function(i, elm) {
        var copy_elm = $(elm).clone(true);
        $(copy_elm).attr('type', 'hidden');
        $(copy_elm).removeAttr('disabled');
        $(elm).after(copy_elm);
    });
});
