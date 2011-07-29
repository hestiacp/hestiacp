$(document).ready(function(){
    try{
        App.Utils.detectBrowser();
        
        App.Env.world = 'USER';
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

