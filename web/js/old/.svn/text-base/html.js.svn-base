App.HTML.makeDatabases = function(databases){
    var acc = [];
    $(databases).each(function(i, o){
        var tpl = App.Templates.get('database', 'database');
        tpl.set(':name', o.Database);
        tpl.set(':db_name', o.Database);
        acc[acc.length++] = tpl.finalize();
    });
    
    return acc.done();
}

App.HTML.makeDbTableList = function(data){
    var acc = [];
    $(data).each(function(i, o){
        var name = App.Helpers.getFirstValue(o);
        var tpl = App.Templates.get('database_table', 'database');
        tpl.set(':name', name);
        tpl.set(':table_name', name);
        acc[acc.length++] = tpl.finalize();
    });
    
    return acc.done();
}

App.HTML.makeDbFieldsList = function(data){
    var acc = [];
    $(data).each(function(i, o){
        var details = [o['Type'], o['Null'], o['Key'], o['Default'], o['Extra']].join(' ');
        var tpl = App.Templates.get('database_field', 'database');
        tpl.set(':name', o.Field);
        tpl.set(':details', details);
        acc[acc.length++] = tpl.finalize();
    });
    
    return acc.done();
}

App.HTML.Build.dns_form = function(options, id) {
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
    tpl.set(':DOMAIN', options.DNS_DOMAIN || '');
    tpl.set(':IP', options.IP || '');
    
    tpl = App.HTML.Build.dns_selects(tpl, options);
    
    return tpl.finalize();
}

App.HTML.Build.ip_form = function(options, id) {
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

App.HTML.Build.ip_selects = function(tpl, options) {
    // OWNER
    var opts = App.HTML.Build.options(App.Env.initialParams.SYS_USERS, options.OWNER);
    tpl.set(':owner_options', opts);
    
    // STATUS
    var opts = App.HTML.Build.options(App.Env.initialParams.STATUSES, options.STATUS);
    tpl.set(':status_options', opts);
    
    // INTERFACE
    var opts = App.HTML.Build.options(App.Env.initialParams.INTERFACES, options.INTERFACE);
    tpl.set(':interface_options', opts);
    
    return tpl;
}

App.HTML.Build.dns_selects = function(tpl, options) {
    // TPL
    var opts = App.HTML.Build.options(App.Constants.DNS_TEMPLATES, options.TPL);
    tpl.set(':TPL', opts);
    
    return tpl;
}

App.HTML.Build.options = function(initial, default_value) {
    var opts = [];
    $.each(initial, function(key){
       var selected = key == default_value ? 'selected="selected"' : ''; 
       opts[opts.length++] = '<option value="'+key+'" '+selected+'>'+initial[key]+'</options>';
    });
    return opts.join('');
}

App.HTML.Build.ip_entry = function(o){
    var tpl = App.Templates.get('ENTRY', 'ip');
    tpl.set(':source',$.toJSON(o));
    tpl.set(':NETMASK', o.NETMASK);
    tpl.set(':IP_ADDRESS', o.IP_ADDRESS);
    tpl.set(':SYS_USERS', o.U_SYS_USERS);
    tpl.set(':WEB_DOMAINS', o.U_WEB_DOMAINS);
    tpl.set(':DATE', o.DATE);
    tpl.set(':INTERFACE', o.INTERFACE);
    tpl.set(':NAME', o.NAME);
    tpl.set(':OWNER', o.OWNER);
    tpl.set(':STATUS', o.STATUS);
    tpl.set(':U_SYS_USERS', o.U_SYS_USERS);
    tpl.set(':U_WEB_DOMAINS', o.U_WEB_DOMAINS);
    
    if (App.Constants.SUSPENDED_YES == o.SUSPENDED) {
        var sub_tpl = App.Templates.get('SUSPENDED_TPL_ENABLED', 'ip');
    }
    else {
        var sub_tpl = App.Templates.get('SUSPENDED_TPL_DISABLED', 'ip');
    }
    
    tpl.set(':SUSPENDED_TPL', sub_tpl.finalize());
    
    return tpl.finalize();
}

App.HTML.Build.dns_entry = function(o){
    var tpl = App.Templates.get('ENTRY', 'dns');
    tpl.set(':source', App.Helpers.toJSON(o));
    tpl.set(':DNS_DOMAIN', o.DNS_DOMAIN);
    var ip = o.IP.split('.');
    tpl.set(':IP', ip.join('<span class="dot">.</span>'));
    tpl.set(':TTL', o.TTL);
    tpl.set(':TPL', o.TPL);
    tpl.set(':SOA', o.SOA);
    tpl.set(':TTL', o.TTL);
    tpl.set(':DATE', o.DATE);
    /*tpl.set(':NETMASK', o.NETMASK);
    tpl.set(':IP_ADDRESS', o.IP_ADDRESS);
    tpl.set(':SYS_USERS', o.U_SYS_USERS);
    tpl.set(':WEB_DOMAINS', o.U_WEB_DOMAINS);
    tpl.set(':DATE', o.DATE);
    tpl.set(':INTERFACE', o.INTERFACE);
    tpl.set(':NAME', o.NAME);
    tpl.set(':OWNER', o.OWNER);
    tpl.set(':STATUS', o.STATUS);
    tpl.set(':U_SYS_USERS', o.U_SYS_USERS);
    tpl.set(':U_WEB_DOMAINS', o.U_WEB_DOMAINS);
    */
    if (App.Constants.SUSPENDED_YES == o.SUSPEND) {
        var sub_tpl = App.Templates.get('SUSPENDED_TPL_ENABLED', 'dns');
    }
    else {
        var sub_tpl = App.Templates.get('SUSPENDED_TPL_DISABLED', 'dns');
    }
    
    tpl.set(':SUSPENDED_TPL', sub_tpl.finalize());
    
    return tpl.finalize();
}

