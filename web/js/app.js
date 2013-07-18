/**
 * jQuery.browser.mobile (http://detectmobilebrowser.com/)
 *
 * jQuery.browser.mobile will be true if the browser is a mobile device
 *
 **/
(function(a){(jQuery.browser=jQuery.browser||{}).mobile=/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))})(navigator.userAgent||navigator.vendor||window.opera);

/*! jQuery JSON plugin 2.4.0 | code.google.com/p/jquery-json */
(function(jQuery){'use strict';var escape=/["\\\x00-\x1f\x7f-\x9f]/g,meta={'\b':'\\b','\t':'\\t','\n':'\\n','\f':'\\f','\r':'\\r','"':'\\"','\\':'\\\\'},hasOwn=Object.prototype.hasOwnProperty;jQuery.toJSON=typeof JSON==='object'&&JSON.stringify?JSON.stringify:function(o){if(o===null){return'null';}
var pairs,k,name,val,type=jQuery.type(o);if(type==='undefined'){return undefined;}
if(type==='number'||type==='boolean'){return String(o);}
if(type==='string'){return jQuery.quoteString(o);}
if(typeof o.toJSON==='function'){return jQuery.toJSON(o.toJSON());}
if(type==='date'){var month=o.getUTCMonth()+1,day=o.getUTCDate(),year=o.getUTCFullYear(),hours=o.getUTCHours(),minutes=o.getUTCMinutes(),seconds=o.getUTCSeconds(),milli=o.getUTCMilliseconds();if(month<10){month='0'+month;}
if(day<10){day='0'+day;}
if(hours<10){hours='0'+hours;}
if(minutes<10){minutes='0'+minutes;}
if(seconds<10){seconds='0'+seconds;}
if(milli<100){milli='0'+milli;}
if(milli<10){milli='0'+milli;}
return'"'+year+'-'+month+'-'+day+'T'+
hours+':'+minutes+':'+seconds+'.'+milli+'Z"';}
pairs=[];if(jQuery.isArray(o)){for(k=0;k<o.length;k++){pairs.push(jQuery.toJSON(o[k])||'null');}
return'['+pairs.join(',')+']';}
if(typeof o==='object'){for(k in o){if(hasOwn.call(o,k)){type=typeof k;if(type==='number'){name='"'+k+'"';}else if(type==='string'){name=jQuery.quoteString(k);}else{continue;}
type=typeof o[k];if(type!=='function'&&type!=='undefined'){val=jQuery.toJSON(o[k]);pairs.push(name+':'+val);}}}
return'{'+pairs.join(',')+'}';}};jQuery.evalJSON=typeof JSON==='object'&&JSON.parse?JSON.parse:function(str){return eval('('+str+')');};jQuery.secureEvalJSON=typeof JSON==='object'&&JSON.parse?JSON.parse:function(str){var filtered=str.replace(/\\["\\\/bfnrtu]/g,'@').replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,']').replace(/(?:^|:|,)(?:\s*\[)+/g,'');if(/^[\],:{}\s]*jQuery/.test(filtered)){return eval('('+str+')');}
throw new SyntaxError('Error parsing JSON, source is not valid.');};jQuery.quoteString=function(str){if(str.match(escape)){return'"'+str.replace(escape,function(a){var c=meta[a];if(typeof c==='string'){return c;}
c=a.charCodeAt();return'\\u00'+Math.floor(c/16).toString(16)+(c%16).toString(16);})+'"';}
return'"'+str+'"';};}(jQuery));

/**
 *
 * @author: Malishev Dmitry <dima.malishev@gmail.com>
 */
var _DEBUG = true;
var _DEBUG_LEVEL = 'ALL';
// possible levels: ALL, IMPORTANT
var Error = {FATAL: 1, WARNING: 0, NORMAL: -1};

//
//  GLOBAL SETTINGS
//
GLOBAL = {};
GLOBAL.FTP_USER_PREFIX  = 'admin_';
GLOBAL.DB_USER_PREFIX   = 'admin_';
GLOBAL.DB_DBNAME_PREFIX = 'admin_';
GLOBAL.AJAX_URL = '';

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

//
var App = {
    // Main namespases for page specific functions
    // Core namespaces
    Ajax: { Busy: {} },
    Core: {},
    // Actions. More widly used funcs
    Actions: {
        DB: {},
        WEB: {}
    },
    // Utilities
    Helpers: {},
    HTML: {Build: {}},
    Filters: {},
    Env: {
        lang: GLOBAL.lang,
    },
    i18n: {},
    Listeners: {
        DB: {},
        WEB: {}
    },
    View:{
        HTML: {
            Build: {}
        },
        // pages related views
    },
    Cache: {
        clear: function(){} // TODO: stub method, will be used later
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
    Settings: { GLOBAL: {}, General: {}},
    Templates: {
        Templator: null,
        Tpl: {},
        _indexes: {}
    }
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



App.Ajax.request = function(method, data, callback, onError){
    // this will prevent multiple ajaxes on user clicks
    /*if (App.Helpers.isAjaxBusy(method, data)) {
        fb.warn('ajax request ['+method+'] is busy');
        return;
    }*/
    //App.Helpers.setAjaxBusy(method, data);
    data = data || {};

    jQuery.ajax({
        url: GLOBAL.ajax_url,
        global: false,
        type: data.request_method || "GET",
        data: jQuery.extend(data, {'action': method}),
        dataType: "text boost",
        converters: {
            "text boost": function(value) {
                value = value.trim();
                return jsonParse(value);
        }},
        async: true,
        cache: false,
        error: function(jqXHR, textStatus, errorThrown)
        {
            onError && onError();
            if ('undefined' != typeof onError) {
                fb.error(textStatus);
            }
        },
        complete: function()
        {
            //App.Helpers.setAjaxFree(method, data);
        },
        success: function(reply)
        {
            //App.Helpers.setAjaxFree(method, data);
            try {
                callback && callback(reply);
            }
            catch(e) {
                alert('GENERAL ERROR: '+e);
                //App.Helpers.generalError();
            }
        }
    });
}

jQuery.extend({
    keys:    function(obj){
        if (!obj) {
            return [];
        }
        var a = [];
        jQuery.each(obj, function(k){ a.push(k) });
        return a;
    }
})


App.Core.create_hidden_form = function(action){
    var form = jQuery('<form>', {
            id     : 'hidden-form',
            method : 'post',
            action : action
        });
    jQuery('body').append(form);

    return form;
};

App.Core.extend_from_json = function(elm, data, prefix){
    elm      = jQuery(elm);
    var data = App.Core.flatten_json(data, prefix);
    var keys = jQuery.keys(data);
    for(var i=0, cnt=keys.length; i<cnt; i++)
    {
        elm.append(jQuery('<input>', {
            name : keys[i],
            value: data[keys[i]],
            type : 'hidden'
        }));
    }

    return elm;
}

App.Core.flatten_json = function(data, prefix){
    var keys   = jQuery.keys(data);
    var result = {};

    prefix || (prefix = '');

    if(keys.length)
    {
        for(var i=0, cnt=keys.length; i<cnt; i++)
        {
            var value = data[keys[i]];
            switch(typeof(value))
            {
                case 'function': break;
                case 'object'  : result = jQuery.extend(result, App.Core.flatten_json(value, prefix + '[' + keys[i] + ']')); break;
                default        : result[prefix + '[' + keys[i] + ']'] = value;
            }
        }
        return result;
    }
    else
    {
        return false;
    }
}

//
// Cookies adapter
// Allow to work old pages realisations of cookie requests
//
function createCookie(name, value, expire_days) {
    jQuery.cookie(name, value, { expires: expire_days});
}

function readCookie(name) {
    jQuery.cookie(name);
}

function eraseCookie(name) {
    jQuery.removeCookie(name);
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
