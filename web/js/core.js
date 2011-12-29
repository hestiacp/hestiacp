//
//  CORE
//
App.Core.listen = function() 
{    
    fb.log('start listening');
    $(document).bind('click', function(evt) { 
        App.Helpers.handleItemsRegisteredInBackground(evt);  
        var elm    = $(evt.target);        
        var action = $(elm).attr('class');
        if (!action) {
            return fb.log('No action passed');
        } 
        action = action.split('do_action_');
        if (action.length < 2) {
            if (elm.hasClass('check-this')) {
                var ref = $(elm).parents('.row');
                if (ref.hasClass('checked-row')) {
                    ref.removeClass('checked-row');
                    App.Tmp[App.Env.world + '_selected_records'] -= 1;
                }
                else {
                    ref.addClass('checked-row');
                    App.Tmp[App.Env.world + '_selected_records'] += 1;
                }
                
                if (App.Tmp[App.Env.world + '_selected_records'] <= 0) {
                    App.Tmp[App.Env.world + '_selected_records'] = 0; // if number is negative
                    $('#batch-processor .selector-title').html('NONE');
                    $('.styled.do_action_toggle_batch_selector.style-applied').attr('checked', false);
                    $('.checkbox.do_action_toggle_batch_selector').css('background-position', '0 0');
                }
                else {
                    $('#batch-processor .selector-title').html(App.Tmp[App.Env.world + '_selected_records'] + ' SELECTED');
                    $('.styled.do_action_toggle_batch_selector.style-applied').attr('checked', true);
                    $('.checkbox.do_action_toggle_batch_selector').css('background-position', '0 -50px');
                }
            }    
            return; // no action found attached to the dom object
        }
        try {            
            action_with_params = action[1].split(' ');// retrieve the action itself
            action = action_with_params[0];            
            App.Core.__CALL__(evt, action);// Call the action
        }
        catch(e) {
            fb.error(e);
        }
    });
    
    $(document).bind('keyup', function(evt) {
        fb.log(evt.keyCode);
        if ('undefined' != typeof App.Constants.KEY.CODED_NAME[evt.keyCode]) {
            var method_name = 'keyboard_' + App.Constants.KEY.CODED_NAME[evt.keyCode];
            App.Helpers[method_name] && App.Helpers[method_name](evt);
        }
    });
}

/**
 * Action caller
 * if no action registered, execution will stop
 */
App.Core.__CALL__ = function(evt, action)
{
    if ('undefined' == typeof App.Actions[action]) {
        return alert('No action registered for: "'+action+'". Stop propagation');
    }
    else{
        return App.Actions[action](evt);
    }

    
}

App.Core.initMenu = function()
{
    $('.section').bind('click', function(evt) {
        var elm = $(evt.target);
        !elm.hasClass('section') ? elm = elm.parents('.section') : -1;
        if (App.Env.world != elm.attr('id')) {
            App.Env.world  = elm.attr('id');            
            App.Pages.init();            
            fb.warn('Switch page to: ' + App.Env.world);
        }
    });
}


