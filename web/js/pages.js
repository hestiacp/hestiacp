App.Pages.init = function(){
    App.Ajax.request('MAIN.getInitial', {}, function(reply){
        App.Env.initialParams = reply.data;
        //App.Helpers.updateInitial();
    });
        
    App.Pages.prepareHTML();
    
    $('.section.active').removeClass('active');
    $('#'+App.Env.world).addClass('active');
}

App.Pages.prepareHTML = function()
{
    if ('undefined' != typeof App.Pages[App.Env.world].prepareHTML) {
        App.Pages.prepareHTML();
    }  
    else {        
        App.Model[App.Env.world].loadList();
    }
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

App.Pages.DNS.edit = function(elm) {
    var options = elm.find('.source').val();
    fb.warn(elm);
    fb.warn(options);
    var tpl = App.HTML.Build.dns_form(options);
    elm.replaceWith(tpl);
}
