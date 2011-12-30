App.Helpers.formatNumber = function(number, no_commas){
    no_commas = no_commas || false;
    number = number.toString().replace(/,/g, '');
       
    var nStr = parseFloat(number).toFixed(2);
    fb.info(nStr);
    nStr = nStr.toString();
    nStr += '';
    x = nStr.split('.');
    x1 = x[0];
    x2 = x.length > 1 ? '.' + x[1] : '';
    if(!no_commas){
        var rgx = /(\d+)(\d{3})/;
        while (rgx.test(x1)) {
            x1 = x1.replace(rgx, '$1' + ',' + '$2');
        }
    }
    return x1 + x2;
}

App.Helpers.getHumanTabName = function()
{
    if (App.Env.world == 'WEB_DOMAIN') {
        return 'WEB DOMAIN';
    }
    if (App.Env.world == 'MAIL') {
        return 'MAIL DOMAIN';
    }
    if (App.Env.world == 'DNS') {
        return 'DNS DOMAIN';
    }
    if (App.Env.world == 'IP') {
        return 'IP ADDRESS';
    }
    if (App.Env.world == 'CRON') {
        return 'CRON JOB';
    }
    if (App.Env.world == 'DB') {
        return 'DATABASE';
    }
    if (App.Env.world == 'BACKUPS') {
        return 'BACKUP';
    }
    if (App.Env.world == 'STATS') {
        return 'STATS';
    }
    return App.Env.world;
}

App.Helpers.scrollTo = function(elm)
{    
    var scroll_to = $(elm).offset().top;
    if (scroll_to > 1000) {
        var scroll_time = 300;
    }
    else {
        var scroll_time = 550;
    }
    $('html, body').animate({ 'scrollTop': scroll_to }, scroll_time);
}

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
   
   
    return only_measure ? 'MB' : (bytes / megabyte).toFixed(precision);
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
    var first = {};
    var key = App.Helpers.getFirstKey(obj);
    first[key] = obj[key];
    return first;
}

App.Helpers.getFirstKey = function(obj)
{ 
    for (key in obj) break;
    return key;       
}

App.Helpers.updateInitial = function()
{    
    $.each(App.Env.initialParams.totals, function(key) {
        var item = App.Env.initialParams.totals[key];
        var expr_id = '#'+key;
        if ('undefined' != typeof item.total) {
            var ref = $(expr_id).find('.num-total');
            if (ref.length > 0) {
                $(ref).html(item.total);
            }
        }
        if ('undefined' != typeof item.blocked) {            
            var ref = $(expr_id).find('.num-blocked');
            if (ref.length > 0) {
                $(ref).html(item.blocked);
            }
        }        
    });
    $('#user-name').html(App.Env.initialParams.PROFILE.uid);
    $('#page').removeClass('hidden');
    
    if (App.Env.initialParams.real_user) {
        var tpl = App.Templates.get('logged_as', 'general');
        tpl.set(':YOU_ARE', App.Env.initialParams.real_user);
        tpl.set(':USER', App.Env.initialParams.auth_user.uid.uid);
        $('body').prepend(tpl.finalize());
    }
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
    $.each(obj, function(key, i) {
        return first = obj[key];
    });
    
    return first;
}

App.Helpers.evalJSON = function(str) 
{       
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
    
    /*App.Ajax.request('MAIN.getInitial', {}, function(reply){
        App.Env.initialParams = reply.data;
        App.Helpers.updateInitial();
    });*/
    $('.first-row').removeClass('first-row');
    $('.row:first').addClass('first-row');
    Custom.init();
}

App.Helpers.alert = function(msg) 
{
    alert(msg);
}

App.Helpers.isEmpty = function(o) 
{
    return 'undefined' == typeof o ? true : jQuery.isEmptyObject(o);
}

App.Helpers.liveValidate = function()
{    
    
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

App.Helpers.openInnerPopup = function(elm, html, title)
{
    var title = title || '';
    App.Helpers.closeInnerPopup();
    
    var offset = $(elm).offset();
    var tpl = App.Templates.get('inner_popup', 'general');
    tpl.set(':CONTENT', html);
    tpl.set(':LEFT', offset.left);
    tpl.set(':TOP', offset.top);
    tpl.set(':POPUP_TITLE', title);
    
    $(document.body).append(tpl.finalize());
}

App.Helpers.closeInnerPopup = function(evt)
{
    $('#inner-popup').remove();
}

App.Helpers.getUploadUrl = function()
{
    return App.Helpers.generateUrl('vesta/upload.php');
}

App.Helpers.getBackendUrl = function()
{
    return App.Helpers.generateUrl('dispatch.php');
}

App.Helpers.generateUrl = function(to_file)
{
    var url_parts = location.href.split('#');
    if (url_parts.length > 1) {
        var tab = url_parts[url_parts.length - 1];
        if ($.inArray(tab, App.Constants.TABS) != -1) {
            App.Tmp.loadTAB = tab;
        }
    }

    var url_parts = location.href.split('?', 1);
    var url = url_parts[0];
    url_parts = url.split('/');
    if (url_parts[url_parts.length -1] == 'index.html') {
        url_parts[url_parts.length -1] = to_file;
    }
    else {
        url_parts.push(to_file);
    }

    return url_parts.join('/').replace('#', '');
}

App.Helpers.disableNotEditable = function()
{
    if ('undefined' == typeof App.Settings.Imutable[App.Env.world]) {
        return false;
    }
    
    $('.form').each(function(i, form)
    {
        if ($(form).attr('id') == '') {
            $('input, select, textarea', form).each(function(i, elm) {
                if ($.inArray($(elm).attr('name'), App.Settings.Imutable[App.Env.world]) != -1) {
                    $(elm).attr('disabled', true);
                }
            });       
        }
    });
}

App.Helpers.handleItemsRegisteredInBackground = function(evt)
{
    // complex selects
    if (!$(evt.target).hasClass('c-s-opt')) { // complex select option
        $('.complex-select-content').addClass('hidden');
    }
}

//
//      HELPERS
//
App.Helpers.keyboard_ESC = function()
{
    $('.complex-select-content').addClass('hidden');
    App.Tmp.focusedComplexSelect = null;
}

App.Helpers.keyboard_ENTER = function()
{
    if (null != App.Tmp.focusedComplexSelectInput) {
        var val = App.Tmp.focusedComplexSelectInput.find('.c-s-value').val();
        App.Tmp.focusedComplexSelect.find('.c-s-title').text(val);
        App.Tmp.focusedComplexSelect.find('.c-s-value-ref').val(val);
        $('.complex-select-content').addClass('hidden');
    }
}

App.Helpers.keyboard_DOWN = function(evt)
{
    if (null != App.Tmp.focusedComplexSelect) {
        App.Tmp.focusedComplexSelect.find('.complex-select-content').removeClass('hidden');
        $('.s-c-highlighted').removeClass('s-c-highlighted');
        if (null == App.Tmp.focusedComplexSelectInput) {
            App.Tmp.focusedComplexSelectInput = App.Tmp.focusedComplexSelect.find('.cust-sel-option:first');
            App.Tmp.focusedComplexSelectInput.addClass('s-c-highlighted');
        }
        else {
            var ref = App.Tmp.focusedComplexSelectInput.next();
            App.Tmp.focusedComplexSelectInput = ref;
            if (ref.length == 1) {
                ref.addClass('s-c-highlighted');
            }
            else {
                App.Tmp.focusedComplexSelectInput = App.Tmp.focusedComplexSelect.find('.cust-sel-option:first');
                App.Tmp.focusedComplexSelectInput.addClass('s-c-highlighted');
            }
        }
    }
}

App.Helpers.keyboard_UP = function(evt)
{
    if (null != App.Tmp.focusedComplexSelect) {
        App.Tmp.focusedComplexSelect.find('.complex-select-content').removeClass('hidden');
        $('.s-c-highlighted').removeClass('s-c-highlighted');
        if (null == App.Tmp.focusedComplexSelectInput) {
            App.Tmp.focusedComplexSelectInput = App.Tmp.focusedComplexSelect.find('.cust-sel-option:last');
            App.Tmp.focusedComplexSelectInput.addClass('s-c-highlighted');
        }
        else {
            var ref = App.Tmp.focusedComplexSelectInput.prev();
            App.Tmp.focusedComplexSelectInput = ref;
            if (ref.length == 1) {
                ref.addClass('s-c-highlighted');
            }
            else {
                App.Tmp.focusedComplexSelectInput = App.Tmp.focusedComplexSelect.find('.cust-sel-option:last');
                App.Tmp.focusedComplexSelectInput.addClass('s-c-highlighted');
            }
        }
    }
}

