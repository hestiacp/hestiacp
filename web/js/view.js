App.View.popup = function(content) 
{
    var tpl = App.Templates.get('popup', 'general');
    if ('undefined' != typeof App.Templates.html.popup[content]) {
        var content = App.Templates.get(content, 'popup').finalize();
    } 
    
    tpl.set(':content', content);
    tpl.set(':STYLE', 'height:'+$(window).height()+'px');
    $('#popup-bg, #popup').remove();
    $(document.body).append(tpl.finalize());
}

App.View.closePopup = function()
{
    $('.black_overlay').remove();
    $('.popup_content').remove();
}

App.View.updateInitialInfo = function(key, object) 
{
    var expr = '.'+key;
    var object = parseInt(object, 10);
    var html = object + ' ' + App.Messages.get(key, (object > 1)); 
    $(expr).html(html);
}

App.View.listItems = function(reply){    
    var acc = [];
    var build_method = App.Env.getWorldName() + '_entry';
    var data = reply.data;   
    // TODO: fix it data.data
    $.each(data, function(key) 
    {
        var o = data[key];
        fb.warn(key);
        acc[acc.length++] = App.HTML.Build[build_method](o, key);
    });
    
    var html = acc.done().wrapperize('ENTRIES_WRAPPER', App.Env.getWorldName());
    App.Ref.CONTENT.html(html);
    //App.Helpers.updateScreen();
}
