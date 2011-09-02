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

App.Validate.getFieldName = function(elm)
{
    fb.log(elm);
    fb.warn($(elm).prev('label').text());
    return ['<strong>', $(elm).prev('label').text(), '</strong>'].join('');
}

App.Validate.Rule = {
    'required' : function(elm) {
        if ($(elm).val().trim() == '') {
            return {VALID: false, ERROR: App.Validate.getFieldName(elm) + ' is required'};
        }
        return {VALID: true};
    },
    'no-spaces': function(elm) {
        if ($(elm).val().search(/\s/) != -1) {
            return {VALID: false, ERROR: App.Validate.getFieldName(elm) + ' cannot contain spaces'};
        }
        return {VALID: true};
    },
    'abc':      function(elm) {
        if ($(elm).val().search(/[^a-zA-Z]+/) != -1) {
            return {VALID: false, ERROR: App.Validate.getFieldName(elm) + ' must contain only letters'};
        }
        return {VALID: true};
    },
    'email':      function(elm) {
        if ($(elm).val().search(/^\s*[\w\-\+_]+(\.[\w\-\+_]+)*\@[\w\-\+_]+\.[\w\-\+_]+(\.[\w\-\+_]+)*\s*$/) == -1) {
            return {VALID: false, ERROR: App.Validate.getFieldName(elm) + ' not a valid email'};
        }
        return {VALID: true};
    }
}


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
            var rules = App.Validate.getRules(field);
            $(rules).each(function(i, rule)
            {
                fb.log('Validate with %o %o', rule, field);
                if (App.Validate.Rule[rule]) {
                    var result = App.Validate.Rule[rule](field);
                    fb.log(result);
                    if (result.VALID == false) {
                        App.Env.FormError.push(result.ERROR); //$(field).attr('name') + ' is required');
                        form_valid = false;
                    }
                }
            })
            /*if ($(field).val().trim() == '' || $(field).val().trim() == '-') {
                App.Env.FormError.push($(field).attr('name') + ' is required');
                form_valid = false;
            }*/
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

App.Validate.getRules = function(elm)
{
    var rules_string = $(elm).attr('class');
    var rules = [];
    $(rules_string.split(/\s/)).each(function(i, str)
    {        
        var rule = str.split('rule-');
        if (rule.length > 1) {
            rules[rules.length++] = rule[1];
        }
    });
    
    return rules;
}



