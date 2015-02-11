App.Actions.PACKAGE.enable_unlimited = function(elm, source_elm) {
    $(elm).data('checked', true);
    $(elm).data('prev_value', $(elm).val()); // save prev value in order to restore if needed
    $(elm).val(App.Constants.UNLIM_VALUE);
    $(elm).attr('disabled', true);
    $(source_elm).css('opacity', '1');
}

App.Actions.PACKAGE.disable_unlimited = function(elm, source_elm) {
    $(elm).data('checked', false);
    if ($(elm).data('prev_value') && $(elm).data('prev_value').trim() != '') {
        var prev_value = $(elm).data('prev_value').trim();
        $(elm).val(prev_value);
    }
    else {
        $(elm).val('0');
    }
    $(elm).attr('disabled', false);
    $(source_elm).css('opacity', '0.5');
}

// 
App.Actions.PACKAGE.toggle_unlimited_feature = function(evt) {
    var elm = $(evt.target);
    var ref = elm.prev('.vst-input');
    if (!$(ref).data('checked')) {
        App.Actions.PACKAGE.enable_unlimited(ref, elm);
    }
    else {
        App.Actions.PACKAGE.disable_unlimited(ref, elm);
    }
}

App.Listeners.PACKAGE.checkbox_unlimited_feature = function() {
    $('.unlim-trigger').on('click', App.Actions.PACKAGE.toggle_unlimited_feature);
}

App.Listeners.PACKAGE.init = function() {
    $('.unlim-trigger').each(function(i, elm) {
        var ref = $(elm).prev('.vst-input');
        if ($(ref).val().trim() == App.Constants.UNLIM_VALUE || $(ref).val().trim() == App.Constants.UNLIM_TRANSLATED_VALUE) {
            $(ref).val('0');
            App.Actions.PACKAGE.enable_unlimited(ref, elm);
            //$(elm).attr('checked', true);
        }
        else {
            App.Actions.PACKAGE.disable_unlimited(ref, elm);
        }
    });
}

//
// Page entry point
// Trigger listeners
App.Listeners.PACKAGE.init();
App.Listeners.PACKAGE.checkbox_unlimited_feature();
$('form[name="v_edit_package"]').bind('submit', function(evt) {
    $('input:disabled').each(function(i, elm) {
        $(elm).attr('disabled', false);
    });
});


