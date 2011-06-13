App.Actions.cancel_ip_form = function(){
    alert(1);
}

/**
 * Embeds new item form
 * if exits custom method (App.Pages[ENVIRONMENT_NAME].newForm) 
 * custom method will be executes instead of default one
 */
App.Actions.newForm = function() {
    if ('undefined' != typeof App.Pages[App.Env.world].newForm) {
        App.Pages[App.Env.world].newForm();
    } else {
        var form_id = App.Constants[App.Env.world + '_FORM_ID'];
        $('#'+form_id).remove();
        var build_method = App.Env.getWorldName() + '_form';
        var tpl = App.HTML.Build[build_method]({}, form_id);
        App.Ref.CONTENT.prepend(tpl);
        App.Helpers.updateScreen();
    }
}

// do_action_save
App.Actions.saveForm = function(evt) {
    
}

// do_action_edit
App.Actions.edit = function(evt) {
    var elm = $(evt.target);
    elm = elm.hasClass('row') ? elm : elm.parents('.row');
    App.Pages[App.Env.world].edit(elm);
}

// do_cancel_form
App.Actions.cancelForm = function(evt, params) {
    var elm = $(evt.target);
    elm = elm.parents('.b-new-entry');
    var form_id = App.Constants[App.Env.world + '_FORM_ID'];
    if (elm.attr('id') == form_id) {
        $('#' + form_id).remove();
    }
    else {
        var options = App.Helpers.evalJSON(elm.find('.source').val());
        var entry_name = App.Env.world.toLowerCase() + '_entry';
        var tpl = App.HTML.Build[entry_name](options);
        elm.replaceWith(tpl);
    }
    App.Helpers.updateScreen();
}

// do_action_form_help
App.Actions.showFormHelp = function(evt) {
    var tpl_name = App.Env.world + '_form';
    var tpl = App.Templates.get(tpl_name, 'help');
    App.View.popup(tpl.finalize());
}

// do_action_entry_help
App.Actions.showEntryHelp = function(evt) {
    var tpl_name = App.Env.world + '_entry';
    var tpl = App.Templates.get(tpl_name, 'help');
    App.View.popup(tpl.finalize());
}

App.Actions.embedSubform = function(evt) {
    var tpl = App.Templates.get('subform', App.Env.getWorldName());
}
