/*App.Validate.form = function(values){
    if(values.IP_ADDRESS == '') {
        return alert('Not correct ip');
    }
    
    return true;
}*/

App.Validate.Is = {
    ip: function(object) {
        var ip_regexp = new RegExp(/\b(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b/);
        return ip_regexp.test() ? false : App.i18n.getMessage('incorrect_ip');
    }
};



App.Validate.form = function(world, elm)
{    
    var form_valid = true;
    App.Env.FormError = [];
    $(elm).find('select, input, textarea').each(function(i, field)
    {
        if ($.inArray($(field).attr('name'), ['target', 'source', 'save']) != -1) {
            //return; // pass            
        }
        else {
        
            if ($(field).val().trim() == '') {
                App.Env.FormError.push($(field).attr('name') + ' is required');
                form_valid = false;
            }
        }
    });
    return form_valid;
}

App.Validate.displayFormErrors = function(world, elm)
{
    var errors_tpl = '';
    $(App.Env.FormError).each(function(i, error)
    {
        var tpl = App.Templates.get('error_elm', 'general');
        tpl.set(':ERROR', error);
        errors_tpl += tpl.finalize();
    });
    var ref = $('.form-error', elm);
    ref.removeClass('hidden');
    ref.html(errors_tpl);
}



