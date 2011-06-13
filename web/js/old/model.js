App.Model.DNS.loadList = function(){
    App.Ajax.request('DNS.getList', {}, App.View.Pages.DNS.list);
}

App.Model.DNS.update = function(values, source_json) {
    App.Ajax.request('DNS.update', {
        'source': source_json,
        'target': App.Helpers.toJSON(values)
    }, function(reply){
        if(!reply.result) {
            App.Pages.DNS.notSaved(reply);
        }
    });
}

App.Model.IP.loadList = function(){
    App.Ajax.request('IP.getList', {}, App.View.Pages.IP.list);
}

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
}
