// Init kinda namespace object
var VE = { // Hestia Events object
    core: {}, // core functions
    navigation: {
        state: {
            active_menu: 1,
            menu_selector: '.l-stat__col',
            menu_active_selector: '.l-stat__col--active'
        }
    }, // menu and element navigation functions
    notifications: {},
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

VE.callbacks.click.do_servicerestart = function(evt, elm) {
    var ref = elm.hasClass('actions-panel') ? elm : elm.parents('.actions-panel');
    var url = $('input[name="servicerestart_url"]', ref).val();
    var dialog_elm = ref.find('.confirmation-text-servicerestart');
    VE.helpers.createConfirmationDialog(dialog_elm, '', url);
}

VE.callbacks.click.do_servicestop = function(evt, elm) {
    var ref = elm.hasClass('actions-panel') ? elm : elm.parents('.actions-panel');
    var url = $('input[name="servicestop_url"]', ref).val();
    var dialog_elm = ref.find('.confirmation-text-servicestop');
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
        //autoOpen: true,
        resizable: false,
        width: 360,
        title: dialog_title,
        close: function() {
            $(this).dialog("destroy");
        },
        buttons: {
            "OK": function(event, ui) {
                 location.href = confirmed_location_url;
            },
            Cancel: function() {
                $(this).dialog("close");
            }
        },
        create:function () {
            $(this).closest(".ui-dialog")
                .find(".ui-button:first")
                .addClass("submit");
            $(this).closest(".ui-dialog")
                .find(".ui-button")
                .eq(1) // the first button
                .addClass("cancel");
        }
    }


    var reference_copied = $(elm[0]).clone();
    console.log(reference_copied);
    config = $.extend(config, custom_config);
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
    var html = '<span class="hide-password"><i class="toggle-psw-visibility-icon fas fa-eye-slash ' + enabled_html + '" onClick="VE.helpers.toggleHiddenPasswordText(\'' + ref + '\', this)"></i></span>';
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

VE.navigation.enter_focused = function() {
    if($('.units').hasClass('active')){
        location.href=($('.units.active .l-unit.focus .actions-panel__col.actions-panel__edit a').attr('href'));
    } else {
        if($(VE.navigation.state.menu_selector + '.focus a').attr('href')){
            location.href=($(VE.navigation.state.menu_selector + '.focus a').attr('href'));
        }
    }
}

VE.navigation.move_focus_left = function(){
    var index = parseInt($(VE.navigation.state.menu_selector).index($(VE.navigation.state.menu_selector+'.focus')));
    if(index == -1)
        index = parseInt($(VE.navigation.state.menu_selector).index($(VE.navigation.state.menu_active_selector)));

    if($('.units').hasClass('active')){
        $('.units').removeClass('active');
        if(VE.navigation.state.active_menu == 0){
            $('.l-menu').addClass('active');
        } else {
            $('.l-stat').addClass('active');
        }
        index++;
    }

    $(VE.navigation.state.menu_selector).removeClass('focus');

    if(index > 0){
        $($(VE.navigation.state.menu_selector)[index-1]).addClass('focus');
    } else {
        VE.navigation.switch_menu('last');
    }
}

VE.navigation.move_focus_right = function(){
    var max_index = $(VE.navigation.state.menu_selector).length-1;
    var index = parseInt($(VE.navigation.state.menu_selector).index($(VE.navigation.state.menu_selector+'.focus')));
    if(index == -1)
        index = parseInt($(VE.navigation.state.menu_selector).index($(VE.navigation.state.menu_active_selector))) || 0;
    $(VE.navigation.state.menu_selector).removeClass('focus');

    if($('.units').hasClass('active')){
        $('.units').removeClass('active');
        if(VE.navigation.state.active_menu == 0){
            $('.l-menu').addClass('active');
        } else {
            $('.l-stat').addClass('active');
        }
        index--;
    }

    if(index < max_index){
        $($(VE.navigation.state.menu_selector)[index+1]).addClass('focus');
    } else {
        VE.navigation.switch_menu('first');
    }
}

VE.navigation.move_focus_down = function(){
    var max_index = $('.units .l-unit:not(.header)').length-1;
    var index = parseInt($('.units .l-unit').index($('.units .l-unit.focus')));

    if($('.l-menu').hasClass('active') || $('.l-stat').hasClass('active')){
        $('.l-menu').removeClass('active');
        $('.l-stat').removeClass('active');
        $('.units').addClass('active');
        index--;

        if(index == -2)
            index = -1;
    }

    if(index < max_index){
        $('.units .l-unit.focus').removeClass('focus');
        $($('.units .l-unit:not(.header)')[index+1]).addClass('focus');

        $('html, body').animate({
            scrollTop: $('.units .l-unit.focus').offset().top - 200
        }, 200);
    }
}

VE.navigation.move_focus_up = function(){
    var index = parseInt($('.units .l-unit:not(.header)').index($('.units .l-unit.focus')));

    if(index == -1)
        index = 0;

    if($('.l-menu').hasClass('active') || $('.l-stat').hasClass('active')){
        $('.l-menu').removeClass('active');
        $('.l-stat').removeClass('active');
        $('.units').addClass('active');
        index++;
    }

    if(index > 0){
        $('.units .l-unit.focus').removeClass('focus');
        $($('.units .l-unit:not(.header)')[index-1]).addClass('focus');

        $('html, body').animate({
            scrollTop: $('.units .l-unit.focus').offset().top - 200
        }, 200);
    }
}

VE.navigation.switch_menu = function(position){
    position = position || 'first'; // last

    if(VE.navigation.state.active_menu == 0){
        VE.navigation.state.active_menu = 1;
        VE.navigation.state.menu_selector = '.l-stat__col';
        VE.navigation.state.menu_active_selector = '.l-stat__col--active';
        $('.l-menu').removeClass('active');
        $('.l-stat').addClass('active');

        if(position == 'first'){
            $($(VE.navigation.state.menu_selector)[0]).addClass('focus');
        } else {
            var max_index = $(VE.navigation.state.menu_selector).length-1;
            $($(VE.navigation.state.menu_selector)[max_index]).addClass('focus');
        }
    } else {
        VE.navigation.state.active_menu = 0;
        VE.navigation.state.menu_selector = '.l-menu__item';
        VE.navigation.state.menu_active_selector = '.l-menu__item--active';
        $('.l-menu').addClass('active');
        $('.l-stat').removeClass('active');

        if(position == 'first'){
            $($(VE.navigation.state.menu_selector)[0]).addClass('focus');
        } else {
            var max_index = $(VE.navigation.state.menu_selector).length-1;
            $($(VE.navigation.state.menu_selector)[max_index]).addClass('focus');
        }
    }
}

VE.notifications.get_list = function(){
/// TODO get notifications only once
    $.ajax({
        url: "/list/notifications/?ajax=1&token="+$('#token').attr('token'),
        dataType: "json"
    }).done(function(data) {
        var acc = [];

        $.each(data, function(i, elm){
            var tpl = Tpl.get('notification', 'WEB');
            if(elm.ACK == 'no')
                tpl.set(':UNSEEN', 'unseen');
            else
                tpl.set(':UNSEEN', '');

            tpl.set(':ID', elm.ID);
            tpl.set(':TYPE', elm.TYPE);
            tpl.set(':TOPIC', elm.TOPIC);
            tpl.set(':NOTICE', elm.NOTICE);
            tpl.set(':TIME', elm.TIME);
            tpl.set(':DATE', elm.DATE);
            acc.push(tpl.finalize());
        });

        if(!Object.keys(data).length){
            var tpl = Tpl.get('notification_empty', 'WEB');
            acc.push(tpl.finalize());
        }

        $('.notification-container').html(acc.done()).show();

        $('.notification-container .mark-seen').click(function(event){
//            VE.notifications.mark_seen($(event.target).attr('id').replace("notification-", ""));
            VE.notifications.delete($(event.target).attr('id').replace("notification-", ""));
        });

    });
}


VE.notifications.delete = function(id){
    $('#notification-'+id).parents('li').hide();
    $.ajax({
        url: "/delete/notification/?delete=1&notification_id="+id+"&token="+$('#token').attr('token')
    });
    if($('.notification-container li:visible').length == 0) {
        $('.l-profile__notifications .status-icon').removeClass('status-icon');
        $('.l-profile__notifications').removeClass('updates').removeClass('active');
    }
}
VE.notifications.mark_seen = function(id){
    $('#notification-'+id).parents('li').removeClass('unseen');
    $.ajax({
        url: "/delete/notification/?notification_id="+id+"&token="+$('#token').attr('token')
    });
    if($('.notification-container .unseen').length == 0) {
        $('.l-profile__notifications .status-icon').removeClass('status-icon');
        $('.l-profile__notifications').removeClass('updates');
    }
}


VE.navigation.init = function(){
    if($('.l-menu__item.l-menu__item--active').length){
//        VE.navigation.switch_menu();
        VE.navigation.state.active_menu = 0;
        VE.navigation.state.menu_selector = '.l-menu__item';
        VE.navigation.state.menu_active_selector = '.l-menu__item--active';
        $('.l-menu').addClass('active');
        $('.l-stat').removeClass('active');
    } else {
        $('.l-stat').addClass('active');
    }
}

VE.navigation.shortcut = function(elm){
  var action = elm.attr('key-action');

  if(action == 'js'){
    var e = elm.find('.data-controls');
    VE.core.dispatch(true, e, 'click');
  }
  if(action == 'href') {
    location.href=elm.find('a').attr('href');
  }
}

VE.helpers.extendPasswordFields();


