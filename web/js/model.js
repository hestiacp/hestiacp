App.Model.DNS.loadList = function(callback)
{
	if (!callback) {
		callback = App.View.listItems;
	}
    App.Ajax.request('DNS.getList', {}, callback);
}

App.Model.IP.loadList = function(callback)
{
	if (!callback) {
		callback = App.View.listItems;
	}
    App.Ajax.request('IP.getList', {}, callback);
}

App.Model.USER.loadList = function(callback)
{
	if (!callback) {
		callback = App.View.listItems;
	}
    App.Ajax.request('USER.getList', {}, callback);
}

App.Model.WEB_DOMAIN.loadList = function(callback)
{
	if (!callback) {
		callback = App.View.listItems;
	}
    App.Ajax.request('WEB_DOMAIN.getList', {}, callback);
}

App.Model.MAIL.loadList = function(callback)
{
    //App.Ajax.request('MAIL.getList', {}, App.View.listItems);
    App.Ref.CONTENT.html('<center><h1 style="padding-top: 20px; font-size: 28px; position: absolute; margin-left: 351px; color: white; text-shadow: 2px 1px 1px rgb(65, 124, 213);">Under maintanance</h1><img width="900px" src="'+App.Helpers.generateUrl('images/Asteroid_Vesta.jpg')+'"></center>');
}

App.Model.DB.loadList = function(callback)
{
	if (!callback) {
		callback = function(reply) {
			var acc = [];
			var build_method = App.Env.getWorldName() + '_entry';
			var data = reply.data;   
			// TODO: fix it data.data
			$.each(data, function(key) 
			{
				var db_list = data[key];
				fb.warn('KEY: %o', key);
				fb.warn('DATA: %o', data[key]);
				var tpl_divider = App.Templates.get('DIVIDER', 'db');
				tpl_divider.set(':TYPE', key);
				acc[acc.length++] = tpl_divider.finalize();
				$(db_list).each(function(i, o)
				{
					acc[acc.length++] = App.HTML.Build[build_method](o, key);
				});
				
				/*var o = data[key];
				fb.warn(key);
				acc[acc.length++] = App.HTML.Build[build_method](o, key);*/
			});
			
			var html = acc.done().wrapperize('ENTRIES_WRAPPER', App.Env.getWorldName());
			App.Ref.CONTENT.html(html);
			App.Helpers.updateScreen();
		};
	}
    App.Ajax.request('DB.getList', {}, callback);
}

App.Model.CRON.loadList = function(callback)
{
	if (!callback) {
		callback = App.View.listItems;
	}
    App.Ajax.request('CRON.getList', {}, callback);
}


App.Model.add = function(values, source_json) 
{    
    var method = App.Settings.getMethodName('add');

    App.Ajax.request(method, {
        spell: $.toJSON(values)
    }, function(reply){
        if(!reply.result) {
            App.Helpers.Warn('Changes were not applied ' + App.Helpers.toJSON(reply.errors) );
        }
        else {
            App.Pages.prepareHTML();
        }
    });
}

App.Model.remove = function(world, elm)
{
    var method = App.Settings.getMethodName('delete');     
    App.Ajax.request(method,
    {
        spell: $('.source', elm).val()
    },
    function(reply) 
    {
        if (!reply.result) {
            App.Helpers.Warn('Changes were not applied');
        }
        else {
            $(elm).remove();
        }
    });
}

App.Model.update = function(values, source_json, elm) 
{    
    var method = App.Settings.getMethodName('update');
    var build_method = App.Env.getWorldName() + '_entry';
    var params = {
        'old': source_json,
        'new': App.Helpers.toJSON(values)
    };
    App.Ajax.request(method, params, function(reply){
        if(!reply.result) {
            var tpl = App.HTML.Build[build_method](App.Helpers.evalJSON(source_json));
            $(elm).replaceWith(tpl);
            App.Helpers.updateScreen();
            App.Helpers.Warn('Changes were not applied. ' + reply.message);
        }
        else {
            var tpl = App.HTML.Build[build_method]($.extend(App.Helpers.evalJSON(source_json), values));
            $(elm).replaceWith(tpl);
            App.Helpers.updateScreen();
        }
    });
}

/*
App.Model.IP.update = function(values, source_json) {
    App.Ajax.request('IP.update', {
        'source': source_json,
        'target': App.Helpers.toJSON(values)
    }, function(reply){
        if(!reply.result) {
            App.Pages.IP.ipNotSaved(reply);
        }
    });
}

App.Model.IP.add = function(values) {
    App.Ajax.request('IP.add', {
        'target': App.Helpers.toJSON(values)
    }, function(reply){
        if(!reply.result) {
            App.Helpers.alert(reply.message)
        }
    });
}

App.Model.IP.remove = function(values_json, elm) {
    App.Ajax.request('IP.remove', {
        'target': values_json
    }, function(reply){
        if(!reply.result) {
            App.Helpers.alert(reply.message);
        }
        else {
            elm.remove();
        }
    });
}*/
