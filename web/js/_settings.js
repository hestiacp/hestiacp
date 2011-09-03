App.Env.lang = 'EN';
App.i18n.EN = {};
App.i18n.EN.incorrect_ip = 'Incorrect ip';
App.i18n.EN.confirm = 'Are you sure?';
App.i18n.getMessage = function(key) 
{
    return 'undefined' != typeof App.i18n[App.Env.lang][key] ? App.i18n[App.Env.lang][key] : '';
}


// Constants
App.Constants.IP_FORM_ID            = 'ip-form';
App.Constants.DNS_FORM_ID           = 'dns-form';
App.Constants.USER_FORM_ID          = 'user-form';
App.Constants.WEB_DOMAIN_FORM_ID    = 'web_domain-form';
App.Constants.DB_FORM_ID            = 'db-form';
App.Constants.CRON_FORM_ID          = 'cron-form';
App.Constants.IP                    = 'IP';
App.Constants.DNS                   = 'DNS';
App.Constants.SUSPENDED_YES         = 'yes';
App.Constants.DNS_TEMPLATES         = {'default': 'Default'};

// Settings
App.Settings.USER_VISIBLE_NS    = 2;
App.Settings.NS_MIN             = 2;
App.Settings.NS_MAX             = 8;
App.Settings.ajax_url           = 1;
App.Settings.uri                = location.href.replace('index.html', '');
App.Settings.popup_conf         = { 'centered' : true, 'bgcolor' : '#FF0000', 'lightboxSpeed' : 'fast', 'destroyOnClose': true };


// Messages
App.Messages.total_dns_records = {single: 'total record', plural: 'total records'};
App.Messages.get = function(key, plural) {
    if ('undefined' != typeof App.Messages[key]) {
        return plural ? App.Messages[key].plural : App.Messages[key].single;
    }
}

// Empty
App.Empty = {};
App.Empty.USER = {'CONTACT':'', 'PASSWORD':'','LOGIN_NAME':'','LNAME':'', 'FNAME':'','NS1':'','NS2':'','NS3':'','NS4':'','NS5':'','NS6':'','NS7':'','NS8':''};

App.Settings.getMethodName = function(action)
{
    var type = '';
    var method = '';
    // TYPE NAME
    switch (App.Env.world) 
    {
        case App.Constants.DNS: 
            type = 'DNS'
            break;
        default:
            type = App.Env.world;
            break;
    }
    // METHOD NAME
    switch (action) 
    {
        case 'update': 
            method = 'change';
            break;
        default:
            method = action;
            break;
    }
    
    return type + '.' + method;
}
