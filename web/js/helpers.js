

App.Helpers.getMbHumanMeasure = function(val)
{  
    return App.Helpers.getMbHuman(val, true);
}

/**
 * Currently no bytes are used, minimal value is in MB
 * uncomment in case we will use bytes instead
 */
App.Helpers.getMbHuman = function(val, only_measure)
{
    var bytes     = val * 1024 * 1024;
    var kilobyte  = 1024;
    var megabyte  = kilobyte * 1024;
    var gigabyte  = megabyte * 1024;
    var terabyte  = gigabyte * 1024;
    var precision = 0;
   
    /*if ((bytes >= 0) && (bytes < kilobyte)) {
        return bytes + ' B';
 
    } else if ((bytes >= kilobyte) && (bytes < megabyte)) {
        return (bytes / kilobyte).toFixed(precision) + ' KB';
 
    } else */
    if ((bytes >= megabyte) && (bytes < gigabyte)) {
        return only_measure ? 'MB' : (bytes / megabyte).toFixed(precision);
 
    } else if ((bytes >= gigabyte) && (bytes < terabyte)) {
        return only_measure ? 'GB' :  (bytes / gigabyte).toFixed(precision);
 
    } else if (bytes >= terabyte) {
        return only_measure ? 'TB' : (bytes / terabyte).toFixed(precision);
 
    } else {
        return only_measure ? 'MB' : bytes;
    }
}

App.Helpers.getFirst = function(obj)
{
    try{ // TODO: remove try / catch
        var first = {};
        var key = App.Helpers.getFirstKey(obj);
        first[key] = obj[key];
        return first;
    }
    catch(e){
        fb.error(e);
    }
    
    return false;
}

App.Helpers.getFirstKey = function(obj)
{
    try{ // TODO: remove try / catch
        for (key in obj) break;
        return key;       
    }
    catch(e){
        fb.error(e);
    }
    
    return false;
}

App.Helpers.updateInitial = function()
{
    // TODO: need api method
    $.each(App.Env.initialParams, function(key) {
        var item = App.Env.initialParams[key];
        $.each(item, function (i, o) {
            if (i.indexOf('total_') != -1) {
                App.View.updateInitialInfo(i, o);
            }
        });
    });
}

App.Helpers.beforeAjax = function(jedi_method) 
{
    switch(jedi_method) {
        case 'DNS.getList':
            App.Helpers.showLoading();
            break;
        default:
            App.Helpers.showLoading();
            break;
    }
}
 
App.Helpers.afterAjax = function() 
{
    App.Helpers.removeLoading();
}

App.Helpers.removeLoading = function() 
{
    var ref = $('#loading');
    if (ref.length > 0) {
        ref.remove();
    }
}

App.Helpers.showLoading = function() 
{
    App.Helpers.removeLoading();
    var tpl = App.Templates.get('loading', 'general');
    $(document.body).append(tpl.finalize());
}
 
// todo: no iteration here
App.Helpers.getFirstValue = function(obj)
{
    var first = '';
    $.each(obj, function(key, i){
        return first = obj[key];
    });
    
    return first;
}

App.Helpers.evalJSON = function(str) 
{
    /*str = str.replace(/\\'/gi, '');
    str = str.replace(/\'/gi, '');
    fb.warn(str);*/
    return $.parseJSON(str);
}

App.Helpers.toJSON = function(object) 
{    
    return ($.toJSON(object).replace(/\\'/gi, ''));
}


//
//  Hints
//
App.Helpers.showConsoleHint = function()
{
    // TODO:
}

App.Helpers.markBrowserDetails = function()
{
    var b = App.Env.BROWSER;
    var classes = [
    b.type.toLowerCase(),
    b.type.toLowerCase() + b.version,
    b.os.toLowerCase()
    ];
    $(document.body).addClass(classes.join(' '));
}

App.Utils.detectBrowser = function()
{
    App.Env.BROWSER = {
        type: $.browser.browser(),
        version: $.browser.version.number(),
        os: $.browser.OS()
    }; 
    
    App.Helpers.markBrowserDetails();
}

App.Helpers.getFormValues = function(form) 
{
    var values = {};
    $(form).find('input, select, textarea').each(function(i, o) {
        if ($.inArray($(o).attr('class'), ['source', 'target'])) {
            values[$(o).attr('name')] = $(o).val();
        }
    });
    
    return values;
}

App.Helpers.getFormValuesFromElement = function(ref) 
{
    var values = {};
    $(ref).find('input, select, textarea').each(function(i, o) {
        if ($.inArray($(o).attr('class'), ['source', 'target'])) {
            values[$(o).attr('name')] = $(o).val();
        }
    });
    
    return values;
}

App.Helpers.updateScreen = function()
{
    Custom.init();
    //$(document.body).find('select').each(function(i, o){
    //   $(o).selectbox(); 
    //});
    }

App.Helpers.alert = function(msg) 
{
    alert(msg);
}

App.Helpers.isEmpty = function(o) 
{
    return '({})' == o.toSource() || '[]' == o.toSource();
}

App.Helpers.liveValidate = function()
{
    //return;
    $('input').live('blur', function(evt)
    {
        fb.log('BLUR');
        var elm = $(evt.target);
        fb.log(elm.attr('TAGNAME'));
    });
}

App.Helpers.generatePassword = function()
{   
   var length = 8; 
   var chars = "aAbBcCdDeEfFgGhHiIjJkKlLmMnNoOpPqQrRsStTuUvVwWxXyYzZ0123456789";
   var pass = "";

   for (x=0;x<length;x++) {
      var i = Math.floor(Math.random() * chars.length);
      pass += chars.charAt(i);
   }

   return pass;
}

App.Helpers.Warn = function(msg)
{
    alert(msg);
}

App.Helpers.openInnerPopup = function(elm, html)
{
    App.Helpers.closeInnerPopup();
    
    var offset = $(elm).offset();
    var tpl = App.Templates.get('inner_popup', 'general');
    tpl.set(':CONTENT', html);
    tpl.set(':LEFT', offset.left);
    tpl.set(':TOP', offset.top);
    
    $(document.body).append(tpl.finalize());
}

App.Helpers.closeInnerPopup = function(evt)
{
    $('#inner-popup').remove();
}
