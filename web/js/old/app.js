/**
 * App namespace
 */
var App = {
    Ajax: {},
    Env: {
        BROWSER: {
            type: 'unknown-type',
            version: 'version', // prefixed with type will be "unknown-version"
            os: 'unknown-os'
        },
        getWorldName: function() {
            return App.Env.world.toLowerCase();
        }
    },
    Core: {},
    Bash: {},
    Console: {},
    Constants: {},
    Actions: {},
    Helpers: {},
    Filters: {},
    i18N: {},
    HTML: {
            Build: {}
        },
    View:{
        
        // pages related views
        Pages: {
            USER: {},
            WEBDOMAIN: {},
            MAIL: {},
            DB: {},
            DNS: {},
            IP: {},
            CRON: {}
        }
    },
    Messages: {},
    Model: {
        USER: {},
        WEBDOMAIN: {},
        MAIL: {},
        DB: {},
        DNS: {},
        IP: {},
        CRON: {}
    },
    Cache: {
        clear: function(){} // stub method, will be used later
    },
    Pages: {
        USER: {},
        WEBDOMAIN: {},
        MAIL: {},
        DB: {},
        DNS: {},
        IP: {},
        CRON: {}
    },
    Ref: {},
    Tmp: {},
    Thread: {
        run: function(delay, ref){
            setTimeout(function(){
                ref();
            }, delay*10);
        }
    },
    Settings: {},
    Templates: {
        Templator: null,
        Tpl: {},
        _indexes: {}
    },
    Utils: {},
    Validate: {}
};

//
//	AJAX global method
//
App.Ajax.request = function(jedi_method, data, callback){
    App.Helpers.beforeAjax(jedi_method);
    $.ajax({
        url: function() {
            var url_parts = location.href.split('/');
            if (url_parts[url_parts.length -1] == 'index.html') {
                url_parts[url_parts.length -1] = 'dispatch.php';
            }
            else {
                url_parts.push('dispatch.php');
            }
            return url_parts.join('/');
        }(),
        global: false,
        type: data.request_method || "POST",
        data: $.extend(data, {'jedi_method': jedi_method}),
        dataType: "json",
        async:true,
        success: function(reply){
            App.Helpers.afterAjax();
            callback && callback(reply);
        },
        error: function() {
            App.View.popup('error');
        }
    });
}






