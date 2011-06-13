/*App.Actions.cancel_ip_form = function(){
    alert(1);
}*/

App.Actions.show_subform = function(evt)
{
    var elm = $(evt.target);
    var ref = elm.hasClass('row') ? elm : elm.parents('.row');
    var ref_subform = ref.next('.subform');
    if (ref_subform.length > 0) {
        ref_subform.remove();
    }
    
    if ('undefined' != typeof App.Pages[App.Env.world].showSubform) {
        App.Pages[App.Env.world].showSubform(ref);
    }    
    // TODO: probably general way to embed subforms
}

App.Actions.close_subform = function(evt) 
{
    var elm = $(evt.target);
    var ref = elm.hasClass('subform') ? elm : elm.parents('.subform');
    var parent_ref = ref.prev('.row');
    if (parent_ref.length > 0) {
        parent_ref.find('.show-records').removeClass('hidden');
    }
    ref.remove();
}

App.Actions.view_template_settings = function(evt) 
{
    alert('TODO');
}

App.Actions.add_subrecord_dns = function(evt)
{
    var elm = $(evt.target);
    var ref = elm.hasClass('subform') ? elm : elm.parents('.subform');
    if (ref.length > 0) {
        var tpl = App.HTML.Build.dns_subrecord({});
        ref.find('.add-box').after(tpl.finalize());
        App.Helpers.updateScreen();
    }
}

App.Actions.delete_subentry = function(evt)
{
    var sure = confirm(App.i18n.getMessage('confirm'));
    if (!sure) {
        return;
    }
    var elm = $(evt.target);
    var ref = elm.hasClass('subrow') ? elm : elm.parents('.subrow');
    ref.effect('puff', {}, 300, function(){ref.remove();})    
}

/**
 * Embeds new item form
 * if exits custom method (App.Pages[ENVIRONMENT_NAME].newForm) 
 * custom method will be executes instead of default one
 */
App.Actions.new_entry = function() {
    if ('undefined' != typeof App.Pages[App.Env.world].new_entry) {
        App.Pages[App.Env.world].new_entry();
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
App.Actions.save_form = function(evt) {
    var elm = $(evt.target);
    elm = elm.parents('.b-new-entry');
    
    var elm_id = App.Env.world + '_FORM_ID';
    var build_method = App.Env.getWorldName() + '_entry';
    
    if (elm.attr('id') == App.Constants[elm_id]) { // NEW ITEM
        var values = App.Helpers.getFormValues(elm);
        if(App.Validate.form(values, $('#'+elm_id))) {
            App.Model.add(values, source);
            var tpl = App.HTML.Build[build_method](values, 'new');
            $('#' + App.Constants[elm_id]).replaceWith(tpl);
        }
    }
    else { // OLD ITEM, UPDATING IT
        var source = $(elm).find('.source').val();
        var values = App.Helpers.getFormValues(elm);
        if(App.Validate.form(values, $('#'+elm_id))) {
            App.Model.update(values, source);
            var tpl = App.HTML.Build[build_method](values);
            elm.replaceWith(tpl);
        }
    }
    App.Helpers.updateScreen();
}

// do_action_edit
App.Actions.edit = function(evt) {
    var elm = $(evt.target);
    elm = elm.hasClass('row') ? elm : elm.parents('.row');
    App.Pages[App.Env.world].edit(elm);
    App.Helpers.updateScreen();
}

// do_cancel_form
App.Actions.cancel_form = function(evt, params) {
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

App.Actions.close_popup = function() 
{
    App.View.closePopup();
}

App.Actions.save_dns_subrecords = function(evt)
{
    var elm = $(evt.target);
    var ref = elm.hasClass('subform') ? elm : elm.parents('.subform');
    
    var records = [];
    ref.find('.subrow').each(function(i, o){
        records[records.length++] = App.Helpers.getFormValuesFromElement(o);
    });
    
    fb.warn($.toJSON(records));
}