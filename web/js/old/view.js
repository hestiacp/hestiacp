App.View.start = function(){
    
};

App.View.showDatabases = function(databases){
    var tpl = App.HTML.makeDatabases(databases);
    $('#navigation').html(tpl.wrapperize('database_wrap', 'database'));
}

App.View.openDatabase = function(data, ref, db_name){
    var tpl = App.HTML.makeDbTableList(data);
    $('.databases .tables').remove();
    $('.databases .active').removeClass('active');
    $(ref).after(tpl.wrapperize('database_table_wrap', 'database'));
    
    $(ref).attr('className', 'active do.closeDatabase('+db_name+')"');
}

App.View.openTable = function(data, ref, table_name){
    var tpl = App.HTML.makeDbFieldsList(data);
    $(ref).next('.fields').remove();
    $(ref).after(tpl.wrapperize('database_field_wrap', 'database'));
    
    $(ref).attr('className', 'active do.closeTable('+table_name+')"');
    
    App.Helpers.updateScreen();
}

App.View.resultReturned = function(reply){
    if(reply.result){
        $('#results').text(reply.data);
    }else{
        $('#results').text(reply.message);
    }
}

App.View.Pages.IP.list = function(reply){
    var acc = [];
    $(reply.data).each(function(i, o){
        acc[acc.length++] = App.HTML.Build.ip_entry(o);
    });
    
    var html = acc.done().wrapperize('ENTRIES_WRAPPER', 'ip');
    App.Ref.CONTENT.html(html);
    App.Helpers.updateScreen();
}

App.View.Pages.DNS.list = function(reply){
    var acc = [];
    $(reply.data).each(function(i, o){
        acc[acc.length++] = App.HTML.Build.dns_entry(o);
    });
    
    var html = acc.done().wrapperize('ENTRIES_WRAPPER', 'dns');
    App.Ref.CONTENT.html(html);
    App.Helpers.updateScreen();
}

App.View.popup = function(content) {
    var tpl = App.Templates.get('popup', 'general');
    if ('undefined' != typeof App.Templates.html.popup[content]) {
        var content = App.Templates.get(content, 'popup').finalize();
    } 
    
    tpl.set(':content', content);
    $('#popup-bg, #popup').remove();
    $(document.body).append(tpl.finalize());
}

App.View.closePopup = function(){
    $('.black_overlay').remove();
    $('.popup_content').remove();
}

App.View.updateInitialInfo = function(key, object) {
    var expr = '.'+key;
    var object = parseInt(object, 10);
    var html = object + ' ' + App.Messages.get(key, (object > 1)); 
    $(expr).html(html);
}