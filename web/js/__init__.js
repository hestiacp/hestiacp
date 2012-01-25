App.Ajax.request('MAIN.about', {}, function(reply) {
    if (reply) {
        App.Settings.VestaAbout.company_name  = reply.data.company_name;
        App.Settings.VestaAbout.company_email = reply.data.company_email;
        App.Settings.VestaAbout.version       = reply.data.version;
        App.Settings.VestaAbout.version_name  = reply.data.version_name;
    }
});


$('document').ready(function() {
    try {
        App.Utils.detectBrowser();
        App.Ref.init();    
        
        //App.Env.world = 'USER';
        // Disabled cookie tab restoring. Enable if needed
        if ('undefined' != typeof App.Tmp.loadTAB) {
            App.Env.world = App.Tmp.loadTAB;
        }
        
        if ('undefined' == typeof App.Tmp.loadTAB && cookieEnabled()) {
            var tab = getCookie('tab');
            if (null != tab && $.inArray(tab, App.Constants.TABS)) {
                App.Env.world = tab;
            }
            else {
                App.Env.world = App.Constants.TABS[0];
            }
        }
        
        App.Pages.init();        
        App.Core.listen();
        App.Core.initMenu();
        App.Helpers.liveValidate();
        $(document).bind('submit', function(evt) {
           evt.preventDefault(); 
        });
    }
    catch(e) {
        fb.error(e);
    }
});

