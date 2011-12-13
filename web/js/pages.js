App.Pages.init = function()
{
    App.Ajax.request('MAIN.getInitial', {}, function(reply) {
        App.Env.initialParams = reply.data;
        App.Helpers.updateInitial();
    });    
    App.Pages.prepareHTML();    
    $('.section.active').removeClass('active');
    $('#'+App.Env.world).addClass('active');
    
    if (cookieEnabled()) {
        setCookie('tab', App.Env.world);
    }
}

App.Pages.prepareHTML = function()
{
    if ('undefined' != typeof App.Pages[App.Env.world].prepareHTML) {
        App.Pages.prepareHTML();
    }  
    else {        
        App.Model[App.Env.world].loadList();
    }
    $('#new-entry-keyword').text(App.Helpers.getHumanTabName());
    document.title = 'Vesta | ' + App.Helpers.getHumanTabName();
}

App.Pages.DNS.showSubform = function(ref) 
{
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
    App.Ref.CONTENT.prepend(tpl);
    App.Helpers.updateScreen(); 
    $('#'+form_id).find('.ns-entry, .additional-ns-add').addClass('hidden');   
}

App.Pages.WEB_DOMAIN.new_entry = function(evt)
{ 
    var form_id = App.Constants[App.Env.world + '_FORM_ID'];
    $('#'+form_id).remove();
    var build_method = App.Env.getWorldName() + '_form';
    var tpl = App.HTML.Build[build_method]({}, form_id);
    App.Ref.CONTENT.prepend(tpl);
    App.Helpers.updateScreen(); 
    $('#'+form_id).find('.ns-entry, .additional-ns-add').addClass('hidden');   
    var ssl_key_upload  = App.HTML.Build.ssl_key_file();
    var ssl_cert_upload = App.HTML.Build.ssl_cert_file();
    $('#'+form_id).find('.ssl-key-input-dummy:first').replaceWith(ssl_key_upload);
    $('#'+form_id).find('.ssl-cert-input-dummy:first').replaceWith(ssl_cert_upload);
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
    tpl = tpl.replace('<span class="ssl-key-input-dummy">...</span>', ssl_key_upload);
    tpl = tpl.replace('<span class="ssl-cert-input-dummy">...</span>', ssl_cert_upload);
    elm.replaceWith(tpl);
    
    App.Helpers.disableNotEditable();
    App.Helpers.updateScreen();
}

App.Pages.WEB_DOMAIN.setSSL = function(type, frame)
{
    var txt = App.Helpers.evalJSON(content);
    var ref = frame.frameElement;
    $(ref).next('textarea').val(frame.document.getElementById('result').value);
}
