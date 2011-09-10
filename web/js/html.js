App.HTML.setTplKeys = function(tpl, o, empty)
{
    var empty = empty || '';
    fb.log(empty);
    tpl.set(':source', $.toJSON(o).replace(/'/gi, "\\'"))
    $(o).each(function(i, object)
    {
        $.each(o, function(key)
        {
            var val = o[key];
            if (empty == true) {
                tpl.set(':' + key, val || '');
            }
            else {
                tpl.set(':' + key, val || '');
            }
        });
    });

    return tpl;
}

App.HTML.makeDatabases = function(databases)
{
    var acc = [];
    $(databases).each(function(i, o)
    {
        var tpl = App.Templates.get('database', 'database');
        tpl.set(':name', o.Database);
        tpl.set(':db_name', o.Database);
        acc[acc.length++] = tpl.finalize();
    });

    return acc.done();
}

App.HTML.makeDbTableList = function(data)
{
    var acc = [];
    $(data).each(function(i, o)
    {
        var name = App.Helpers.getFirstValue(o);
        var tpl = App.Templates.get('database_table', 'database');
        tpl.set(':name', name);
        tpl.set(':table_name', name);
        acc[acc.length++] = tpl.finalize();
    });

    return acc.done();
}

App.HTML.makeDbFieldsList = function(data)
{
    var acc = [];
    $(data).each(function(i, o)
    {
        var details = [o['Type'], o['Null'], o['Key'], o['Default'], o['Extra']].join(' ');
        var tpl = App.Templates.get('database_field', 'database');
        tpl.set(':name', o.Field);
        tpl.set(':details', details);
        acc[acc.length++] = tpl.finalize();
    });

    return acc.done();
}

App.HTML.Build.dns_form = function(options, id) 
{
    if('undefined' == typeof App.Env.initialParams) {
        return alert('PLease wait a bit. Some background processes are not yet executed. Thank you for patience.');
    }
    var tpl = App.Templates.get('FORM', 'dns');
    tpl.set(':source', options);

    options = App.Helpers.evalJSON(options) || {};
    if (App.Helpers.isEmpty(options)) {
       tpl.set(':title', 'New dns record'); 
       tpl.set(':save_button', 'ADD'); 
    }
    else {
        tpl.set(':title', 'Edit dns record'); 
        tpl.set(':save_button', 'SAVE'); 
    }

    tpl.set(':id', id || ''); 
    tpl.set(':DNS_DOMAIN', options.DNS_DOMAIN || '');    
    tpl.set(':IP', options.IP || '');
    tpl.set(':TTL', options.TTL || '');
    tpl.set(':SOA', options.SOA || '');
    tpl.set(':DATE', options.DATE || '');

    tpl = App.HTML.Build.dns_selects(tpl, options);

    return tpl.finalize();
}

App.HTML.Build.ip_form = function(options, id) 
{try{
    if('undefined' == typeof App.Env.initialParams) {
        return alert('PLease wait a bit. Some background processes are not yet executed. Thank you for patience.');
    }
    var tpl = App.Templates.get('FORM', 'ip');
    tpl.set(':source', options);

    options = App.Helpers.evalJSON(options) || {};
    if (App.Helpers.isEmpty(options)) {
       tpl.set(':title', 'New ip address'); 
       tpl.set(':save_button', 'ADD'); 
    }
    else {
        tpl.set(':title', 'Edit ip address'); 
        tpl.set(':save_button', 'SAVE'); 
    }

    tpl.set(':id', id || ''); 
    tpl.set(':IP_ADDRESS', options.IP_ADDRESS || '');
    tpl.set(':NETMASK', options.NETMASK || '');
    tpl.set(':NAME', options.NAME || '');
    
    tpl = App.HTML.Build.ip_selects(tpl, options);
}catch(e){fb.error(e);}
    return tpl.finalize();
}


App.HTML.Build.options = function(initial, default_value) 
{
    var opts = [];
    $.each(initial, function(key){
       var selected = key == default_value ? 'selected="selected"' : ''; 
       opts[opts.length++] = '<option value="'+key+'" '+selected+'>'+initial[key]+'</options>';
    });
    return opts.join('');
}

App.HTML.Build.ip_entry = function(o)
{
    var tpl = App.Templates.get('ENTRY', 'ip');
    tpl = App.HTML.setTplKeys(tpl, o);

    /*if (App.Constants.SUSPENDED_YES == o.SUSPENDED) {
        var sub_tpl = App.Templates.get('SUSPENDED_TPL_ENABLED', 'ip');
    }
    else {
        var sub_tpl = App.Templates.get('SUSPENDED_TPL_DISABLED', 'ip');
    }*/

    tpl.set(':SUSPENDED_TPL', '');

    return tpl.finalize();
}

App.HTML.Build.dns_entry = function(o, is_new)
{
    var tpl = App.Templates.get('ENTRY', 'dns');
    tpl = App.HTML.setTplKeys(tpl, o);      

    var ip = o.IP.split('.');
    tpl.set(':IP', ip.join('<span class="dot">.</span>'));    
    tpl.set(':CHECKED', '');
    if (is_new) {
        var now = new Date();
        tpl.set(':DATE', now.format("d.mm.yyyy"));
    }

    /*if (App.Constants.SUSPENDED_YES == o.SUSPEND) {
        var sub_tpl = App.Templates.get('SUSPENDED_TPL_NOT_SUSPENDED', 'general');
    }
    else {
        var sub_tpl = App.Templates.get('SUSPENDED_TPL_SUSPENDED', 'general');
    }*/

    tpl.set(':SUSPENDED_TPL', '');

    return tpl.finalize();
}

App.HTML.Build.user_entry = function(o, key)
{
    var processed_data = {
        'NICKNAME'              : key, 
        'U_DISK_PERCENTAGE'     : o.U_DISK > 0 ? o.U_DISK / o.DISK_QUOTA * 100 : 0.01,
        'U_BANDWIDTH_PERCENTAGE': o.U_BANDWIDTH > 0 ? o.U_BANDWIDTH / o.BANDWIDTH * 100 : 0.01,
        'DISK_QUOTA_MEASURE'    : App.Helpers.getMbHumanMeasure(o.DISK_QUOTA),
        'BANDWIDTH_MEASURE'     : App.Helpers.getMbHumanMeasure(o.BANDWIDTH),
        'BANDWIDTH'             : App.Helpers.getMbHuman(o.BANDWIDTH),
        'DISK_QUOTA'            : App.Helpers.getMbHuman(o.DISK_QUOTA)
    };
    var o = $.extend(o, processed_data);
    var tpl = App.Templates.get('ENTRY', 'user');  
    tpl = App.HTML.setTplKeys(tpl, o);
       
    tpl.set(':SUSPENDED_TPL', '');//sub_tpl.finalize());
   
    var ns      = [];
    var ns_full = [];
    
    $([1,2,3,4,5,6,7,8]).each(function(i, index)
    {
        if (o['NS'+index].trim() != '') {
            var tpl_ns = App.Templates.get('NS_RECORD', 'user');
            tpl_ns.set(':NAME', o['NS'+index]);
            var tpl_finalized = tpl_ns.finalize();
            ns_full[ns_full.length++] = tpl_finalized;
            if (i < App.Settings.USER_VISIBLE_NS) {
                ns[ns.length++] = tpl_finalized;
            }
        }                      
    });
        
    if (ns_full.length <= App.Settings.USER_VISIBLE_NS) {
        tpl.set(':NS', ns.done());
    }
    else {
        var ns_custom = App.Templates.get('NS_MINIMIZED', 'user');
        ns_custom.set(':NS_MINI', ns.done());
        ns_custom.set(':NS_FULL', ns_full.done());
        ns_custom.set(':MORE_NUMBER', Math.abs(App.Settings.USER_VISIBLE_NS - ns_full.length));
        tpl.set(':NS', ns_custom.finalize());
    }
    
    tpl.set(':REPORTS_ENABLED', o.REPORTS_ENABLED == 'yes' ? 'enabled' : 'DISABLED');    
        
    return tpl.finalize();
}


App.HTML.Build.user_form = function(options, id) 
{
    var in_edit = false;
    if (!App.Helpers.isEmpty(options)) {
        in_edit = true;
    }
    if('undefined' == typeof App.Env.initialParams) {
        return alert('Please wait a bit. Some background processes are not yet executed. Thank you for patience.');
    }
    var tpl = App.Templates.get('FORM', 'user');
    tpl.set(':source', options);
    tpl.set(':id', id || '');
    options = App.Helpers.evalJSON(options) || {};
    if (App.Helpers.isEmpty(options)) {
       tpl.set(':title', 'New user'); 
       tpl.set(':save_button', 'ADD'); 
    }
    else {
        tpl.set(':title', 'Edit user'); 
        tpl.set(':save_button', 'SAVE'); 
    }
    
    options = !App.Helpers.isEmpty(options) ? options : App.Empty.USER;
    
    // NS
    var ns = [];
    $([3,4,5,6,7,8]).each(function(i, index)
    {
        if (options['NS'+index].trim() != '') {
            var tpl_ns = App.Templates.get('NS_INPUT', 'user');
            tpl_ns.set(':NS_LABEL', 'NS #' + (index));
            tpl_ns.set(':NAME', options['NS'+index]);
            ns[ns.length++] = tpl_ns.finalize();
        }
    });
    ns[ns.length++] = App.Templates.get('PLUS_ONE_NS', 'user').finalize();
    
    tpl.set(':NS', ns.done());
    if (in_edit == true) {
        options.PASSWORD = App.Settings.PASSWORD_IMMUTE;
    }  
    tpl = App.HTML.setTplKeys(tpl, options, true);        
    tpl = App.HTML.Build.user_selects(tpl, options);
    
    if (options.REPORTS_ENABLED == 'yes') {
        tpl.set(':CHECKED', 'checked="checked"');
    }
    else {
        tpl.set(':CHECKED', '');
    }
    
    return tpl.finalize();
}

App.HTML.Build.web_domain_entry = function(o, key)
{    
    var processed_data = {
        DOMAIN: key
    };
    var o = $.extend(o, processed_data);
    var tpl = App.Templates.get('ENTRY', 'web_domain');
    tpl = App.HTML.setTplKeys(tpl, o);      
    
    if (App.Constants.SUSPENDED_YES == o.SUSPEND) {
        var sub_tpl = App.Templates.get('SUSPENDED_TPL_SUSPENDED', 'general');
    }
    else {
        var sub_tpl = App.Templates.get('SUSPENDED_TPL_NOT_SUSPENDED', 'general');
    }    
    tpl.set(':SUSPENDED_TPL', sub_tpl.finalize());
    
    if (o.STATS_LOGIN.trim() != '') {
        tpl.set(':STATS_AUTH', '+auth');
    }
    else {
        tpl.set(':STATS_AUTH', '');
    }
    
    return tpl.finalize();
}

App.HTML.Build.web_domain_form = function(options, id) 
{
    if('undefined' == typeof App.Env.initialParams) {
        return alert('PLease wait a bit. Some background processes are not yet executed. Thank you for patience.');
    }
    
    var in_edit = false;
    if (!App.Helpers.isEmpty(options)) {
        in_edit = true;
    }
    
    var tpl = App.Templates.get('FORM', 'web_domain');
    tpl.set(':source', options);
    tpl.set(':id', id || '');
    options = App.Helpers.evalJSON(options) || {};
    if (App.Helpers.isEmpty(options)) {
       tpl.set(':title', 'New WEB domain'); 
       tpl.set(':save_button', 'ADD'); 
    }
    else {
        tpl.set(':title', 'Edit WEB domain'); 
        tpl.set(':save_button', 'SAVE'); 
    }
    
    options = !App.Helpers.isEmpty(options) ? options : App.Empty.WEB_DOMAIN;
    if (in_edit == true) {
        options.STATS_PASSWORD = options.STATS_LOGIN.trim() != '' ? App.Settings.PASSWORD_IMMUTE : '';
    }  
    tpl = App.HTML.setTplKeys(tpl, options, true);        
    tpl = App.HTML.Build.web_domain_selects(tpl, options);
    
    if (options.CGI == 'yes') {
        tpl.set(':CHECKED_CGI', 'checked="checked"');
    }
    
    if (options.ELOG == 'yes') {
        tpl.set(':CHECKED_ELOG', 'checked="checked"');
    }
    
    if (options.STATS_LOGIN.trim() != '') {
        tpl.set(':STAT_AUTH', 'checked="checked"');
        tpl.set(':ACTIVE_LOGIN', '');
        tpl.set(':ACTIVE_PASSWORD', '');
        tpl.set(':stats_auth_checked', 'checked="checked"');
    }
    else {
        tpl.set(':ACTIVE_LOGIN', 'hidden');
        tpl.set(':ACTIVE_PASSWORD', 'hidden');
        tpl.set(':stats_auth_checked', '');
    }
    
    return tpl.finalize();
}

App.HTML.Build.mail_entry = function(o, key)
{        
    var processed_data = {
        DOMAIN: key
    };
    var o = $.extend(o, processed_data);
    var tpl = App.Templates.get('ENTRY', 'mail');
    tpl = App.HTML.setTplKeys(tpl, o);      
    
    return tpl.finalize();
}

App.HTML.Build.db_entry = function(o, key)
{
    var user_list_html = [];    
    $(o['USERS']).each(function(i, o)
    {
        var tpl = App.Templates.get('USER_ITEM', 'db');
        tpl.set(':NAME', o);
        user_list_html.push(tpl.finalize());
    });   
    var wrapper = App.Templates.get('USER_ITEMS_WRAPPER', 'db');
    wrapper.set(':CONTENT', user_list_html.done());
    var processed_data = {
        'USER_LIST': wrapper.finalize(),
        'USERS': o['USERS'].length || 0,
        'U_DISK_PERCENTAGE'     : o.U_DISK > 0 ? o.U_DISK / o.DISK * 100 : 0.01,
        'DISK_MEASURE': App.Helpers.getMbHumanMeasure(o.DISK),
        'DISK': App.Helpers.getMbHuman(o.DISK)
    };
    var o = $.extend(o, processed_data);
    
    var tpl = App.Templates.get('ENTRY', 'db');
    tpl = App.HTML.setTplKeys(tpl, o);      
    
    return tpl.finalize();
}

App.HTML.Build.db_form = function(options, id) 
{
    var in_edit = false;
    if (!App.Helpers.isEmpty(options)) {
        in_edit = true;
    }
    if('undefined' == typeof App.Env.initialParams) {
        return alert('PLease wait a bit. Some background processes are not yet executed. Thank you for patience.');
    }
    var tpl = App.Templates.get('FORM', 'db');
    tpl.set(':source', options);
    tpl.set(':id', id || '');
    options = App.Helpers.evalJSON(options) || {};
    if (App.Helpers.isEmpty(options)) {
       tpl.set(':title', 'New database'); 
       tpl.set(':save_button', 'ADD'); 
    }
    else {
        tpl.set(':title', 'Edit database "'+options.DB+'"'); 
        tpl.set(':save_button', 'SAVE'); 
    }
    
    options = !App.Helpers.isEmpty(options) ? options : App.Empty.DB;  
    if (in_edit == true) {
        options.PASSWORD = App.Settings.PASSWORD_IMMUTE;
    }  
    tpl = App.HTML.setTplKeys(tpl, options, true);        
    tpl = App.HTML.Build.db_selects(tpl, options);       
    
    return tpl.finalize();
}

App.HTML.Build.cron_entry = function(o, key)
{        
    var processed_data = {
        DOMAIN: key
    };
    var o = $.extend(o, processed_data);    
    var tpl = App.Templates.get('ENTRY', 'cron');
    tpl = App.HTML.setTplKeys(tpl, o);     
    
    if (App.Constants.SUSPENDED_YES == o.SUSPENDED) {
        var sub_tpl = App.Templates.get('SUSPENDED_TPL_SUSPENDED', 'general');
    }
    else {
        var sub_tpl = App.Templates.get('SUSPENDED_TPL_NOT_SUSPENDED', 'general');
    }    
    tpl.set(':SUSPENDED_TPL', sub_tpl.finalize()); 
    
    return tpl.finalize();
}
     

App.HTML.Build.cron_form = function(options, id) 
{try{
    if('undefined' == typeof App.Env.initialParams) {
        return alert('PLease wait a bit. Some background processes are not yet executed. Thank you for patience.');
    }
    var tpl = App.Templates.get('FORM', 'cron');
    tpl.set(':source', options);
    tpl.set(':id', id || '');
    options = App.Helpers.evalJSON(options) || {};
    if (App.Helpers.isEmpty(options)) {
       tpl.set(':title', 'New cron entry'); 
       tpl.set(':save_button', 'ADD'); 
    }
    else {
        tpl.set(':title', 'Edit cron entry'); 
        tpl.set(':save_button', 'SAVE'); 
    }
    
    options = !App.Helpers.isEmpty(options) ? options : {DAY:'', MONTH: '', WDAY:'',HOUR:'',CMD:'',MIN:''};    
    tpl = App.HTML.setTplKeys(tpl, options);  

    /*tpl.set(':id', id || ''); 
    tpl.set(':IP_ADDRESS', options.IP_ADDRESS || '');
    tpl.set(':NETMASK', options.NETMASK || '');
    tpl.set(':NAME', options.NAME || '');*/
    
    //tpl = App.HTML.Build.ip_selects(tpl, options);
}catch(e){fb.error(e);}
    return tpl.finalize();
}
    

App.HTML.Build.dns_records = function(records)
{
    var acc = [];
    $.each(records, function(i, record)
    {
        var record = records[i];
        var tpl = App.HTML.Build.dns_subrecord(record);
        acc[acc.length++] = tpl.finalize();
    });
    
    return acc.done();
}

App.HTML.Build.dns_subrecord = function(record)
{
    var tpl = App.Templates.get('SUBENTRY', 'dns');
    tpl.set(':RECORD', record.RECORD || '');
    tpl.set(':RECORD_VALUE', record.RECORD_VALUE || '');
    tpl.set(':RECORD_ID', record.RECORD_ID || '');
    //tpl.set(':RECORD_TYPE_VALUE', '');
    tpl.set(':RECORD_TYPE', App.HTML.Build.options(App.Env.initialParams.DNS.record.RECORD_TYPE, (record.RECORD_TYPE || -1)));
    
    return tpl;
}

App.HTML.Build.ssl_key_file = function()
{
    return '<iframe src="'+App.Helpers.getUploadUrl()+'?action=show&type=key" width="500px;" height="53px;" framevorder="0" scroll="no">..</iframe>';
}

App.HTML.Build.ssl_cert_file = function()
{
    return '<iframe src="'+App.Helpers.getUploadUrl()+'?action=show&type=cert" width="500px;" height="53px;" framevorder="0" scroll="no">..</iframe>';
}

App.HTML.Build.user_selects = function(tpl, options)
{
    var acc = [];
    // PACKAGE
    var pkg = App.Env.initialParams.USERS.PACKAGE;
    $.each(pkg, function(val)
    {
        var tpl = App.Templates.get('select_option', 'general');
        tpl.set(':VALUE', val);
        tpl.set(':TEXT', pkg[val]);
        tpl.set(':SELECTED', val == options.PACKAGE ? 'selected="selected"' : '');
        acc[acc.length++] = tpl.finalize();
    });
    tpl.set(':PACKAGE_OPTIONS', acc.done());
    // ROLE
    acc = [];
    var roles = App.Env.initialParams.USERS.ROLE;
    $.each(roles, function(val)
    {
        var tpl = App.Templates.get('select_option', 'general');
        tpl.set(':VALUE', val);
        tpl.set(':TEXT', roles[val]);
        tpl.set(':SELECTED', val == options.ROLE ? 'selected="selected"' : '');
        acc[acc.length++] = tpl.finalize();
    });
    tpl.set(':ROLE_OPTIONS', acc.done());
    // SHELL
    acc = [];
    var shell = App.Env.initialParams.USERS.SHELL;
    $.each(shell, function(val)
    {
        var tpl = App.Templates.get('select_option', 'general');
        tpl.set(':VALUE', val);
        tpl.set(':TEXT', shell[val]);
        tpl.set(':SELECTED', val == options.SHELL ? 'selected="selected"' : '');
        acc[acc.length++] = tpl.finalize();
    });
    tpl.set(':SHELL_OPTIONS', acc.done());
    
    return tpl;
}

App.HTML.Build.db_selects = function(tpl, options)
{
    var acc = [];
    // PACKAGE
    var items = App.Env.initialParams.DB.TYPE;
    $.each(items, function(val)
    {
        var tpl = App.Templates.get('select_option', 'general');
        tpl.set(':VALUE', val);
        tpl.set(':TEXT', items[val]);
        tpl.set(':SELECTED', val == options.TYPE ? 'selected="selected"' : '');
        acc[acc.length++] = tpl.finalize();
    });
    tpl.set(':TYPE_OPTIONS', acc.done());
    // ROLE
    acc = [];
    var items = App.Env.initialParams.DB.HOST;
    $.each(items, function(val)
    {
        var tpl = App.Templates.get('select_option', 'general');
        tpl.set(':VALUE', val);
        tpl.set(':TEXT', items[val]);
        tpl.set(':SELECTED', val == options.HOST ? 'selected="selected"' : '');
        acc[acc.length++] = tpl.finalize();
    });
    tpl.set(':HOST_OPTIONS', acc.done());
        
    return tpl;
}

App.HTML.Build.ip_selects = function(tpl, options) 
{
    // OWNER
    var users = App.Env.initialParams.IP.OWNER;
    var opts = App.HTML.Build.options(users, options.OWNER);
    tpl.set(':owner_options', opts);
    
    // STATUS
    var opts = App.HTML.Build.options(App.Env.initialParams.IP.STATUSES, options.STATUS);
    tpl.set(':status_options', opts);
    
    // INTERFACE
    var opts = App.HTML.Build.options(App.Env.initialParams.IP.INTERFACES, options.INTERFACE);
    tpl.set(':interface_options', opts);
    
    return tpl;
}

App.HTML.Build.dns_selects = function(tpl, options) 
{    
    try {
        // TPL
        var obj = App.Env.initialParams.DNS.TPL;
        var opts = App.HTML.Build.options(obj, options.TPL);
        tpl.set(':TPL', opts);
        tpl.set(':TPL_DEFAULT_VALUE', options.TPL || App.Helpers.getFirstKey(obj));
    }
    catch (e) {        
        return tpl;
    }
    
    return tpl;
}

App.HTML.Build.web_domain_selects = function(tpl, options) 
{    
    try {
        // IP
        var obj = App.Env.initialParams.WEB_DOMAIN.IP;
        var opts = App.HTML.Build.options(obj, options.IP);
        tpl.set(':IP_OPTIONS', opts);        
        
        // TPL
        var obj = App.Env.initialParams.WEB_DOMAIN.TPL;
        var opts = App.HTML.Build.options(obj, options.TPL);
        tpl.set(':TPL_OPTIONS', opts);        
        
        // TPL
        var obj = App.Env.initialParams.WEB_DOMAIN.STAT;
        var opts = App.HTML.Build.options(obj, options.STAT);
        tpl.set(':STAT_OPTIONS', opts);        
        
        
        //<input type="checkbox" name="STATS" ~!:stats_checked~!="" value="~!:STATS~!" class="not-styled">\
    }
    catch (e) {        
        return tpl;
    }
    
    return tpl;
}




