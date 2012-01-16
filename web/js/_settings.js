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

App.Constants.KEY                   = {};
App.Constants.KEY.ESC               = 27;
App.Constants.KEY.ENTER             = 13;
App.Constants.KEY.SPACE             = 32;
App.Constants.KEY.BACKSPACE         = 8;
App.Constants.KEY.UP                = 38;
App.Constants.KEY.DOWN              = 40;
App.Constants.KEY.CODED_NAME        = {'27':'ESC', '13':'ENTER','32':'SPACE','8':'BACKSPACE','38':'UP','40':'DOWN'};
App.Constants.KEY.WDAYS             = {'0':'Sunday', '1':'Monday','2':'Tuesday','3':'Wednesday','4':'Thursday','5':'Friday', '6': 'Saturday'};

// Settings
App.Settings.PSW_MIN_LEN            = 6;
App.Settings.FIELD_MAX_LEN          = 32;
App.Settings.DAY_MAX_LEN            = 2;
App.Settings.MONTH_MAX_LEN          = 2;
App.Settings.WDAY_MAX_LEN           = 2;
App.Settings.HOURS_MAX_LEN          = 2;
App.Settings.MINUTE_MAX_LEN         = 2;
App.Settings.USER_VISIBLE_NS        = 2;
App.Settings.USER_VISIBLE_WEB_TPL   = 2;
App.Settings.NS_MIN                 = 2;
App.Settings.NS_MAX                 = 8;
App.Settings.ajax_url               = 1;
App.Settings.uri                    = location.href.replace('index.html', '');
App.Settings.popup_conf             = { 'centered' : true, 'bgcolor' : '#FF0000', 'lightboxSpeed' : 'fast', 'destroyOnClose': true };

App.Settings.PASSWORD_IMMUTE        = '********';

App.Settings.AJAX_SYNCRONOUS = ['MAIN.getInitial'];

// Messages
App.Messages.total_dns_records = {single: 'total record', plural: 'total records'};
App.Messages.get = function(key, plural) {
    if ('undefined' != typeof App.Messages[key]) {
        return plural ? App.Messages[key].plural : App.Messages[key].single;
    }
}

// Imutable
App.Settings.Imutable               = {};
App.Settings.Imutable.USER          = ['LOGIN_NAME'];
App.Settings.Imutable.WEB_DOMAIN    = ['DOMAIN'];
App.Settings.Imutable.DB            = ['DB', 'TYPE', 'USER', 'CHARSET', 'HOST'];
App.Settings.Imutable.DNS           = ['DNS_DOMAIN'];
App.Settings.Imutable.IP            = ['IP_ADDRESS', 'NETMASK', 'INTERFACE'];


// Empty
App.Empty = {};
App.Empty.USER       = {'CONTACT':'', 'PASSWORD':'','LOGIN_NAME':'','LNAME':'', 'FNAME':'','NS1':'','NS2':'','NS3':'','NS4':'','NS5':'','NS6':'','NS7':'','NS8':''};
App.Empty.WEB_DOMAIN = {'CONTACT':'', 'PASSWORD':'','LOGIN_NAME':'','NS':'', 'DOMAIN':'','SSL_CERT':'','SSL_KEY':'','SSL_HOME':'','STATS_PASSWORD':'','STATS_LOGIN':'','ALIAS':'', 'SSL_CA':'', 'SSL_CRT':'','NGINX_EXT':''};
App.Empty.DB         = {'DB':'', 'USER':'','FORM':'', 'PASSWORD': ''};

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
