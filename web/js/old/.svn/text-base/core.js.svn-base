//
// REFLECTOR
//
App.Core.action_reflector = {
    'new_entry': App.Actions.newForm,
    'cancel_form': App.Actions.cancelForm,
    'save_form': App.Actions.saveForm,//App.Pages.IP.saveIpForm,
    'remove': App.Actions.remove,//App.Pages.IP.deleteIp,
           
    'cancel_dns_form': App.Pages.DNS.closeForm,
    'save_dns_form': App.Pages.DNS.saveForm,
    
    'edit': App.Actions.edit,
    'embed_subform': App.Actions.embedSubform,
    
    'form_help': App.Actions.showFormHelp,
    'entry_help': App.Actions.showEntryHelp,
    
    'close_popup': App.View.closePopup
};
//
//  CORE
//
App.Core.listen = function(){
    fb.log('start listening');
    $(document).bind('click', function(evt){
        //App.Pages.IP.customListen && App.Pages.IP.customListen(evt);
        var elm = $(evt.target);
        fb.log(elm);
        var action = $(elm).attr('className').split('do_action_');
        if(action.length < 2){
            if (elm.hasClass('check-this')) {
                var ref = $(elm).parents('.row');
                ref.hasClass('checked-row') ? ref.removeClass('checked-row') : ref.addClass('checked-row');
            }
    
            return; // no action found attached to the dom object
        }
        try{
            // retrieve the action itself
            action_with_params = action[1].split(' ');
            action = action_with_params[0];
            params = elm.find('.prm-'+action).value || null;        
            // TODO: filter params here
            // Call the action
            App.Core.__CALL__(evt, action, params);
        }catch(e){
            fb.error(e)
        }  
    });
}

/**
 * Action caller
 * if no action registered, execution will stop
 */
App.Core.__CALL__ = function(evt, action, params){
    if('undefined' == typeof App.Core.action_reflector[action]){
        return fb.warn('No action registered for: "'+action+'". Stop propagation');
    }else{
        return App.Core.action_reflector[action](evt, params);
    }

    
}

App.Core.initMenu = function(){
    $('.section').bind('click', function(evt){
        var elm = $(evt.target);
        !elm.hasClass('section') ? elm = elm.parents('.section') : -1;
        if(App.Env.world != elm.attr('id')){
            App.Env.world = elm.attr('id');            
            App.Pages.init();            
            fb.warn('Switch page to: ' + App.Env.world);
        }
    });
}


