App.Pages.init = function()
{
	if ('undefined' == typeof App.Env.initialParams) { // first run
		App.Ajax.request('MAIN.getInitial', {}, function(reply) {
			App.Env.initialParams = reply.data;
			App.Helpers.updateInitial();
		});


/*
        if (!App.Env.initialParams.auth_user.admin) {
                var head= document.getElementsByTagName('head')[0];
                var script= document.createElement('script');
                script.type= 'text/javascript';
                script.src= App.Helpers.generateUrl('js/user_templates.js?'+Math.random());
                head.appendChild(script);
        }
        else{
                var head= document.getElementsByTagName('head')[0];
                var script= document.createElement('script');
                script.type= 'text/javascript';
                script.src= App.Helpers.generateUrl('js/templates.js?'+Math.random());
                head.appendChild(script);
        }
*/


	}
    
    App.Pages.prepareHTML();    
    $('.section.active').removeClass('active');
    $('#'+App.Env.world).addClass('active');
    
    if (cookieEnabled()) {
        setCookie('tab', App.Env.world);
    }
}

App.Pages.prepareHTML = function()
{
	$('.d-popup').remove();
    App.Actions.reset_batch();
    $('#actions-toolbar .stats-subbar').remove();
    $('#actions-toolbar .do_action_new_entry').removeClass('hidden');
	$('.active').removeClass('active');
    $('.row-filters').removeClass('hidden');
    if ('undefined' != typeof App.Pages[App.Env.world].prepareHTML) {
        App.Pages[App.Env.world].prepareHTML();
    }  
    else {        
        App.Model[App.Env.world].loadList();
    }
    $('#new-entry-keyword').text(App.Helpers.getHumanTabName());
    document.title = 'Vesta | ' + App.Helpers.getHumanTabName();
    
    App.Tmp[App.Env.world + '_selected_records'] = 0;
}

App.Pages.DNS.showSubform = function(ref) 
{
    fb.log('loading');
    return;
    App.Helpers.showLoading();
    var data = ref.find('.source:first').val();
    App.Ajax.request('DNS.getListRecords', {
        spell: data
    }, function(reply) {
        var tpl = App.Templates.get('SUBFORM', 'dns');
        var tpl_records = App.HTML.Build.dns_records(reply.data);
        tpl.set(':SUBRECORDS', tpl_records);
        
        $(ref).find('.show-records').addClass('hidden');
        $(ref).after(tpl.finalize());
        App.Helpers.updateScreen();
    });
}

App.Pages.USER.new_entry = function(evt)
{ 
    var form_id = App.Constants[App.Env.world + '_FORM_ID'];
    $('#'+form_id).remove();
    var build_method = App.Env.getWorldName() + '_form';
    var tpl = App.HTML.Build[build_method]({}, form_id);
    var box = $('<div>').html(tpl);
    $(box).find('.suspended').addClass('hidden');
    App.Ref.CONTENT.prepend($(box).html());
    App.Helpers.updateScreen(); 
    $('#'+form_id).find('.ns-entry, .additional-ns-add').addClass('hidden').find('.rule-required').removeClass('rule-required');   
    $('#'+form_id).find('.shell-entry').addClass('hidden');
}

App.Pages.WEB_DOMAIN.new_entry = function(evt)
{ 
    var form_id = App.Constants[App.Env.world + '_FORM_ID'];
    $('#'+form_id).remove();
    var build_method = App.Env.getWorldName() + '_form';
    var tpl = App.HTML.Build[build_method]({}, form_id);
    var box = $('<div>').html(tpl);
    $(box).find('.suspended').addClass('hidden');
    App.Ref.CONTENT.prepend($(box).html());
    //App.Ref.CONTENT.prepend(tpl);
    App.Helpers.updateScreen(); 
    $('#'+form_id).find('.ns-entry, .additional-ns-add').addClass('hidden');   
    var ssl_key_upload  = App.HTML.Build.ssl_key_file();
    var ssl_cert_upload = App.HTML.Build.ssl_cert_file();
    var ssl_ca_upload = App.HTML.Build.ssl_ca_file();
    $('#'+form_id).find('.ssl-key-input-dummy:first').replaceWith(ssl_key_upload);
    $('#'+form_id).find('.ssl-cert-input-dummy:first').replaceWith(ssl_cert_upload);
    $('#'+form_id).find('.ssl-ca-input-dummy:first').replaceWith(ssl_ca_upload);
    App.Actions.toggle_ssl_support({}, $('#'+form_id));
}

App.Pages.WEB_DOMAIN.edit = function(evt) 
{      
    var elm = $(evt.target);
    elm = elm.hasClass('row') ? elm : elm.parents('.row');
    
    var options = elm.find('.source').val();    
    var build_method = App.Env.getWorldName() + '_form';    
    var tpl = App.HTML.Build[build_method](options);
    // ssls uploads
    var ssl_key_upload  = App.HTML.Build.ssl_key_file();
    var ssl_cert_upload = App.HTML.Build.ssl_cert_file();
    var ssl_ca_upload = App.HTML.Build.ssl_ca_file();
    tpl = tpl.replace('<span class="ssl-key-input-dummy">...</span>', ssl_key_upload);
    tpl = tpl.replace('<span class="ssl-cert-input-dummy">...</span>', ssl_cert_upload);
    tpl = tpl.replace('<span class="ssl-ca-input-dummy">...</span>', ssl_cert_upload);
    var tmp_elm = $('<div>').html(tpl);
    App.Actions.toggle_ssl_support({}, tmp_elm.find('.form'));
    elm.replaceWith(tmp_elm.html());
    
    
    App.Helpers.disableNotEditable();
    App.Helpers.updateScreen();
}

App.Pages.WEB_DOMAIN.setSSL = function(type, frame)
{
    var txt = App.Helpers.evalJSON(content);
    var ref = frame.frameElement;
    $(ref).next('textarea').val(frame.document.getElementById('result').value);
}

App.Pages.loadBackups = function()
{
	App.Env.world = 'BACKUPS';
	App.Pages.prepareHTML();
}

App.Pages.BACKUPS.prepareHTML = function()
{
	$('#primary-nav-box .active').removeClass('active');
	$('#BACKUPS').addClass('active');
	$('#new-entry-keyword').text(App.Helpers.getHumanTabName());
    document.title = 'Vesta | ' + App.Helpers.getHumanTabName();
    
    App.Ajax.request('MAIN.getBackups', {}, function(reply) {
		if (!reply.result) {
			App.Herlers.alert('Backups list failed to load. Please try again a bit later');
		}
		
		App.Ref.CONTENT.html(App.HTML.Build.backup_list(reply.data));
        App.Helpers.updateScreen();
		//$('#content').html(App.HTML.Build.backup_list(reply.data));
	});
}

App.Pages.loadStats = function()
{
	App.Env.world = 'STATS';
	App.Pages.prepareHTML();
}

App.Pages.STATS.prepareHTML = function()
{
    $('.row-filters').addClass('hidden');
    $('#actions-toolbar .do_action_new_entry').addClass('hidden');
    $('#actions-toolbar .stats-subbar').remove();
    $('#actions-toolbar .do_action_new_entry').after(App.Templates.get('SUBMENU', 'stats').finalize());
    
	$('#primary-nav-box .active').removeClass('active');
	$('#STATS').addClass('active');
	$('#new-entry-keyword').text(App.Helpers.getHumanTabName());
    document.title = 'Vesta | ' + App.Helpers.getHumanTabName();
    
    App.Ajax.request('STATS.getList', {}, function(reply) {
		if (!reply.result) {
			App.Herlers.alert('Stats list failed to load. Please try again a bit later');
		}
		
		App.Ref.CONTENT.html(App.HTML.Build.stats_list(reply.data));
        App.Helpers.updateScreen();
		//$('#content').html(App.HTML.Build.backup_list(reply.data));
	});
}

