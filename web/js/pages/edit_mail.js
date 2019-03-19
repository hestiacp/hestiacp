App.Actions.WEB.toggle_letsencrypt = function(elm) {
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

function elementHideShow(elementToHideOrShow){
    var el = document.getElementById(elementToHideOrShow);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}