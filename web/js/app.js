var _DEBUG = true;


(function($){$.toJSON=function(o)
{if(typeof(JSON)=='object'&&JSON.stringify)
return JSON.stringify(o);var type=typeof(o);if(o===null)
return"null";if(type=="undefined")
return undefined;if(type=="number"||type=="boolean")
return o+"";if(type=="string")
return $.quoteString(o);if(type=='object')
{if(typeof o.toJSON=="function")
return $.toJSON(o.toJSON());if(o.constructor===Date)
{var month=o.getUTCMonth()+1;if(month<10)month='0'+month;var day=o.getUTCDate();if(day<10)day='0'+day;var year=o.getUTCFullYear();var hours=o.getUTCHours();if(hours<10)hours='0'+hours;var minutes=o.getUTCMinutes();if(minutes<10)minutes='0'+minutes;var seconds=o.getUTCSeconds();if(seconds<10)seconds='0'+seconds;var milli=o.getUTCMilliseconds();if(milli<100)milli='0'+milli;if(milli<10)milli='0'+milli;return'"'+year+'-'+month+'-'+day+'T'+
hours+':'+minutes+':'+seconds+'.'+milli+'Z"';}
if(o.constructor===Array)
{var ret=[];for(var i=0;i<o.length;i++)
ret.push($.toJSON(o[i])||"null");return"["+ret.join(",")+"]";}
var pairs=[];for(var k in o){var name;var type=typeof k;if(type=="number")
name='"'+k+'"';else if(type=="string")
name=$.quoteString(k);else
continue;if(typeof o[k]=="function")
continue;var val=$.toJSON(o[k]);pairs.push(name+":"+val);}
return"{"+pairs.join(", ")+"}";}};$.evalJSON=function(src)
{if(typeof(JSON)=='object'&&JSON.parse)
return JSON.parse(src);return eval("("+src+")");};$.secureEvalJSON=function(src)
{if(typeof(JSON)=='object'&&JSON.parse)
return JSON.parse(src);var filtered=src;filtered=filtered.replace(/\\["\\\/bfnrtu]/g,'@');filtered=filtered.replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,']');filtered=filtered.replace(/(?:^|:|,)(?:\s*\[)+/g,'');if(/^[\],:{}\s]*$/.test(filtered))
return eval("("+src+")");else
throw new SyntaxError("Error parsing JSON, source is not valid.");};$.quoteString=function(string)
{if(string.match(_escapeable))
{return'"'+string.replace(_escapeable,function(a)
{var c=_meta[a];if(typeof c==='string')return c;c=a.charCodeAt();return'\\u00'+Math.floor(c/16).toString(16)+(c%16).toString(16);})+'"';}
return'"'+string+'"';};var _escapeable=/["\\\x00-\x1f\x7f-\x9f]/g;var _meta={'\b':'\\b','\t':'\\t','\n':'\\n','\f':'\\f','\r':'\\r','"':'\\"','\\':'\\\\'};})(jQuery);

/**
 * Init debug, grabs console object if accessible, or makes dummy debugger
 */
var fb = _DEBUG && 'undefined' != typeof(console) ? console : {
    log         : function(){},
    debug       : function(){},
    info        : function(){},
    warn        : function(){},
    error       : function(){},
    assert      : function(){},
    dir         : function(){},
    dirxml      : function(){},
    trace       : function(){},
    group       : function(){},
    groupEnd    : function(){},
    time        : function(){},
    timeEnd     : function(){},
    profile     : function(){},
    profileEnd  : function(){},
    count       : function(){},
    msg         : function(){}
};

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
    i18n: {},
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

// Internals
Array.prototype.set = function(key, value){
    var index = this[0][key];
    this[1][index] = value;
}
Array.prototype.get = function(key){
    var index = this[0][key];
    return this[1][index];
}
Array.prototype.finalize = function(){
    this.shift();
    this[0] = this[0].join('');
    return this[0];
}
Array.prototype.done = function(){
    return this.join('');
}

String.prototype.wrapperize = function(key, ns){
    var tpl = App.Templates.get(key, ns);
    tpl.set(':content', this);
    
    return tpl.finalize();
}

App.Ajax.request = function(jedi_method, data, callback){
    App.Helpers.beforeAjax(jedi_method);
    $.ajax({
        url: function() {
            var url_parts = location.href.replace('#', '').split('/');
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


/**
 * Timer for profiling
 */
var timer = {};
timer.start = function()
{
    timer.start_time = new Date();
}

timer.stop = function( msg )
{
    timer.stop_time = new Date();
    timer.print( msg );
}

timer.print = function( msg )
{
    var passed = timer.stop_time - timer.start_time;
    fb.info( msg || '' + passed / 1000 );
}





