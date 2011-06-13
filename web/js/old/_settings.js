// Constants
App.Constants.IP_FORM_ID = 'ip-form';
App.Constants.DNS_FORM_ID = 'dns-form';

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