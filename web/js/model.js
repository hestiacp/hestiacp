App.Model.DNS.loadList = function(){
    App.Ajax.request('DNS.getList', {}, App.View.listItems);
}

App.Model.IP.loadList = function(){
    App.Ajax.request('IP.getList', {}, App.View.listItems);
}

App.Model.add = function(values, source_json) {    
    var method = App.Settings.getMethodName('add');
    App.Ajax.request(method, {
        spell: $.toJSON(values)
    }, function(reply){
        if(!reply.result) {
            alert('FALSE');
        }
    });
}

App.Model.update = function(values, source_json) {    
    var method = App.Settings.getMethodName('update');
    App.Ajax.request(method, {
        'old': source_json,
        'new': App.Helpers.toJSON(values)
    }, function(reply){
        if(!reply.result) {
            alert('FALSE');
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
