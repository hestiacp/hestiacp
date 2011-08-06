App.Env.lang = 'EN';
App.i18n.EN = {};
App.i18n.EN.incorrect_ip = 'Incorrect ip';
App.i18n.EN.confirm = 'Are you sure?';
App.i18n.getMessage = function(key) 
{
    return 'undefined' != typeof App.i18n[App.Env.lang][key] ? App.i18n[App.Env.lang][key] : '';
}



// Constants
App.Constants.IP_FORM_ID          = 'ip-form';
App.Constants.DNS_FORM_ID         = 'dns-form';
App.Constants.USER_FORM_ID        = 'user-form';
App.Constants.WEB_DOMAIN_FORM_ID  = 'web_domain-form';
App.Constants.DB_FORM_ID          = 'db-form';

App.Settings.ajax_url = 1;
App.Settings.uri = location.href.replace('index.html', '');
App.Settings.popup_conf = { 'centered' : true, 'bgcolor' : '#FF0000', 'lightboxSpeed' : 'fast', 'destroyOnClose': true };

App.Constants.SUSPENDED_YES = 'yes';

App.Constants.IP = 'IP';
App.Constants.DNS = 'DNS';

App.Constants.DNS_TEMPLATES = {'default': 'Default'};

App.Messages.total_dns_records = {single: 'total record', plural: 'total records'};

App.Messages.get = function(key, plural) {
    if ('undefined' != typeof App.Messages[key]) {
        return plural ? App.Messages[key].plural : App.Messages[key].single;
    }
}


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
