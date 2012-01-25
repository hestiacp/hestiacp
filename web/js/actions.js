App.Actions.toggle_ssl_support = function(evt, elm)
{
    if (!elm) {
        var elm = $(evt.target);
    }
    
    var ref = elm.hasClass('form') ? elm : elm.parents('.form');
    fb.log(ref);
    if (ref.find('.ssl_support').attr('checked')) {
        $('.ssl-crtfct-box', ref).removeClass('hidden');
    }
    else {
        $('.ssl-crtfct-box', ref).addClass('hidden');
    }
}

App.Actions.view_nginx_extensions = function(evt)
{
    var elm = $(evt.target);
    var ref = elm.hasClass('row') ? elm : elm.parents('.row');
    var data = App.Helpers.evalJSON(ref.find('.source').val());
    var extensions = data['NGINX_EXT'] || '';
    var html = extensions.replace(/,/gi, ' ');
    App.Helpers.openInnerPopup(elm, html, 'Nginx extensions');
}

App.Actions.login_as = function(evt) 
{
    var elm = $(evt.target);
    var ref = elm.parents('.row');
    var source = App.Helpers.evalJSON($(ref).find('.source').val())
    App.Ajax.request('USER.loginAs', {'user': source.LOGIN_NAME}, function(reply) {
        if (reply.result) {
            location.href = "";
        }
        else {
            App.Helpers.alert('You cannot do this action. Please contact support');
        }
    });
}

App.Actions.toggle_suspend = function(evt)
{
	var elm = $(evt.target);
	var ref = elm.parents('.form');
	ref.removeClass('form-suspended');
	fb.warn(ref);
	var ref_checkbox = ref.find('input[name="SUSPEND"]');
	ref_checkbox.val() == 'on' ? ref_checkbox.val('off') : ref_checkbox.val('on'); //  switch state
	if (ref_checkbox.val() == 'on') { // set class on new state
		ref.addClass('form-suspended');
		fb.warn('SUSP');
	}
	else {
		ref.removeClass('form-suspended');		
		fb.warn('UNSUSP');
	}
}

App.Actions.toggle_custom_select = function(evt)
{
    var elm = $(evt.target);
    elm     = elm.hasClass('complex-select') ? elm : elm.parents('.complex-select');
    var ref = elm.find('.complex-select-content');
    $('.s-c-highlighted').removeClass('s-c-highlighted');
    if (ref.hasClass('hidden')) {
        ref.removeClass('hidden');
        App.Tmp.focusedComplexSelect = elm;
    }
    else {
        ref.addClass('hidden');
    }
}

App.Actions.update_cs_value = function(evt)
{
    var elm = $(evt.target);
    elm = elm.hasClass('cust-sel-option') ? elm : elm.parents('.cust-sel-option');

    var val = elm.find('.c-s-value').val();
    $('.complex-select-content').addClass('hidden');
    
    if (val.toLowerCase() == 'nothing') {
        App.Actions.mass_nothing();
        return;
    }
    
    if (App.Tmp[App.Env.world + '_selected_records'] > 0) {
        var confirm_message_key = App.Tmp[App.Env.world + '_selected_records'] == 1 ? 1 + ' record' : App.Tmp[App.Env.world + '_selected_records'] + ' records';
        var confirmed = confirm('This action will ' + val.toLowerCase() + ' ' + confirm_message_key + '. Do you want to proceed?');
        if (confirmed) {
            fb.log('mass_' + val);
            var func_name = val.toLowerCase();
            'function' == typeof App.Actions['mass_' + func_name] ? App.Actions['mass_' + func_name]() : false;
        }
    }
}

App.Actions.mass_delete = function()
{
    App.Actions.mass_action('massiveDelete');
    App.Actions.reset_batch();
}

App.Actions.mass_suspend = function()
{
    App.Actions.mass_action('massiveSuspend');
    App.Actions.reset_batch();
}

App.Actions.mass_unsuspend = function()
{
    App.Actions.mass_action('massiveUnsuspend');
    App.Actions.reset_batch();
}

App.Actions.mass_nothing = function()
{
    $('.complex-select-content').addClass('hidden');
}

App.Actions.mass_action = function(method_name)
{
    var rows = $('.checked-row');
    if (rows.length > 0) {
        var acc = [];
        rows.each(function(i, o) {
            acc[acc.length++] = App.Helpers.evalJSON($(o).find('.source').val());
        });
        
        App.Ajax.request(App.Env.world+'.'+method_name, {'entities': App.Helpers.toJSON(acc)}, function() {
            App.Pages.prepareHTML();
        });
    }
}

App.Actions.reset_batch = function()
{
    $('#batch-processor .selector-title').html('NONE');
    $('.styled.do_action_toggle_batch_selector.style-applied').attr('checked', false);
    $('.checkbox.do_action_toggle_batch_selector').css('background-position', '0 0');
}

App.Actions.do_change_password = function()
{
    
    var params = {
        email: $('#change-email').val(),
        captcha: $('#captcha').val()
    }
    
    App.Ajax.request('MAIN.requestPassword', params, function(reply){
        $('#captcha-img').attr('src', App.Helpers.generateUrl('captcha.php?')+Math.floor(Math.random() * 9999));
        $('#captcha').val('');
        if (reply.result) {
            $('#change-psw-error').html('');           
            $('#change-psw-error').addClass('hidden');
            $('#change-psw-success').html('Reset link was sent to email box provided by you.'); 
            $('#change-psw-success').removeClass('hidden');
            $('.form-row').remove();            
        }
        else {
            $('#change-psw-error').html(reply.message);           
            $('#change-psw-error').removeClass('hidden');
        }
    }); 
}

App.Actions.back_to_login = function()
{    
    $('body').addClass('page-auth');
    var tpl = App.Templates.get('login', 'popup');
    tpl.set(':LOGO_URL', App.Helpers.generateUrl('images/vesta-logo-2011-12-14.png'));
    tpl.set(':YEAR', new Date().getFullYear());
    tpl.set(':EMAIL_REAL', App.Settings.VestaAbout.company_email);
    tpl.set(':EMAIL', App.Settings.VestaAbout.company_email);
    tpl.set(':PRODUCT_NAME', App.Settings.VestaAbout.company_name);
    tpl.set(':VERSION', App.Settings.VestaAbout.version_name + ' ' + App.Settings.VestaAbout.version);
    $('body').prepend(tpl.finalize());
    $('#change-psw-block').remove();    
    $('.remember-me').checkBox();
}

App.Actions.change_password = function(evt)
{
    evt.preventDefault();
    
    if ($('#change-psw-block').length > 0) {
        return $('#change-psw-block').show();
    }  
    
    var tpl = App.Templates.get('change_psw', 'popup');
    tpl.set(':LOGO_URL', App.Helpers.generateUrl('images/vesta-logo-2011-12-14.png'));
    tpl.set(':YEAR', new Date().getFullYear());
    tpl.set(':CAPTCHA_URL', App.Helpers.generateUrl('captcha.php?')+Math.floor(Math.random() * 9999));
    tpl.set(':CAPTCHA_URL_2', App.Helpers.generateUrl('captcha.php'));  
    tpl.set(':EMAIL_REAL', App.Settings.VestaAbout.company_email);
    tpl.set(':EMAIL', App.Settings.VestaAbout.company_email);
    tpl.set(':PRODUCT_NAME', App.Settings.VestaAbout.company_name);
    tpl.set(':VERSION', App.Settings.VestaAbout.version_name + ' ' + App.Settings.VestaAbout.version);  
    $('#auth-block').remove();
    $('body').prepend(tpl.finalize()); 
    $('#change-psw-error').html('');           
    $('#change-psw-error').addClass('hidden');       
}

App.Actions.profile_exit = function(evt)
{
    evt.preventDefault();
    if (App.Env.initialParams.real_user) { // exit "logged in as" state
        App.Ajax.request('USER.logoutAs', {}, function(reply) {
            if (reply.result) {
                location.href = "";
            }
            else {
                App.Helpers.alert('You cannot do this action. Please contact support');
            }
        });
        return;
    }
    
    
    App.Ajax.request('MAIN.logoff', {}, function(reply) {
        location.href = '';
    });
}

// show auth form
App.Actions.authorize = function()
{
    $('#change-psw-block').remove();
    if ($('#auth-block').length > 0) {
        return;
    }    
    $('#page').addClass('hidden');
    $('body').addClass('page-auth');
    var tpl = App.Templates.get('login', 'popup');
    tpl.set(':LOGO_URL', App.Helpers.generateUrl('images/vesta-logo-2011-12-14.png'));
    tpl.set(':YEAR', new Date().getFullYear());
    tpl.set(':EMAIL_REAL', App.Settings.VestaAbout.company_email);
    tpl.set(':EMAIL', App.Settings.VestaAbout.company_email);
    tpl.set(':PRODUCT_NAME', App.Settings.VestaAbout.company_name);
    tpl.set(':VERSION', App.Settings.VestaAbout.version_name + ' ' + App.Settings.VestaAbout.version);
    $('body').prepend(tpl.finalize());
    $(document).ready(function(){
        $('.remember-me').checkBox();
    });
}

/**
* Embeds new item form
* if exits custom method (App.Pages[ENVIRONMENT_NAME].newForm)
* custom method will be executes instead of default one
*/
App.Actions.new_entry = function() {
    if ('undefined' != typeof App.Pages[App.Env.world].new_entry) {fb.log(1);
        App.Pages[App.Env.world].new_entry();
    } else {
        var form_id = App.Constants[App.Env.world + '_FORM_ID'];
        $('#'+form_id).remove();
        var build_method = App.Env.getWorldName() + '_form';
        var tpl = App.HTML.Build[build_method]({}, form_id);
        var box = $('<div>').html(tpl);
        $(box).find('.suspended').addClass('hidden');
        App.Ref.CONTENT.prepend($(box).html());
        
        App.Helpers.updateScreen();
    }
}

// execute authorisation
App.Actions.do_authorize = function()
{
    $('#auth-error').text('');
    $('#auth-form-content').hide();
    App.Ajax.request('MAIN.signin', {'login':$('#authorize-login').val(), 'password':$('#authorize-password').val()}, function(reply)
    {
        if (reply.result == true) {
            location.href = '';
        }
        else {
            $('#auth-error').text(reply.data.error_msg);
            $('#auth-form-content').show();
            $('#auth-error').removeClass('hidden');
        }
    });
}

App.Actions.delete_entry = function(evt) 
{
    var confirmed = confirm(App.i18n.getMessage('confirm'));
    if (!confirmed) {
        return;
    }
    var elm = $(evt.target);
    var elm = elm.hasClass('form') ? elm : elm.parents('.form');
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
    var elm = $(evt.target);    
    var ref = elm.hasClass('tpl-item') ? elm : elm.prev('.tpl-item');
    var tpl_name = $(ref).val() || $(ref).text();    
    App.Helpers.openInnerPopup(elm, App.Env.initialParams.WEB_DOMAIN.TPL[tpl_name].DESCR || tpl_name, 'Template Settings');
}

App.Actions.view_dns_template_settings = function(evt) 
{
    var elm = $(evt.target);    
    var ref = elm.hasClass('tpl-item') ? elm : elm.prev('.tpl-item');
    var tpl_name = $(ref).val() || $(ref).text();    
    App.Helpers.openInnerPopup(elm, App.Env.initialParams.DNS.TPL[tpl_name].DESCR || tpl_name, 'Template Settings');
}

/*App.Actions.view_dns_template_settings = function(evt) 
{
    var elm = $(evt.target);    
    var ref = elm.prev('.tpl-item');        
    var tpl_name = $(ref).val() || $(ref).text();    

    App.Helpers.openInnerPopup(elm, App.Env.initialParams.DNS.TPL[tpl_name].DESCR || tpl_name, '');
}*/

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
        if ($('.b-new-entry').length > 1) {
            var confirmed = confirm('You were editing other entries and those changes will be discarded. Click cancel if you want to save updated entries before adding new one.');
            if (!confirmed) {
                return true;
            }
        }

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
App.Actions.edit = function(evt) 
{

    if ('undefined' != typeof App.Pages[App.Env.world].edit) {        
        App.Pages[App.Env.world].edit(evt);
    } 
    else {
        var elm = $(evt.target);
        elm = elm.hasClass('row') ? elm : elm.parents('.row');
        
        var options = elm.find('.source').val();    
        var build_method = App.Env.getWorldName() + '_form';    
        var tpl = App.HTML.Build[build_method](options);
        elm.replaceWith(tpl);
        
        App.Helpers.disableNotEditable();
        App.Helpers.updateScreen();
    }
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

App.Actions.generate_pass = function(evt)
{
    var elm = $(evt.target);
    var ref = elm.parents('.form-row');
    $('.password', ref).val(App.Helpers.generatePassword());
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
    App.Helpers.openInnerPopup(elm, $(elm).next('.inner-popup-html').val(), 'Details');
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
    tpl.set(':NS_LABEL', 'Name Server');
    var ref = $(elm).hasClass('form-row') ? elm : $(elm).parents('.form-row');
    $(ref).before(tpl.finalize());
    
    if ((total_nses + 1) == App.Settings.NS_MAX ) { // added last NS
        $('.additional-ns-add', form).addClass('hidden');
    }
    
    $(form).find('.ns-entry').each(function(i, o)
    {
        $(o).find('label').text('Name Server #' + (i + 1));
        $(o).find('input').attr('name', 'NS' + (i + 1));
    });
}

App.Actions.delete_ns = function(evt)
{
    var sure = confirm(App.i18n.getMessage('confirm'));
    if (!sure) {
        return;
    }
    var elm = $(evt.target);
    
    form = elm.parents('.form:first');
    var total_nses = $(form).find('.ns-entry').length;
    if (total_nses == App.Settings.NS_MIN) {
        return App.Helpers.alert('Minimum number of Name Servers is ' + App.Settings.NS_MIN);
    }
    
    var form = elm.parents('.form:first');
    $(elm).parents('.form:first').find('.additional-ns-add').removeClass('hidden');    
    $(elm).parents('.ns-entry').remove();   
    
    $(form).find('.ns-entry').each(function(i, o)
    {
        $(o).find('label').text('Name Server #' + (i + 1));
        $(o).find('input').attr('name', 'NS' + (i + 1));
    });
}

App.Actions.view_full_ns_list = function(evt)
{
    var elm = $(evt.target);
    App.Helpers.openInnerPopup(elm, $(elm).parents('.prop-box').find('.ns-full-list:first').html(), 'Name Server list');    
}

App.Actions.view_full_web_templates = function(evt)
{
    var elm = $(evt.target);
    App.Helpers.openInnerPopup(elm, $(elm).parents('.prop-box').find('.ns-full-list:first').html(), 'Web Templates list');    
}

App.Actions.view_template_info = function(evt)
{
    var elm = $(evt.target);
    ref = elm.hasClass('row') ? elm : elm.parents('.row');
    
    var options = ref.find('.source').val(); 
    App.Ajax.request('DNS.getTemplateInfo', {spell: options}, function(reply) {
        if (reply.result) {
            var html = '';
            $.each(reply.data, function(key) {
                html += '<li><strong>'+key+':</strong> '+reply.data[key]+'</li>';
            });
            App.Helpers.openInnerPopup(elm, '<ul>'+html+'</ul>', 'Template Info');
        }        
    });
}

App.Actions.toggle_stats_block = function(evt)
{
    var elm = $(evt.target);
    if (!!elm.attr('checked')) {
        elm.parents('.stats-settings').find('.stats-block').removeClass('hidden');
    }
    else {
        elm.parents('.stats-settings').find('.stats-block').addClass('hidden');
    }
}

App.Actions.exec_v_console = function(evt)
{
    evt.preventDefault();
    App.Helpers.openInnerPopup(evt.target, 'This functionality will be available in next releases', 'Details');
}

App.Actions.view_profile_settings = function(evt)
{
    evt.preventDefault();
    App.Helpers.openInnerPopup(evt.target, 'This functionality will be available in next releases', 'Details');
}

App.Actions.select_all = function(evt) 
{
    $('.row').addClass('checked-row')
}

App.Actions.deselect_all = function(evt) 
{
    $('.row').removeClass('checked-row')
}

App.Actions.delete_selected = function(evt)
{
    var selected = $('.checked-row');
    if (selected.length == 0) {
        return App.Helpers.alert('No entry selected. Please select at least one.');
    }
    var confirmed = confirm('You are about to delete ' + selected.length + ' entrie(s). Are you sure?');
    if (!confirmed) {
        return;
    }
}

App.Actions.loadStats = function(type)
{
    var period = '';
    switch (type) {
        case 'month':
            period = 'monthly'
            break;
        case 'today':
            period = 'daily'
            break;
        case 'week':
            period = 'weekly'
            break;
        case 'year':
            period = 'yearly'
            break;
        default:
            period = 'daily';
            break;
    }
    
    $('#actions-toolbar .sub-active').removeClass('sub-active');
    $('#actions-toolbar .'+type).addClass('sub-active');
    
    App.Ajax.request('STATS.getList', {period: period}, function(reply) {
		if (!reply.result) {
			App.Herlers.alert('Stats list failed to load. Please try again a bit later');
		}
		
		App.Ref.CONTENT.html(App.HTML.Build.stats_list(reply.data));
        App.Helpers.updateScreen();
	});
}

App.Actions.toggle_batch_selector = function() 
{
    if (App.Tmp[App.Env.world + '_selected_records'] == 0) { // Select all
        var rows = $('.row');
        rows.each(function(i, row) {
            $(row).addClass('checked-row');
        });
        App.Tmp[App.Env.world + '_selected_records'] = rows.length;
        $('#batch-processor .selector-title').html(rows.length + ' SELECTED');
    }
    else {
        var rows = $('.row');
        rows.each(function(i, row) {
            $(row).removeClass('checked-row');
        });
        App.Tmp[App.Env.world + '_selected_records'] = 0;
        $('#batch-processor .selector-title').html('NONE');
    }
}
