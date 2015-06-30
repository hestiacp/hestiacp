// Init kinda namespace object
var VE = { // Vesta Events object
    core: {}, // core functions
    callbacks: { // events callback functions
        click: {},
        mouseover: {},
        mouseout: {},
        keypress: {}
    },
    helpers: {}, // simple handy methods
    tmp: {
        sort_par: 'sort-name',
        sort_direction: -1,
        sort_as_int: 0,
        form_changed: 0,
        search_activated: 0,
        search_display_interval: 0,
        search_hover_interval: 0
    }
};

/*
 * Main method that invokes further event processing
 * @param root is root HTML DOM element that. Pass HTML DOM Element or css selector
 * @param event_type (eg: click, mouseover etc..)
 */
VE.core.register = function(root, event_type) {
    var root = !root ? 'body' : root; // if elm is not passed just bind events to body DOM Element
    var event_type = !event_type ? 'click' : event_type; // set event type to "click" by default
    $(root).bind(event_type, function(evt) {
        var elm = $(evt.target);
        VE.core.dispatch(evt, elm, event_type); // dispatch captured event
    });
}

/*
 * Dispatch event that was previously registered
 * @param evt related event object
 * @param elm that was catched
 * @param event_type (eg: click, mouseover etc..)
 */
VE.core.dispatch = function(evt, elm, event_type) {
    if ('undefined' == typeof VE.callbacks[event_type]) {
        return VE.helpers.warn('There is no corresponding object that should contain event callbacks for "'+event_type+'" event type');
    }
    // get class of element
    var classes = $(elm).attr('class');
    // if no classes are attached, then just stop any further processings
    if (!classes) {
        return; // no classes assigned
    }
    // split the classes and check if it related to function
    $(classes.split(/\s/)).each(function(i, key) {
        VE.callbacks[event_type][key] && VE.callbacks[event_type][key](evt, elm);
    });
}

//
//  CALLBACKS
//



/*
 * Suspend action
 */
VE.callbacks.click.do_suspend = function(evt, elm) {
     var ref = elm.hasClass('actions-panel') ? elm : elm.parents('.actions-panel');
     var url = $('input[name="suspend_url"]', ref).val();
     var dialog_elm = ref.find('.confirmation-text-suspention');
     VE.helpers.createConfirmationDialog(dialog_elm, '', url);
}

/*
 * Unsuspend action
 */
VE.callbacks.click.do_unsuspend = function(evt, elm) {
     var ref = elm.hasClass('actions-panel') ? elm : elm.parents('.actions-panel');
     var url = $('input[name="unsuspend_url"]', ref).val();
     var dialog_elm = ref.find('.confirmation-text-suspention');
     VE.helpers.createConfirmationDialog(dialog_elm, '', url);
}

/*
 * Delete action
 */
VE.callbacks.click.do_delete = function(evt, elm) {
     var ref = elm.hasClass('actions-panel') ? elm : elm.parents('.actions-panel');
     var url = $('input[name="delete_url"]', ref).val();
     var dialog_elm = ref.find('.confirmation-text-delete');
     VE.helpers.createConfirmationDialog(dialog_elm, '', url);
}


/*
 * Create dialog box on the fly
 * @param elm Element which contains the dialog contents
 * @param dialog_title 
 * @param confirmed_location_url URL that will be redirected to if user hit "OK"
 * @param custom_config Custom configuration parameters passed to dialog initialization (optional)
 */
VE.helpers.createConfirmationDialog = function(elm, dialog_title, confirmed_location_url, custom_config) {
    var custom_config = !custom_config ? {} : custom_config;
    var config = {
        modal: true,
        autoOpen: true,
        width: 360,
        title: dialog_title,
        close: function() {
            $(this).dialog("destroy");
        },
        buttons: {
            "OK": function(event, ui) {
                 location.href = confirmed_location_url;
            },
            "Cancel": function() {
                $(this).dialog("close");
                $(this).dialog("destroy");
            }
        }
    }
    config = $.extend(config, custom_config);
    var reference_copied = $(elm).clone();
    $(reference_copied).dialog(config);
}

/*
 * Simple debug output
 */
VE.helpers.warn = function(msg) {
    alert('WARNING: ' + msg);
}

VE.helpers.extendPasswordFields = function() {
    var references = ['.password'];
    
    $(document).ready(function() {
        $(references).each(function(i, ref) {
            VE.helpers.initAdditionalPasswordFieldElements(ref);
        });
    });
}

VE.helpers.initAdditionalPasswordFieldElements = function(ref) {
    var enabled = $.cookie('hide_passwords') == '1' ? true : false;
    if (enabled) {
        VE.helpers.hidePasswordFieldText(ref);
    }

    $(ref).prop('autocomplete', 'off');

    var enabled_html = enabled ? '' : 'show-passwords-enabled-action';
    var html = '<span class="hide-password"><img class="toggle-psw-visibility-icon ' + enabled_html + '" onClick="VE.helpers.toggleHiddenPasswordText(\'' + ref + '\', this)" src="/images/toggle_password.png" /></span>';
    $(ref).after(html);
}

VE.helpers.hidePasswordFieldText = function(ref) {
    $.cookie('hide_passwords', '1', { expires: 365, path: '/' });
    $(ref).prop('type', 'password');
}

VE.helpers.revealPasswordFieldText = function(ref) {
    $.cookie('hide_passwords', '0', { expires: 365, path: '/' });
    $(ref).prop('type', 'text');
}

VE.helpers.toggleHiddenPasswordText = function(ref, triggering_elm) {
    $(triggering_elm).toggleClass('show-passwords-enabled-action');

    if ($(ref).prop('type') == 'text') {
        VE.helpers.hidePasswordFieldText(ref);
    }
    else {
        VE.helpers.revealPasswordFieldText(ref);
    }
}

VE.helpers.refresh_timer = {
    speed: 50,
    degr: 180,
    right: 0,
    left: 0,
    periodical: 0,
    first: 1,

    start: function(){
        this.periodical = setInterval(function(){VE.helpers.refresh_timer.turn()}, this.speed);
    },

    stop: function(){
        clearTimeout(this.periodical);
    },

    turn: function(){
        this.degr += 1;

        if (this.first && this.degr >= 361){
            this.first = 0;
            this.degr = 180;
            this.left.css({'-webkit-transform': 'rotate(180deg)'});
            this.left.css({'transform': 'rotate(180deg)'});
            this.left.children('.loader-half').addClass('dark');
        }
        if (!this.first && this.degr >= 360){
            this.first = 1;
            this.degr = 180;
            this.left.css({'-webkit-transform': 'rotate(0deg)'});
            this.right.css({'-webkit-transform': 'rotate(180deg)'});
            this.left.css({'transform': 'rotate(0deg)'});
            this.right.css({'transform': 'rotate(180deg)'});
            this.left.children('.loader-half').removeClass('dark');

            this.stop();
            location.reload();
        }

        if (this.first){
            this.right.css({'-webkit-transform': 'rotate('+this.degr+'deg)'});
            this.right.css({'transform': 'rotate('+this.degr+'deg)'});
        }
        else{
            this.left.css({'-webkit-transform': 'rotate('+this.degr+'deg)'});
            this.left.css({'transform': 'rotate('+this.degr+'deg)'});
        }
    }
}



VE.helpers.extendPasswordFields();
