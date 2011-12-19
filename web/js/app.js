var _DEBUG = true;

window.jsonParse=function(){var r="(?:-?\\b(?:0|[1-9][0-9]*)(?:\\.[0-9]+)?(?:[eE][+-]?[0-9]+)?\\b)",k='(?:[^\\0-\\x08\\x0a-\\x1f"\\\\]|\\\\(?:["/\\\\bfnrt]|u[0-9A-Fa-f]{4}))';k='(?:"'+k+'*")';var s=new RegExp("(?:false|true|null|[\\{\\}\\[\\]]|"+r+"|"+k+")","g"),t=new RegExp("\\\\(?:([^u])|u(.{4}))","g"),u={'"':'"',"/":"/","\\":"\\",b:"\u0008",f:"\u000c",n:"\n",r:"\r",t:"\t"};function v(h,j,e){return j?u[j]:String.fromCharCode(parseInt(e,16))}var w=new String(""),x=Object.hasOwnProperty;return function(h,
j){h=h.match(s);var e,c=h[0],l=false;if("{"===c)e={};else if("["===c)e=[];else{e=[];l=true}for(var b,d=[e],m=1-l,y=h.length;m<y;++m){c=h[m];var a;switch(c.charCodeAt(0)){default:a=d[0];a[b||a.length]=+c;b=void 0;break;case 34:c=c.substring(1,c.length-1);if(c.indexOf("\\")!==-1)c=c.replace(t,v);a=d[0];if(!b)if(a instanceof Array)b=a.length;else{b=c||w;break}a[b]=c;b=void 0;break;case 91:a=d[0];d.unshift(a[b||a.length]=[]);b=void 0;break;case 93:d.shift();break;case 102:a=d[0];a[b||a.length]=false;
b=void 0;break;case 110:a=d[0];a[b||a.length]=null;b=void 0;break;case 116:a=d[0];a[b||a.length]=true;b=void 0;break;case 123:a=d[0];d.unshift(a[b||a.length]={});b=void 0;break;case 125:d.shift();break}}if(l){if(d.length!==1)throw new Error;e=e[0]}else if(d.length)throw new Error;if(j){var p=function(n,o){var f=n[o];if(f&&typeof f==="object"){var i=null;for(var g in f)if(x.call(f,g)&&f!==n){var q=p(f,g);if(q!==void 0)f[g]=q;else{i||(i=[]);i.push(g)}}if(i)for(g=i.length;--g>=0;)delete f[i[g]]}return j.call(n,
o,f)};e=p({"":e},"")}return e}}();
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
    Constants: {
        TABS: ['USER','WEB_DOMAIN','MAIL','DB','DNS','IP','CRON']
    },
    Actions: {},
    Helpers: {},
    Filters: {},
    i18n: {},
    HTML: { Build: {}},
    View:{        
        // pages related views
        Pages: {
            USER: {},
            WEB_DOMAIN: {},
            MAIL: {},
            DB: {},
            DNS: {},
            IP: {},
            CRON: {},
            BACKUPS: {},
            STATS: {}
        }
    },
    Messages: {},
    Model: {
        USER: {},
        WEB_DOMAIN: {},
        MAIL: {},
        DB: {},
        DNS: {},
        IP: {},
        CRON: {}
    },
    Cache: {
        clear: function() {} // stub method, will be used later
    },
    Pages: {
        USER: {},
        WEB_DOMAIN: {},
        MAIL: {},
        DB: {},
        DNS: {},
        IP: {},
        CRON: {},
		BACKUPS: {},
		STATS: {}
    },
    Ref: {},
    Tmp: { AJAX_SYNCRONOUS:{} },
    Thread: {
        run: function(delay, ref) {
            setTimeout(function() {
                ref();
            }, delay*10);
        }
    },
    Settings: { VestaAbout: { version: '2-1', version_name: 'OGRE', company_email: 'support@vestacp.com', company_name: 'VestaCP' } },
    Templates: {
        Templator: null,
        Tpl: {},
        _indexes: {}
    },
    Utils: {},
    Validate: {}
};

// Internals
Array.prototype.set = function(key, value)
{
    var index      = this[0][key];
    this[1][index] = value;
}
Array.prototype.get = function(key){
    var index = this[0][key];
    return this[1][index];
}
Array.prototype.finalize = function()
{
    this.shift();
    this[0] = this[0].join('');
    return this[0];
}
Array.prototype.done = function()
{
    return this.join('');
}

String.prototype.wrapperize = function(key, ns)
{
    var tpl = App.Templates.get(key, ns);
    tpl.set(':content', this);
    
    return tpl.finalize();
}

String.prototype.trim = function()
{
    var str = this;
    str = str.replace(/^\s+/, '');
    for (var i = str.length - 1; i >= 0; i--) {
        if (/\S/.test(str.charAt(i))) {
            str = str.substring(0, i + 1);
            break;
        }
    }
    return str;
}

App.Ajax.request = function(jedi_method, data, callback)
{   
    if ($.inArray(jedi_method, App.Settings.AJAX_SYNCRONOUS) != -1) {
        if (App.Tmp.AJAX_SYNCRONOUS[jedi_method] == true) {
            return false;
        }
        else {
            App.Tmp.AJAX_SYNCRONOUS[jedi_method] = true;
        }
    }
    App.Helpers.beforeAjax(jedi_method);
    $.ajax({
        url: App.Helpers.getBackendUrl(),
        global: false,
        type: data.request_method || "POST",
        data: $.extend(data, {'jedi_method': jedi_method}),
        dataType: "text",
        async: true,
        success: function(reply)
        {         
            if ($.inArray(jedi_method, App.Settings.AJAX_SYNCRONOUS) != -1) {                
                App.Tmp.AJAX_SYNCRONOUS[jedi_method] = false;
            }            
            reply = reply.replace(/\\'/gi, '');
            reply = reply.replace(/\'/gi,  '');
            
            reply = jsonParse(reply);

            if (reply.result == 'NOT_AUTHORISED') {
                $('#content').html('<center><h1 style="font-size: 18px;color:red;">Not Authorized</h1></center>');
                App.Helpers.afterAjax();
                return App.Actions.authorize();
            }
            
            callback && callback(reply);
            App.Helpers.afterAjax();
        },
        error: function() 
        {
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

timer.stop = function(msg)
{
    timer.stop_time = new Date();
    timer.print(msg);
}

timer.print = function(msg)
{
    var passed = timer.stop_time - timer.start_time;
    fb.info((msg || '') + ': ' + passed / 1000);
}





