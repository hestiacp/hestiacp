/**
 *
 * @author: Malishev Dmitry <dima.malishev@gmail.com>
 */
App.Templates.html = {
    WEB: {
        hint: ['']
    },
    // file manager
    //

//<input id="check~!:index~!" class="ch-toggle2" type="checkbox" name="domain[]" value="~!:index3~!">\    
    
    FM: {
        entry_line: ['<li class="dir">\
                        <span class="marker">\
                        </span>\
                        <span class="icon ~!:ITEM_TYPE~!" ></span>\
                        <input type="hidden" class="source" value=\'~!:SOURCE~!\'/>\
                        <span class="filename ripple" ~!:CL_ACTION_1~!>~!:NAME~!</span>\
                        <span class="mode">~!:PERMISSIONS~!</span>\
                        <span class="owner">~!:OWNER~!</span>\
                        <span class="size-unit">~!:SIZE_UNIT~!</span>\
                        <span class="size-value">~!:SIZE_VALUE~!</span>\
                        <span class="date">~!:DATE~!</span>\
                        <span class="time">~!:TIME~!</span>\
                        <span class="subcontext-control ~!:SUBMENU_CLASS~!" onClick="FM.toggleSubContextMenu(this)">&#8226;&#8226;&#8226;&nbsp;\
                        <ul class="subcontext-menu subcontext-menu-hidden"><li onClick="FM.downloadFileFromSubcontext(this);">Download</li><li onClick="FM.editFileFromSubcontext(this);">Edit</li></ul>\
                        </span>\
                    </li>'],
        popup_alert: ['<div class="confirm-box alarm popup-box">\
                            <div class="message">~!:TEXT~!</div>\
                                <div class="controls">\
                            <p class="ok" onClick="FM.popupClose();">close</p>\
                            </div>\
                        </div>'],
        popup_bulk: ['<div class="confirm-box alarm popup-box">\
                            <div class="message">~!:ACTION~!: <br />~!:TEXT~!</div>\
                            <div class="results"></div>\
                                <div class="controls">\
                            <!-- p class="ok" onClick="FM.popupClose();">close</p -->\
                            <p><img src="/images/in_progress.gif"></p>\
                            </div>\
                        </div>'],
        popup_delete: ['<div class="confirm-box delete popup-box">\
                            <div class="message">Are you sure you want to delete <span class="title">"~!:FILENAME~!"</span>?</div>\
                                <div class="controls">\
                            <p class="cancel" onClick="FM.popupClose();">cancel</p>\
                            <p class="ok" onClick="FM.confirmDelete();">delete</p>\
                            </div>\
                        </div>'],
        popup_copy: ['<div class="confirm-box copy popup-box">\
                            <div class="message">Are you sure you want to copy <span class="title">"~!:SRC_FILENAME~!"</span> into:</div>\
                            <div class="actions">\
                                <input type="text" id="copy_dest" value="~!:DST_FILENAME~!" class="new-title">\
                            </div>\
                            <div class="message">existing files will be replaced</div>\
                                <div class="controls">\
                            <p class="cancel" onClick="FM.popupClose();">cancel</p>\
                            <p class="ok" onClick="FM.confirmCopyItems();">copy</p>\
                            </div>\
                        </div>'],
        popup_rename: ['<div class="confirm-box rename warning">\
                            <div class="message">Original name: <span class="title">"~!:FILENAME~!"</span></div>\
                            <!-- div class="warning">File <span class="title">"reading.txt"</span> already exists</div -->\
                            <div class="warning warning-message"></div>\
                            <div class="actions">\
                                <input type="text" id="rename-title" class="new-title"  value="~!:NEW_NAME~!" />\
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

        popup_pack: ['<div class="confirm-box pack warning">\
                            <div class="message">Pack <span class="title">"~!:FILENAME~!"</span> into:</div>\
                            <div class="actions">\
                                <input type="text" id="pack-destination" class="new-title" value="~!:DST_DIRNAME~!">\
                            </div>\
                            <div class="warning warning-message"></div>\
                            <!-- div class="actions">\
                                <label><input type="checkbox" name="overwrite" class="title" />Overwrite exising files</label>\
                            </div -->\
                            <div class="controls">\
                                <p class="cancel" onClick="FM.popupClose();">cancel</p>\
                                <p class="ok" onClick="FM.confirmPackItem();">Pack</p>\
                            </div>\
                        </div>'],

        popup_unpack: ['<div class="confirm-box unpack warning">\
                            <div class="message">Extract archive <span class="title">"~!:FILENAME~!"</span> to:</div>\
                            <div class="actions">\
                                <input type="text" id="unpack-destination" class="new-title" value="~!:DST_DIRNAME~!">\
                            </div>\
                            <div class="warning warning-message"></div>\
                            <!-- div class="actions">\
                                <label><input type="checkbox" name="overwrite" class="title" />Overwrite exising files</label>\
                            </div -->\
                            <div class="controls">\
                                <p class="cancel" onClick="FM.popupClose();">cancel</p>\
                                <p class="ok" onClick="FM.confirmUnpackItem();">Extract</p>\
                            </div>\
                        </div>'],


        popup_create_file: ['<div class="confirm-box rename warning">\
                            <div class="message">Create file</div>\
                            <!-- div class="warning">File <span class="title">"reading.txt"</span> already exists</div -->\
                            <div class="warning warning-message"></div>\
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
                            <div class="warning warning-message"></div>\
                            <div class="actions">\
                                <input type="text" id="rename-title" class="new-title" />\
                            </div>\
                            <div class="controls replace">\
                                <p class="cancel" onClick="FM.popupClose();">cancel</p>\
                                <p class="ok" onClick="FM.confirmCreateDir();">create</p>\
                            </div>\
                        </div>'],
        popup_no_file_selected: ['<div class="confirm-box no-file-selected">\
                            <div class="message">Please select a file</div>\
                            <div class="controls">\
                                <p class="ok" onClick="FM.confirmCreateDir();">ok</p>\
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
