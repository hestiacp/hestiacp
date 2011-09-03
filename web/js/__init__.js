$(document).ready(function(){
    try{
        App.Utils.detectBrowser();
        
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
        App.Ref.init();

        //App.View.start();
        App.Core.listen();
        App.Core.initMenu();
        App.Helpers.liveValidate();

    }catch(e){
        fb.error(e);
    }
});

