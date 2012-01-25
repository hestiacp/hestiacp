App.HTML.setTplKeys = function (tpl, o, empty) {
    var empty = empty || '';
    fb.log(empty);
    tpl.set(':source', $.toJSON(o).replace(/'/gi, "\\'"))
    $(o).each(function (i, object) {
        $.each(o, function (key) {
            var val = o[key];
            if (empty == true) {
                tpl.set(':' + key, val || '');
            } else {
                tpl.set(':' + key, val || '');
            }
        });
    });
    return tpl;
}

//
//	BUILD FORMS
//

App.HTML.Build.dns_form = function (options, id) {
    if ('undefined' == typeof App.Env.initialParams) {
        return alert('Please wait a bit. Some background processes are not yet executed. Thank you for patience.');
    }
    var tpl = App.Templates.get('FORM', 'dns');
    tpl.set(':source', options);
    options = App.Helpers.evalJSON(options) || {};
    if (App.Helpers.isEmpty(options)) {
        tpl.set(':title', 'New dns domain');
        tpl.set(':save_button', 'ADD');
        tpl.set(':DELETE_ACTION', '');
    } else {
        tpl.set(':title', 'Edit dns domain');
        tpl.set(':save_button', 'SAVE');
        tpl.set(':DELETE_ACTION', App.Templates.get('DELETE_ACTION', 'general').finalize());
    }
    tpl.set(':id', id || '');
    tpl.set(':DNS_DOMAIN', options.DNS_DOMAIN || '');
    tpl.set(':IP', options.IP || '');
    tpl.set(':TTL', options.TTL || '');
    tpl.set(':SOA', options.SOA || '');
    tpl.set(':DATE', options.DATE || '');
    tpl = App.HTML.Build.dns_selects(tpl, options);
    tpl = App.HTML.toggle_suspended_form(tpl, options);
    
    return tpl.finalize();
}

App.HTML.Build.ip_form = function (options, id) {
    if ('undefined' == typeof App.Env.initialParams) {
        return alert('PLease wait a bit. Some background processes are not yet executed. Thank you for patience.');
    }
    var tpl = App.Templates.get('FORM', 'ip');
    tpl.set(':source', options);
    options = App.Helpers.evalJSON(options) || {};
    if (App.Helpers.isEmpty(options)) {
        tpl.set(':title', 'New ip address');
        tpl.set(':save_button', 'ADD');
        tpl.set(':DELETE_ACTION', '');
    } else {
        tpl.set(':title', 'Edit ip address');
        tpl.set(':save_button', 'SAVE');
        tpl.set(':DELETE_ACTION', App.Templates.get('DELETE_ACTION', 'general').finalize());
    }
    tpl.set(':id', id || '');
    tpl.set(':IP_ADDRESS', options.IP_ADDRESS || '');
    tpl.set(':NETMASK', options.NETMASK || '');
    tpl.set(':NAME', options.NAME || '');
    tpl = App.HTML.Build.ip_selects(tpl, options);
    tpl = App.HTML.toggle_suspended_form(tpl, options);
    
    return tpl.finalize();
}

App.HTML.Build.user_form = function (options, id) {
    var in_edit = false;
    if (!App.Helpers.isEmpty(options)) {
        in_edit = true;
    }
    if ('undefined' == typeof App.Env.initialParams) {
        return alert('Please wait a bit. Some background processes are not yet executed. Thank you for patience.');
    }
    var tpl = App.Templates.get('FORM', 'user');
    tpl.set(':source', options);
    tpl.set(':id', id || '');
    options = App.Helpers.evalJSON(options) || {};
    if (App.Helpers.isEmpty(options)) {
        tpl.set(':title', 'New user');
        tpl.set(':save_button', 'ADD');
        tpl.set(':DELETE_ACTION', '');
    } else {
        tpl.set(':title', 'Edit user');
        tpl.set(':save_button', 'SAVE');
        tpl.set(':DELETE_ACTION', App.Templates.get('DELETE_ACTION', 'general').finalize());
    }
    options = !App.Helpers.isEmpty(options) ? options : App.Empty.USER;

    if (in_edit == true) {
        options.PASSWORD = App.Settings.PASSWORD_IMMUTE;
        var ns = [];
        $([3, 4, 5, 6, 7, 8]).each(function (i, index) {
            if (options['NS' + index].trim() != '') {
                var tpl_ns = App.Templates.get('NS_INPUT', 'user');
                tpl_ns.set(':NS_LABEL', 'Name Server #' + (index));
                tpl_ns.set(':NAME', options['NS' + index]);
                ns[ns.length++] = tpl_ns.finalize();
            }
        });
        ns[ns.length++] = App.Templates.get('PLUS_ONE_NS', 'user').finalize();
        tpl.set(':NS', ns.done());
    } else {
        tpl.set(':NS', '');
    }

    tpl = App.HTML.setTplKeys(tpl, options, true);
    tpl = App.HTML.Build.user_selects(tpl, options);
    tpl = App.HTML.toggle_suspended_form(tpl, options);
    if (options.REPORTS_ENABLED == 'yes' || options.REPORTS_ENABLED == 'on') {
        tpl.set(':CHECKED', 'checked="checked"');
    } else {
        tpl.set(':CHECKED', '');
    }
    if (!in_edit) {
        tpl.set(':REPORTS_ENABLED_EDITABLE', 'hidden');
    }

    return tpl.finalize();
}

App.HTML.Build.web_domain_form = function (options, id) {
    if ('undefined' == typeof App.Env.initialParams) {
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
        tpl.set(':DELETE_ACTION', '');
    } else {
        tpl.set(':title', 'Edit WEB domain');
        tpl.set(':save_button', 'SAVE');
        tpl.set(':DELETE_ACTION', App.Templates.get('DELETE_ACTION', 'general').finalize());

        if(options.SSL_CRT == '' || options.SSL_KEY == ''){
            options.SSL = '';
            options.SSL_HOME = '';
            options.SSL_CRT = '';
            options.SSL_KEY = '';
            options.SSL_CA = '';
        }

        if (options.SSL == 'on') {
            tpl.set(':ssl_checked', 'checked="checked"');
        }
        else {
            tpl.set(':ssl_checked', '');
        }
        if (options.SSL_HOME == 'on') {
            tpl.set(':ssl_home_checked', 'checked="checked"');
        }
        else{
            tpl.set(':ssl_home_checked', '');
        }
    }
    
    options = !App.Helpers.isEmpty(options) ? options : App.Empty.WEB_DOMAIN;
    if (in_edit == true) {
        options.STATS_PASSWORD = options.STATS_LOGIN.trim() != '' ? App.Settings.PASSWORD_IMMUTE : '';
    }
    tpl = App.HTML.setTplKeys(tpl, options, true);
    tpl = App.HTML.Build.web_domain_selects(tpl, options);
    tpl = App.HTML.toggle_suspended_form(tpl, options);

    if (options.CGI == 'yes' || options.CGI == 'on' || !in_edit) {
        tpl.set(':CHECKED_CGI', 'checked="checked"');
    }


    if (options.ELOG == 'yes' || options.ELOG == 'on') {
        tpl.set(':CHECKED_ELOG', 'checked="checked"');
    }
    if (options.STATS_LOGIN.trim() != '') {
        tpl.set(':STAT_AUTH', 'checked="checked"');
        tpl.set(':ACTIVE_LOGIN', '');
        tpl.set(':ACTIVE_PASSWORD', '');
        tpl.set(':stats_auth_checked', 'checked="checked"');
    } else {
        tpl.set(':ACTIVE_LOGIN', 'hidden');
        tpl.set(':ACTIVE_PASSWORD', 'hidden');
        tpl.set(':stats_auth_checked', '');
    }

    if (options.SSL == 'on') {
        tpl.set(':ssl_checked', 'checked="checked"');
        if (options.SSL_HOME == 'on') {
            tpl.set(':ssl_home_checked', 'checked="checked"');
        }
        else{
            tpl.set(':ssl_home_checked', '');
        }
    }
    else {
        tpl.set(':ssl_checked', '');
        tpl.set(':ssl_home_checked', '');
        tpl.set(':SSL_HOME', '');
        tpl.set(':SSL_CRT', '');
        tpl.set(':SSL_KEY', '');
        tpl.set(':SSL_CA', '');
    }

    tpl.set(':DNS_DOMAIN_ALSO', in_edit? 'hidden' : '');


    return tpl.finalize();
}

App.HTML.Build.db_form = function (options, id) {
    var in_edit = false;
    if (!App.Helpers.isEmpty(options)) {
        in_edit = true;
    }
    if ('undefined' == typeof App.Env.initialParams) {
        return alert('PLease wait a bit. Some background processes are not yet executed. Thank you for patience.');
    }
    var tpl = App.Templates.get('FORM', 'db');
    tpl.set(':source', options);
    tpl.set(':id', id || '');
    options = App.Helpers.evalJSON(options) || {};
    if (App.Helpers.isEmpty(options)) {
        tpl.set(':title', 'New database');
        tpl.set(':save_button', 'ADD');
        tpl.set(':DELETE_ACTION', '');
    } else {
        tpl.set(':title', 'Edit database "' + options.DB + '"');
        tpl.set(':save_button', 'SAVE');
        tpl.set(':DELETE_ACTION', App.Templates.get('DELETE_ACTION', 'general').finalize());
    }
    options = !App.Helpers.isEmpty(options) ? options : App.Empty.DB;
    if (in_edit == true) {
        options.PASSWORD = App.Settings.PASSWORD_IMMUTE;
    }
    tpl = App.HTML.setTplKeys(tpl, options, true);
    tpl = App.HTML.Build.db_selects(tpl, options);
    tpl = App.HTML.toggle_suspended_form(tpl, options);
    
    return tpl.finalize();
}

App.HTML.Build.cron_form = function (options, id) {
    if ('undefined' == typeof App.Env.initialParams) {
        return alert('PLease wait a bit. Some background processes are not yet executed. Thank you for patience.');
    }
    var tpl = App.Templates.get('FORM', 'cron');
    tpl.set(':source', options);
    tpl.set(':id', id || '');
    options = App.Helpers.evalJSON(options) || {};
    if (App.Helpers.isEmpty(options)) {
        tpl.set(':title', 'New cron job');
        tpl.set(':save_button', 'ADD');
        tpl.set(':DELETE_ACTION', '');
    } else {
        tpl.set(':title', 'Edit cron job');
        tpl.set(':save_button', 'SAVE');
        tpl.set(':DELETE_ACTION', App.Templates.get('DELETE_ACTION', 'general').finalize());
    }
    options = !App.Helpers.isEmpty(options) ? options : {
        DAY: '',
        MONTH: '',
        WDAY: '',
        HOUR: '',
        CMD: '',
        MIN: ''
    };
    tpl = App.HTML.setTplKeys(tpl, options);
    tpl = App.HTML.toggle_suspended_form(tpl, options);
    
    return tpl.finalize();
}

//
//	BUILD ENTRIES
//


App.HTML.Build.ip_entry = function (o) {
    var tpl = App.Templates.get('ENTRY', 'ip');
    tpl = App.HTML.setTplKeys(tpl, o);
    tpl = App.HTML.toggle_suspended_entry(tpl, o);
    
    return tpl.finalize();
}
App.HTML.Build.dns_entry = function (o) {
    var tpl = App.Templates.get('ENTRY', 'dns');
    tpl = App.HTML.setTplKeys(tpl, o);
    var ip = o.IP.split('.');
    tpl.set(':IP', ip.join('<span class="dot">.</span>'));
    tpl.set(':CHECKED', '');
    tpl.set(':TPL_VAL', o.TPL);
    tpl = App.HTML.toggle_suspended_entry(tpl, o);
    
    return tpl.finalize();
}
App.HTML.Build.user_entry = function (o, key) {
    var processed_data = {
        'NICKNAME': key,
        'U_DISK_PERCENTAGE':        o.U_DISK > 0        ? parseFloat(o.U_DISK / o.DISK_QUOTA * 100).toFixed(2)     : 1,
        'U_BANDWIDTH_PERCENTAGE':   o.U_BANDWIDTH > 0   ? parseFloat(o.U_BANDWIDTH / o.BANDWIDTH * 100).toFixed(2) : 1,
        'U_DISK':                   o.U_DISK == 0       ? 1 : App.Helpers.formatNumber(o.U_DISK),
        'U_BANDWIDTH':              o.U_BANDWIDTH == 0  ? 1 : App.Helpers.formatNumber(o.U_BANDWIDTH),
        'DISK_QUOTA_MEASURE':       App.Helpers.getMbHumanMeasure(o.DISK_QUOTA),
        'BANDWIDTH_MEASURE':        App.Helpers.getMbHumanMeasure(o.BANDWIDTH),
        'BANDWIDTH':                App.Helpers.getMbHuman(o.BANDWIDTH),
        'DISK_QUOTA':               App.Helpers.getMbHuman(o.DISK_QUOTA)
    };
    var o = $.extend(o, processed_data);
    o.U_DISK_PERCENTAGE_2 = o.U_DISK_PERCENTAGE;
    o.U_DISK_PERCENTAGE_3 = o.U_DISK_PERCENTAGE;
    o.BANDWIDTH_MEASURE_2 = o.BANDWIDTH_MEASURE;
    o.DISK_QUOTA_MEASURE_2 = o.DISK_QUOTA_MEASURE;
    o.U_BANDWIDTH_PERCENTAGE_2 = o.U_BANDWIDTH_PERCENTAGE;
    o.U_BANDWIDTH_PERCENTAGE_3 = o.U_BANDWIDTH_PERCENTAGE;
    var tpl = App.Templates.get('ENTRY', 'user');
    tpl = App.HTML.setTplKeys(tpl, o);
    tpl = App.HTML.toggle_suspended_entry(tpl, o);
    var ns = [];
    var ns_full = [];
    $([1, 2, 3, 4, 5, 6, 7, 8]).each(function (i, index) {
        var key = 'NS' + index;
        if ('undefined' != typeof o[key]) {
            if (o[key].trim() != '') {
                var tpl_ns = App.Templates.get('NS_RECORD', 'user');
                tpl_ns.set(':NAME', o[key]);
                var tpl_finalized = tpl_ns.finalize();
                ns_full[ns_full.length++] = tpl_finalized;
                if (i < App.Settings.USER_VISIBLE_NS) {
                    ns[ns.length++] = tpl_finalized;
                }
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
    tpl = App.HTML.Build.user_web_tpl(tpl, o);

    tpl.set(':REPORTS_ENABLED', o.REPORTS_ENABLED == 'yes' || o.REPORTS_ENABLED == 'on' ? 'enabled' : 'DISABLED');
    if (o.U_DISK_PERCENTAGE > 100) {
        var tpl_over = App.Templates.get('over_bar', 'general');
        var difference = parseInt(o.U_DISK_PERCENTAGE, 10) - 100;
        tpl_over.set(':OVER_PERCENTS', difference);
        tpl_over.set(':OVER_PERCENTS_2', difference);
        tpl.set(':OVER_BAR', tpl_over.finalize());
        tpl.set(':U_DISK_PERCENTAGE_3', 100);
        tpl.set(':OVER_DRAFT_VALUE', 'overdraft');
    } 
    else {
        tpl.set(':OVER_BAR', '');
        tpl.set(':OVER_DRAFT_VALUE', '');
    }
    if (o.U_BANDWIDTH_PERCENTAGE > 100) {
        var tpl_over = App.Templates.get('over_bar', 'general');
        var difference = parseInt(o.U_BANDWIDTH_PERCENTAGE, 10) - 100;
        tpl_over.set(':OVER_PERCENTS', difference);
        tpl_over.set(':OVER_PERCENTS_2', difference);
        tpl.set(':OVER_BAR_2', tpl_over.finalize());
        tpl.set(':U_BANDWIDTH_PERCENTAGE_3', 100);
        tpl.set(':OVER_DRAFT_VALUE_2', 'overdraft');
    } 
    else {
        tpl.set(':OVER_BAR_2', '');
        tpl.set(':OVER_DRAFT_VALUE_2', '');
    }
    return tpl.finalize();
}

App.HTML.Build.web_domain_entry = function (o, key) {
    var processed_data = {
        DOMAIN: key,
        'U_DISK_PERCENTAGE':        o.U_DISK > 0        ? parseFloat(o.U_DISK / App.Env.initialParams.user_data.DISK_QUOTA * 100).toFixed(2)     : 1,
        'U_BANDWIDTH_PERCENTAGE':   o.U_BANDWIDTH > 0   ? parseFloat(o.U_BANDWIDTH / App.Env.initialParams.user_data.BANDWIDTH * 100).toFixed(2) : 1,
        'U_DISK':                   o.U_DISK == 0       ? 1 : App.Helpers.formatNumber(o.U_DISK),
        'U_BANDWIDTH':              o.U_BANDWIDTH == 0  ? 1 : App.Helpers.formatNumber(o.U_BANDWIDTH),
        'DISK_QUOTA_MEASURE':       App.Helpers.getMbHumanMeasure(App.Env.initialParams.user_data.DISK_QUOTA),
        'BANDWIDTH_MEASURE':        App.Helpers.getMbHumanMeasure(App.Env.initialParams.user_data.BANDWIDTH),
        'BANDWIDTH':                App.Helpers.getMbHuman(App.Env.initialParams.user_data.BANDWIDTH),
        'DISK_QUOTA':               App.Helpers.getMbHuman(App.Env.initialParams.user_data.DISK_QUOTA),
        'SSL':                      (o.SSL_CRT == '' || o.SSL_KEY == '' || o.SSL != 'on') ? 'off' : 'on'
    };


    var o = $.extend(o, processed_data);
    o.U_DISK_PERCENTAGE_2 = o.U_DISK_PERCENTAGE;
    o.U_DISK_PERCENTAGE_3 = o.U_DISK_PERCENTAGE;
    o.BANDWIDTH_MEASURE_2 = o.BANDWIDTH_MEASURE;
    o.DISK_QUOTA_MEASURE_2 = o.DISK_QUOTA_MEASURE;
    o.U_BANDWIDTH_PERCENTAGE_2 = o.U_BANDWIDTH_PERCENTAGE;
    o.U_BANDWIDTH_PERCENTAGE_3 = o.U_BANDWIDTH_PERCENTAGE;
    var tpl = App.Templates.get('ENTRY', 'web_domain');
    tpl = App.HTML.setTplKeys(tpl, o);
	tpl = App.HTML.toggle_suspended_entry(tpl, o);
    if (o.STATS_LOGIN.trim() != '') {
        tpl.set(':STATS_AUTH', '+auth');
    } 
    else {
        tpl.set(':STATS_AUTH', '');
    }
    tpl.set(':DISK', App.Env.initialParams.PROFILE.BANDWIDTH);
    tpl.set(':BANDWIDTH', App.Env.initialParams.PROFILE.DISK);
        
    tpl = App.HTML.toggle_suspended_entry(tpl, o);
    
    return tpl.finalize();
}

App.HTML.Build.mail_entry = function (o, key) {
    var processed_data = {
        DOMAIN: key
    };
    var o = $.extend(o, processed_data);
    var tpl = App.Templates.get('ENTRY', 'mail');
    tpl = App.HTML.setTplKeys(tpl, o);
    tpl = App.HTML.toggle_suspended_entry(tpl, o);
    
    return tpl.finalize();
}
App.HTML.Build.db_entry = function (o, key) {
    var user_list_html = [];
    $(o['USERS']).each(function (i, o) {
        var tpl = App.Templates.get('USER_ITEM', 'db');
        tpl.set(':NAME', o);
        user_list_html.push(tpl.finalize());
    });
    var wrapper = App.Templates.get('USER_ITEMS_WRAPPER', 'db');
    wrapper.set(':CONTENT', user_list_html.done());
    var processed_data = {
        'USER_LIST': wrapper.finalize(),
        'USERS': o['USERS'].length || 0,
        'U_DISK_PERCENTAGE': o.U_DISK > 0 ? o.U_DISK / o.DISK * 100 : 0.01,
        'DISK_MEASURE': App.Helpers.getMbHumanMeasure(o.DISK),
        'DISK': App.Helpers.getMbHuman(o.DISK)
    };
    var o = $.extend(o, processed_data);
    var tpl = App.Templates.get('ENTRY', 'db');
    tpl = App.HTML.setTplKeys(tpl, o);
    tpl = App.HTML.toggle_suspended_entry(tpl, o);
    
    return tpl.finalize();
}

App.HTML.Build.cron_entry = function (o, key) {
    var processed_data = {
        DOMAIN: key
    };
    var o = $.extend(o, processed_data);
    var tpl = App.Templates.get('ENTRY', 'cron');
    tpl = App.HTML.setTplKeys(tpl, o);
    tpl = App.HTML.toggle_suspended_entry(tpl, o);
    
    return tpl.finalize();
}

//
//	GENERAL METHODS
//

App.HTML.Build.backup_list = function(backups)
{
	if (!backups || backups.length == 0) {
		return '<br /><br /><center><h1>Backups are not available</h1></center>';
	}
	
	var acc = [];
	$.each(backups, function(key) {
		var bckp = backups[key];
        // generated time calc        
        var generated_time = 1; //min
        bckp.RUNTIME > 60 ? generated_time = bckp.RUNTIME / 60 + ' h.' : generated_time += ' m.';
        
        var created_date = new Date(key);
		var tpl = App.Templates.get('ENTRY', 'backup');
		tpl.set(':CREATED_AT', key);
		tpl.set(':CREATED_AT_TIME', bckp.TIME);
        tpl.set(':GENERATION_TIME', generated_time);
        tpl.set(':OWNER', App.Env.initialParams.auth_user.uid.uid);
		tpl.set(':CREATED_AT_WDAY', App.Constants.KEY.WDAYS[created_date.getDay()]);
        tpl.set(':SIZE', App.Helpers.getMbHuman(bckp.SIZE) + ' ' + App.Helpers.getMbHuman(bckp.SIZE, true));
		acc[acc.length++] = tpl.finalize()
	});
	
	var wrap = App.Templates.get('WRAPPER', 'backup');
	wrap.set(':CONTENT', acc.done());
	
	return wrap.finalize();
}

App.HTML.Build.stats_list = function(stats)
{
	if (!stats || stats.length == 0) {
		return '<br /><br /><center><h1>Stats are not available</h1></center>';
	}
	
	var acc = [];
	$.each(stats, function(key) {
		var stat = stats[key];
        
        var tpl = App.Templates.get('ENTRY', 'stats');
        tpl.set(':HEADER', stat.TITLE);
        tpl.set(':IMG_SRC', stat.SRC);
        acc[acc.length++] = tpl.finalize()
	});
	
	var wrap = App.Templates.get('WRAPPER', 'backup');
	wrap.set(':CONTENT', acc.done());
	
	return wrap.finalize();
}

//
//	HANDY METHODS
//

App.HTML.toggle_suspended_form = function(tpl, options)
{
	if (App.Constants.SUSPENDED_YES == options.SUSPEND) {
		tpl.set(':SUSPENDED_CHECKED', 'checked="checked"');
        tpl.set(':FORM_SUSPENDED', 'form-suspended');
        tpl.set(':SUSPENDED_VALUE', 'on');
    } else {
		tpl.set(':SUSPENDED_CHECKED', '');
        tpl.set(':FORM_SUSPENDED', '');
        tpl.set(':SUSPENDED_VALUE', 'off');
    }
        
    return tpl;
}

App.HTML.toggle_suspended_entry = function(tpl, options)
{
	if (App.Constants.SUSPENDED_YES == options.SUSPEND) {
        var sub_tpl = App.Templates.get('SUSPENDED_TPL_SUSPENDED', 'general');
        tpl.set(':SUSPENDED_CLASS', 'inactive-row');
        
    } else {
        var sub_tpl = App.Templates.get('SUSPENDED_TPL_NOT_SUSPENDED', 'general');
        tpl.set(':SUSPENDED_CLASS', '');
    }
    tpl.set(':SUSPENDED_TPL', sub_tpl.finalize());
    
    return tpl;
}

App.HTML.makeDatabases = function (databases) {
    var acc = [];
    $(databases).each(function (i, o) {
        var tpl = App.Templates.get('database', 'database');
        tpl.set(':name', o.Database);
        tpl.set(':db_name', o.Database);
        acc[acc.length++] = tpl.finalize();
    });
    return acc.done();
}
App.HTML.makeDbTableList = function (data) {
    var acc = [];
    $(data).each(function (i, o) {
        var name = App.Helpers.getFirstValue(o);
        var tpl = App.Templates.get('database_table', 'database');
        tpl.set(':name', name);
        tpl.set(':table_name', name);
        acc[acc.length++] = tpl.finalize();
    });
    return acc.done();
}
App.HTML.makeDbFieldsList = function (data) {
    var acc = [];
    $(data).each(function (i, o) {
        var details = [o['Type'], o['Null'], o['Key'], o['Default'], o['Extra']].join(' ');
        var tpl = App.Templates.get('database_field', 'database');
        tpl.set(':name', o.Field);
        tpl.set(':details', details);
        acc[acc.length++] = tpl.finalize();
    });
    return acc.done();
}

App.HTML.Build.options = function (initial, default_value) {
    var opts = [];
    $.each(initial, function (key) {
        var selected = key == default_value ? 'selected="selected"' : '';
        opts[opts.length++] = '<option value="' + key + '" ' + selected + '>' + initial[key] + '</options>';
    });
    return opts.join('');
}


App.HTML.Build.dns_records = function (records) {
    var acc = [];
    $.each(records, function (i, record) {
        var record = records[i];
        var tpl = App.HTML.Build.dns_subrecord(record);
        acc[acc.length++] = tpl.finalize();
    });
    return acc.done();
}
App.HTML.Build.dns_subrecord = function (record) {
    var tpl = App.Templates.get('SUBENTRY', 'dns');
    tpl.set(':RECORD', record.RECORD || '');
    tpl.set(':RECORD_VALUE', record.RECORD_VALUE || '');
    tpl.set(':RECORD_ID', record.RECORD_ID || '');
    tpl.set(':RECORD_TYPE', App.HTML.Build.options(App.Env.initialParams.DNS.record.RECORD_TYPE, (record.RECORD_TYPE || -1)));
    return tpl;
}
App.HTML.Build.ssl_key_file = function () {
    return '<iframe src="' + App.Helpers.getUploadUrl() + '?action=show&type=key" width="500px;" height="53px;" framevorder="0" scroll="no">..</iframe>';
}
App.HTML.Build.ssl_cert_file = function () {
    return '<iframe src="' + App.Helpers.getUploadUrl() + '?action=show&type=cert" width="500px;" height="53px;" framevorder="0" scroll="no">..</iframe>';
}
App.HTML.Build.ssl_ca_file = function () {
    return '<iframe src="' + App.Helpers.getUploadUrl() + '?action=show&type=ca" width="500px;" height="53px;" framevorder="0" scroll="no">..</iframe>';
}
App.HTML.Build.user_selects = function (tpl, options) {
    var acc = [];
    var pkg = App.Env.initialParams.USERS.PACKAGE;
    $.each(pkg, function (val) {
        var tpl = App.Templates.get('select_option', 'general');
        tpl.set(':VALUE', val);
        tpl.set(':TEXT', pkg[val]);
        tpl.set(':SELECTED', val == options.PACKAGE ? 'selected="selected"' : '');
        acc[acc.length++] = tpl.finalize();
    });
    tpl.set(':PACKAGE_OPTIONS', acc.done());
    acc = [];
    var shell = App.Env.initialParams.USERS.SHELL;
    $.each(shell, function (val) {
        var tpl = App.Templates.get('select_option', 'general');
        tpl.set(':VALUE', val);
        tpl.set(':TEXT', shell[val]);
        tpl.set(':SELECTED', val == options.SHELL ? 'selected="selected"' : '');
        acc[acc.length++] = tpl.finalize();
    });
    tpl.set(':SHELL_OPTIONS', acc.done());
    return tpl;
}
App.HTML.Build.db_selects = function (tpl, options) {
    var acc = [];
    var items = App.Env.initialParams.DB.TYPE;
    $.each(items, function (val) {
        var tpl = App.Templates.get('select_option', 'general');
        tpl.set(':VALUE', val);
        tpl.set(':TEXT', items[val]);
        tpl.set(':SELECTED', val == options.TYPE ? 'selected="selected"' : '');
        acc[acc.length++] = tpl.finalize();
    });
    tpl.set(':TYPE_OPTIONS', acc.done());

    var obj = App.Env.initialParams.DB.HOST;
    var opts = App.HTML.Build.options(obj, options.HOST);
    tpl.set(':HOST_OPTIONS', opts);


    var obj = App.Env.initialParams.DB.CHARSET;
    var opts = App.HTML.Build.options(obj, options.CHARSET);
    tpl.set(':CHARSET_OPTIONS', opts);

    return tpl;
}
App.HTML.Build.ip_selects = function (tpl, options) {
    var users = App.Env.initialParams.IP.OWNER;
    var opts = App.HTML.Build.options(users, options.OWNER);
    tpl.set(':owner_options', opts);
    var opts = App.HTML.Build.options(App.Env.initialParams.IP.STATUSES, options.STATUS);
    tpl.set(':status_options', opts);
    var opts = App.HTML.Build.options(App.Env.initialParams.IP.INTERFACES, options.INTERFACE);
    tpl.set(':interface_options', opts);
    return tpl;
}
App.HTML.Build.dns_selects = function (tpl, options) {
    try {
        var obj = {};
        $.each(App.Env.initialParams.DNS.TPL, function (key) {
            obj[key] = key;
        });

        var opts = App.HTML.Build.options(obj, options.TPL);
        tpl.set(':TPL', opts);
//        tpl.set(':TPL_DEFAULT_VALUE', options.TPL || App.Helpers.getFirstKey(obj));

    } catch (e) {
        return tpl;
    }
    return tpl;
}
App.HTML.Build.web_domain_selects = function (tpl, options) {
    try {
        var obj = App.Env.initialParams.WEB_DOMAIN.IP;
        var opts = App.HTML.Build.options(obj, options.IP);
        tpl.set(':IP_OPTIONS', opts);
        var obj = {};
        $.each(App.Env.initialParams.WEB_DOMAIN.TPL, function (key) {
            obj[key] = key;
        });
        var opts = App.HTML.Build.options(obj, options.TPL);
        tpl.set(':TPL_OPTIONS', opts);
        var obj = App.Env.initialParams.WEB_DOMAIN.STAT;
        var opts = App.HTML.Build.options(obj, options.STAT);
        tpl.set(':STAT_OPTIONS', opts);
    } catch (e) {
        return tpl;
    }
    return tpl;
}
App.HTML.Build.user_web_tpl = function (tpl, o) {
    var wt = [];
    var wt_full = [];
    var templates = o.WEB_TPL;
    templates = templates.split(',');
    if (templates.length == 0) {
        templates = templates.split(' ');
    }
    $(templates).each(function (i, web_tpl) {
        var tpl_wt = App.Templates.get('WEB_TPL', 'user');
        tpl_wt.set(':NAME', web_tpl);
        var tpl_finalized = tpl_wt.finalize();
        wt_full[wt_full.length++] = tpl_finalized;
        if (i < App.Settings.USER_VISIBLE_WEB_TPL) {
            wt[wt.length++] = tpl_finalized;
        }
    });
    if (templates.length <= App.Settings.USER_VISIBLE_NS) {
        tpl.set(':WEB_TPL', wt.done());
    } else {
        var wt_custom = App.Templates.get('WEB_TPL_MINIMIZED', 'user');
        wt_custom.set(':WEB_TPL_MINI', wt.done());
        wt_custom.set(':WEB_TPL_FULL', wt_full.done());
        wt_custom.set(':MORE_NUMBER', Math.abs(App.Settings.USER_VISIBLE_NS - wt_full.length));
        tpl.set(':WEB_TPL', wt_custom.finalize());
    }
    return tpl;
}
