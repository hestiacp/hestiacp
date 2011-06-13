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



App.Validate.form = function(values, form_ref){    
    // TODO: validate it!
    return true;
    var errors = [];
    $.each(values, function(key) {
        var value = values[key];
        /*if ('undefined' != typeof App.Validate.Is[key] ) {            
            if(var error = App.Validate.Is[key](value)) {
                errors[erros.length++] = error;
            }
        }*/
    });    
}



