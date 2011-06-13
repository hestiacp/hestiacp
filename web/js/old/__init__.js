$(document).ready(function(){
    try{
    App.Utils.detectBrowser();
    
    App.Env.world = 'DNS';
    App.Pages.init();
    
    App.Ref.init();
        
    App.View.start();
    App.Core.listen();
    App.Core.initMenu();
    
    }catch(e){
        fb.error(e);
    }
});

