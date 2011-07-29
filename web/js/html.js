App.HTML.setTplKeys = function(tpl, o, empty)
{
    var empty = empty || '-';
    fb.log(empty);
    tpl.set(':source', $.toJSON(o))
    $(o).each(function(i, object)
    {              
        $.each(o, function(key)
        {  
            var val = o[key];     
            if (empty == true) {       
                tpl.set(':' + key, val || '');
            }
            else {
                tpl.set(':' + key, val || '-');
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
{
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
    
    return tpl.finalize();
}

App.HTML.Build.ip_selects = function(tpl, options) 
{
    // OWNER
    var opts = App.HTML.Build.options(App.Env.initialParams.IP.SYS_USERS, options.OWNER);
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
        return '';
    }
    
    return tpl;
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
       
    if (App.Constants.SUSPENDED_YES == o.SUSPENDED) {
        var sub_tpl = App.Templates.get('SUSPENDED_TPL_ENABLED', 'ip');
    }
    else {
        var sub_tpl = App.Templates.get('SUSPENDED_TPL_DISABLED', 'ip');
    }
    
    tpl.set(':SUSPENDED_TPL', sub_tpl.finalize());
    
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
    
    if (App.Constants.SUSPENDED_YES == o.SUSPEND) {
        var sub_tpl = App.Templates.get('SUSPENDED_TPL_ENABLED', 'dns');
    }
    else {
        var sub_tpl = App.Templates.get('SUSPENDED_TPL_DISABLED', 'dns');
    }
    
    tpl.set(':SUSPENDED_TPL', sub_tpl.finalize());
    
    return tpl.finalize();
}

App.HTML.Build.user_entry = function(o, key)
{
    var processed_data = {
        'NICKNAME': key,
        'BANDWIDTH_PERCENTS': 90,
        'U_DISK_PERCENTS': 80
    };
    var o = $.extend(o, processed_data);
    var tpl = App.Templates.get('ENTRY', 'user');  
    tpl = App.HTML.setTplKeys(tpl, o);
       
    if (App.Constants.SUSPENDED_YES == o.SUSPENDED) {
        var sub_tpl = App.Templates.get('SUSPENDED_TPL_ENABLED', 'ip');
    }
    else {
        var sub_tpl = App.Templates.get('SUSPENDED_TPL_DISABLED', 'ip');
    }
    tpl.set(':SUSPENDED_TPL', sub_tpl.finalize());
    
    return tpl.finalize();
}


App.HTML.Build.user_form = function(options, id) 
{
    if('undefined' == typeof App.Env.initialParams) {
        return alert('PLease wait a bit. Some background processes are not yet executed. Thank you for patience.');
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
    
    options = !App.Helpers.isEmpty(options) ? options : {'CONTACT':'', 'PASSWORD':'','LOGIN_NAME':'','NS':''};
    
    tpl = App.HTML.setTplKeys(tpl, options, true);        
    tpl = App.HTML.Build.user_selects(tpl, options);
    
    return tpl.finalize();
}

App.HTML.Build.web_domain_entry = function(o, key)
{    
    // TODO:
    /*<span class="domain-name">~!:ALIAS~!,</span>\
								<span class="domain-name">naumov-socolov.org.md,</span>\
								<span class="domain-name">naumov-socolov.to</span>\*/
    var processed_data = {
        DOMAIN: key
    };
    var o = $.extend(o, processed_data);
    //fb.info(o);
    var tpl = App.Templates.get('ENTRY', 'web_domain');
    tpl = App.HTML.setTplKeys(tpl, o);      
    
    /*if (App.Constants.SUSPENDED_YES == o.SUSPENDED) {
        var sub_tpl = App.Templates.get('SUSPENDED_TPL_ENABLED', 'ip');
    }
    else {
        var sub_tpl = App.Templates.get('SUSPENDED_TPL_DISABLED', 'ip');
    }
    
    tpl.set(':SUSPENDED_TPL', sub_tpl.finalize());
    */
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
    var processed_data = {
        DOMAIN: key
    };
    var o = $.extend(o, processed_data);
    var tpl = App.Templates.get('ENTRY', 'db');
    tpl = App.HTML.setTplKeys(tpl, o);      
    
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
        acc[acc.length++] = tpl.finalize();
    });
    tpl.set(':SHELL_OPTIONS', acc.done());
    
    return tpl;
}

