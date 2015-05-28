/**
 *
 * @author: Malishev Dmitry <dima.malishev@gmail.com>
 */
App.Templates.html = {
    WEB: {
        hint: ['']
    },
    // file manager
    FM: {
        entry_line: ['<li class="dir">\
                        <span class="marker"></span>\
                        <span class="icon ~!:ITEM_TYPE~!" ></span>\
                        <input type="hidden" class="source" value=\'~!:SOURCE~!\'/>\
                        <span class="filename ripple" ~!:CL_ACTION_1~!>~!:NAME~!</span>\
                        <span class="mode">~!:PERMISSIONS~!</span>\
                        <span class="owner">~!:OWNER~!</span>\
                        <span class="size">~!:SIZE~!</span>\
                        <span class="date">~!:DATE~!</span>\
                        <span class="time">~!:TIME~!</span>\
                    </li>'],
        popup_delete: ['<div class="confirm-box delete popup-box">\
                            <div class="message">Are you sure you want to delete file <span class="title">"~!:FILENAME~!"</span>?</div>\
                                <div class="controls">\
                            <p class="cancel" onClick="FM.popupClose();">cancel</p>\
                            <p class="ok" onClick="FM.confirmDelete();">delete</p>\
                            </div>\
                        </div>'],
        popup_rename: ['<div class="confirm-box rename warning">\
                            <div class="message">Rename file <span class="title">"~!:FILENAME~!"</span></div>\
                            <!-- div class="warning">File <span class="title">"reading.txt"</span> already exists</div -->\
                            <div class="warning"></div>\
                            <div class="actions">\
                                <input type="text" id="rename-title" class="new-title" />\
                            </div>\
                            <div class="controls">\
                                <p class="cancel" onClick="FM.popupClose();">cancel</p>\
                                <p class="ok" onClick="FM.confirmRename();">rename</p>\
                            </div>\
                            <div class="controls replace">\
                                <p class="cancel" onClick="FM.popupClose();">cancel</p>\
                                <p class="ok" onClick="FM.confirmRename();">rename</p>\
                            </div>\
                        </div>'],
        popup_create_file: ['<div class="confirm-box rename warning">\
                            <div class="message">Create file</div>\
                            <!-- div class="warning">File <span class="title">"reading.txt"</span> already exists</div -->\
                            <div class="warning"></div>\
                            <div class="actions">\
                                <input type="text" id="rename-title" class="new-title" />\
                            </div>\
                            <div class="controls replace">\
                                <p class="cancel" onClick="FM.popupClose();">cancel</p>\
                                <p class="ok" onClick="FM.confirmCreateFile();">create</p>\
                            </div>\
                        </div>'],
        popup_create_dir: ['<div class="confirm-box rename warning">\
                            <div class="message">Create directory</div>\
                            <!-- div class="warning">File <span class="title">"reading.txt"</span> already exists</div -->\
                            <div class="warning"></div>\
                            <div class="actions">\
                                <input type="text" id="rename-title" class="new-title" />\
                            </div>\
                            <div class="controls replace">\
                                <p class="cancel" onClick="FM.popupClose();">cancel</p>\
                                <p class="ok" onClick="FM.confirmCreateDir();">create</p>\
                            </div>\
                        </div>']
    }
};

// Internals
var Tpl = App.Templates;

var Templator = function()
{
    var init = function()
    {
        fb.info('Templator work');
        Templator.splitThemAll();
        Templator.freezeTplIndexes();
    };


    /**
     * Split the tpl strings into arrays
     */
    Templator.splitThemAll = function(){
        fb.info('splitting tpls');
        jQuery.each(App.Templates.html, function(o){
            //try{
            var tpls = App.Templates.html[o];
            jQuery.each(tpls, function(t){
                tpls[t] = tpls[t][0].split('~!');
            });
            //}catch(e){fb.error('%o %o', o, e);}
        });

    },

    /**
     * Iterates tpls
     */
    Templator.freezeTplIndexes = function(){
        fb.info('freezing tpl keys');
        jQuery.each(App.Templates.html, Templator.cacheTplIndexes);
    },

    /**
     * Grab the tpl group key and process it
     */
    Templator.cacheTplIndexes = function(key)
    {
        var tpls = App.Templates.html[key];

        jQuery.each(tpls, function(o)
        {
            var tpl = tpls[o];
            Templator.catchIndex(key, o, tpl);
        });
    },

    /**
     * Set the indexes
     */
    Templator.catchIndex = function(key, ref_key, tpl)
    {
        'undefined' == typeof App.Templates._indexes[key] ? App.Templates._indexes[key] = {} : false;
        'undefined' == typeof App.Templates._indexes[key][ref_key] ?
        App.Templates._indexes[key][ref_key] = {} : false;

        jQuery(tpl).each(function(index, o) {
            if (':' == o.charAt(0)) {
                App.Templates._indexes[key][ref_key][o.toString()] = index;
            }
        });
    }

    /**
     * Get concrete templates
     */
    init();
    return Templator;
};

Templator.getTemplate = function(ns, key){
    return [
        App.Templates._indexes[ns][key],
        App.Templates.html[ns][key].slice(0)
    ];
}
// init templator
Tpl.Templator = Templator();

Tpl.get = function(key, group){
    return Tpl.Templator.getTemplate(group, key);
}
