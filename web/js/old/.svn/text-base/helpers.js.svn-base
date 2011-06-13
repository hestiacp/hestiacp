App.Helpers.updateInitial = function(){
    $.each(App.Env.initialParams, function(key) {
        var item = App.Env.initialParams[key];
        $.each(item, function (i, o) {
            if (i.indexOf('total_') != -1) {
                App.View.updateInitialInfo(i, o);
            }
        });
    });
}

App.Helpers.beforeAjax = function(jedi_method) {
    switch(jedi_method) {
        case 'DNS.getList':
            App.Helpers.showLoading();
            break;
        default:
            break;
    }
}
 
App.Helpers.afterAjax = function() {
    App.Helpers.removeLoading();
}

App.Helpers.removeLoading = function() {
    var ref = $('#loading');
    if (ref.length > 0) {
        ref.remove();
    }
}

App.Helpers.showLoading = function() {
    App.Helpers.removeLoading();
    var tpl = App.Templates.get('loading', 'general');
    $(document.body).append(tpl.finalize());
}
 
// todo: no iteration here
App.Helpers.getFirstValue = function(obj){
    var first = '';
    $.each(obj, function(key, i){
        return first = obj[key];
    });
    
    return first;
}

App.Helpers.evalJSON = function(string) {
    return $.parseJSON(string);
}

App.Helpers.toJSON = function(object) {
    return ($.toJSON(object).replace(/'/gi, ''));
}


//
//  Hints
//
App.Helpers.showConsoleHint = function(){
    // TODO:
    }


// UTILS
App.Utils.generatePasswordHash = function(length){
    var length = length || 11;
    var chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!~.";
    var pass = "";
    for(var x=0;x<length;x++)
    {
        var i = Math.floor(Math.random() * 62);
        pass += chars.charAt(i);
    }

    return pass;
}

App.Helpers.markBrowserDetails = function(){
    var b = App.Env.BROWSER;
    var classes = [
    b.type.toLowerCase(),
    b.type.toLowerCase() + b.version,
    b.os.toLowerCase()
    ];
    $(document.body).addClass(classes.join(' '));
}

App.Utils.detectBrowser = function(){
    App.Env.BROWSER = {
        type: $.browser.browser(),
        version: $.browser.version.number(),
        os: $.browser.OS()
    }; 
    
    App.Helpers.markBrowserDetails();
}

App.Helpers.getFormValues = function(form) {
    var values = {};
    $(form).find('input[type!="hidden"], select, textarea').each(function(i, o){
        values[$(o).attr('name')] = $(o).val();
    });
    
    return values;
}

App.Helpers.updateScreen = function(){
    //Custom.init();
    //$(document.body).find('select').each(function(i, o){
    //   $(o).selectbox(); 
    //});
    }

App.Helpers.alert = function(msg) {
    alert(msg);
}

App.Helpers.isEmpty = function(o) {
    return '({})' == o.toSource() || '[]' == o.toSource();
}