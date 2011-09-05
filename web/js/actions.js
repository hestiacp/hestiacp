App.Actions.delete_entry = function(evt) 
{
    var confirmed = confirm(App.i18n.getMessage('confirm'));
    if (!confirmed) {
        return;
    }
    var elm = $(evt.target);
    var elm = elm.hasClass('row') ? elm : elm.parents('.row');    
    App.Model.remove(App.Env.world, elm);
}

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
    
    if (!App.Validate.form(App.Env.world, elm)) {
        return App.Validate.displayFormErrors(App.Env.world, elm);
    }
    
    if (elm.attr('id') == App.Constants[elm_id]) { // NEW ITEM
        var values = App.Helpers.getFormValues(elm);
        if(App.Validate.form(values, $('#'+elm_id))) {
            App.Model.add(values, source);
            var form_id = App.Constants[App.Env.world + '_FORM_ID'];
            $('#'+form_id).remove();
        }
    }
    else { // OLD ITEM, UPDATING IT
        var source = $(elm).find('.source').val();
        var values = App.Helpers.getFormValues(elm);
        if(App.Validate.form(values, $('#'+elm_id))) {            
            App.Model.update(values, source, elm);         
        }       
    }    
}

// do_action_edit
App.Actions.edit = function(evt) {
    var elm = $(evt.target);
    elm = elm.hasClass('row') ? elm : elm.parents('.row');
    
    var options = elm.find('.source').val();
    fb.warn(elm);
    fb.warn(options);
    var build_method = App.Env.getWorldName() + '_form';    
    var tpl = App.HTML.Build[build_method](options);
    elm.replaceWith(tpl);
    
    //App.Pages[App.Env.world].edit(elm);
    //App.Helpers.updateScreen();
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
        fb.warn(elm.find('.source').val());
        var options = App.Helpers.evalJSON(elm.find('.source').val());
        var entry_name = App.Env.world.toLowerCase() + '_entry';
        var tpl = App.HTML.Build[entry_name](options);
        elm.replaceWith(tpl);
    }
    App.Helpers.updateScreen();
}

App.Actions.suspend = function(evt)
{
    var confirmed = confirm('Suspend?');
    if (!confirmed) {
        return ;
    }
    var elm = $(evt.target);    
    var row = elm.parents('.row');
    
    var options = row.find('.source').val();    
    App.Ajax.request(App.Env.world+'.suspend', {spell: options}, function(reply) {
        if (reply.result) {
            //var tpl = App.Templates.get('SUSPENDED_TPL_SUSPENDED', 'general');
            //$(elm).replaceWith(tpl.finalize());
            App.Pages.prepareHTML();
            App.Helpers.updateScreen();
        }
        else {
            return App.Helpers.alert('Failed to suspend');
        }
    });    
}

App.Actions.unsuspend = function(evt)
{
    var confirmed = confirm('Unsuspend?');
    if (!confirmed) {
        return ;
    }
    
    var elm = $(evt.target);    
    var row = elm.parents('.row');
    
    var options = row.find('.source').val();    
    App.Ajax.request(App.Env.world+'.unsuspend', {spell: options}, function(reply) {
        if (reply.result) {
            //var tpl = App.Templates.get('SUSPENDED_TPL_NOT_SUSPENDED', 'general');
            //$(elm).replaceWith(tpl.finalize());
            App.Pages.prepareHTML();
            App.Helpers.updateScreen();
        }
        else {
            return App.Helpers.alert('Failed to suspend');
        }
    });    
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


App.Actions.close_subform = function(evt, elm) 
{
    var elm = elm || $(evt.target);
    var ref = elm.hasClass('subform') ? elm : elm.parents('.subform');
    var parent_ref = ref.prev('.row');
    if (parent_ref.length > 0) {
        parent_ref.find('.show-records').removeClass('hidden');
    }
    ref.remove();
}

App.Actions.save_dns_subrecords = function(evt)
{
    var elm = $(evt.target);
    var ref = elm.hasClass('subform') ? elm : elm.parents('.subform');
    
    var data = [];
    $('.subform').find('.subrow').each(function(i, o)
    {
        data[data.length++] = App.Helpers.getFormValues(o);
    });
    
    var parent_row = $(elm).parents('.subform').prev('.dns-details-row');
    var dns_json = $(parent_row).find('.source').val();
    
    App.Ajax.request('DNS.changeRecords', {spell: App.Helpers.toJSON(data), dns: dns_json}, function(reply)
    {
        if (reply.result) {
            var emphasize = $('.show-records', parent_row);
            App.Actions.close_subform(null, elm);
            $(emphasize).effect("highlight", {'color':'#B0D635'}, 3000);
            
        }
        else {
            App.Helpers.alert('Changes were not applied');
        }
    });
}

App.Actions.delete_subentry = function(evt)
{
    var sure = confirm(App.i18n.getMessage('confirm'));
    if (!sure) {
        return;
    }
    
    var elm = $(evt.target);
    var ref = elm.hasClass('subrow') ? elm : elm.parents('.subrow');
    $(ref).remove();    
}

App.Actions.generate_pass = function()
{
    $('.password').val(App.Helpers.generatePassword());
}

App.Actions.toggle_section = function(evt)
{
    var elm = $(evt.target);    
    var ref = $(elm).parents('.form-options-group:first');
    fb.log(ref);
    if ($('.sub_section:first', ref).hasClass('hidden')) {
        $('.sub_section:first', ref).removeClass('hidden');
        $('.group-header:first', ref).removeClass('collapsed').addClass('expanded');
    }
    else {
        $('.sub_section:first', ref).addClass('hidden');
        $('.group-header:first', ref).removeClass('expanded').addClass('collapsed');
    }
}

App.Actions.close_inner_popup = function(evt)
{
    App.Helpers.closeInnerPopup();
}

App.Actions.open_inner_popup = function(evt)
{
    var elm = $(evt.target);  
    App.Helpers.openInnerPopup(elm, $(elm).next('.inner-popup-html').val());
}

App.Actions.add_db_user = function(evt)
{
    alert('TODO');
}

App.Actions.backup_db = function(evt)
{
    alert('TODO');
}

App.Actions.add_form_ns = function(evt)
{
    var elm = $(evt.target);
    
    form = elm.parents('.form:first');
    var total_nses = $(form).find('.ns-entry').length;
    if (total_nses == App.Settings.NS_MAX) {
        return App.Helpers.alert('Maximum number of NS cannot be more than ' + App.Settings.NS_MAX);
    }
    
    var tpl = App.Templates.get('NS_INPUT', 'user');
    tpl.set(':NAME', '');
    tpl.set(':NS_LABEL', 'NS');
    elm.before(tpl.finalize());
    
    if ((total_nses + 1) == App.Settings.NS_MAX ) { // added last NS
        $('.additional-ns-add', form).addClass('hidden');
    }
    
    $(form).find('.ns-entry').each(function(i, o)
    {
        $(o).find('label').text('NS #' + (i + 1));
        $(o).find('input').attr('name', 'NS' + (i + 1));
    });
}

App.Actions.delete_ns = function(evt)
{
    var elm = $(evt.target);
    
    form = elm.parents('.form:first');
    var total_nses = $(form).find('.ns-entry').length;
    if (total_nses == App.Settings.NS_MIN) {
        return App.Helpers.alert('Minimum number of NS is ' + App.Settings.NS_MIN);
    }
    
    var form = elm.parents('.form:first');
    $(elm).parents('.form:first').find('.additional-ns-add').removeClass('hidden');    
    $(elm).parents('.ns-entry').remove();   
    
    $(form).find('.ns-entry').each(function(i, o)
    {
        $(o).find('label').text('NS #' + (i + 1));
        $(o).find('input').attr('name', 'NS' + (i + 1));
    });
}

App.Actions.view_full_ns_list = function(evt)
{
    var elm = $(evt.target);
    App.Helpers.openInnerPopup(elm, $(elm).parents('.prop-box').find('.ns-full-list:first').html());    
}
