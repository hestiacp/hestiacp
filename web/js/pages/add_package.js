App.Actions.PACKAGE.enable_unlimited = function(elm, source_elm) {
    $(elm).data('checked', true);
    $(elm).data('prev_value', $(elm).val()); // save prev value in order to restore if needed
    $(elm).val(App.Constants.UNLIM_TRANSLATED_VALUE);
    $(elm).attr('disabled', true);
    $(source_elm).css('opacity', '1');
}

App.Actions.PACKAGE.disable_unlimited = function(elm, source_elm) {
    $(elm).data('checked', false);
    if ($(elm).data('prev_value') && $(elm).data('prev_value').trim() != '') {
        var prev_value = $(elm).data('prev_value').trim();
        $(elm).val(prev_value);
        if (App.Helpers.isUnlimitedValue(prev_value)) {
            $(elm).val('0');
        }
    }
    else {
        if (App.Helpers.isUnlimitedValue($(elm).val())) {
            $(elm).val('0');
        }
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
        if (App.Helpers.isUnlimitedValue($(ref).val())) {
            App.Actions.PACKAGE.enable_unlimited(ref, elm);
        }
        else {
            $(ref).data('prev_value', $(ref).val());
            App.Actions.PACKAGE.disable_unlimited(ref, elm);
        }
    });
}

App.Helpers.isUnlimitedValue = function(value) {
    var value = value.trim();
    if (value == App.Constants.UNLIM_VALUE || value == App.Constants.UNLIM_TRANSLATED_VALUE) {
        return true;
    }

    return false;
}

//
// Page entry point
// Trigger listeners
App.Listeners.PACKAGE.init();
App.Listeners.PACKAGE.checkbox_unlimited_feature();
$('form[name="v_add_package"]').bind('submit', function(evt) {
    $('input:disabled').each(function(i, elm) {
        $(elm).attr('disabled', false);
        if (App.Helpers.isUnlimitedValue($(elm).val())) {
            $(elm).val(App.Constants.UNLIM_VALUE);
        }
    });
});


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
