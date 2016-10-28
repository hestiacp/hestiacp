/**
 *
 * @author: Malishev Dmitry <dima.malishev@gmail.com>
 */
App.Templates.html = {
    WEB: {
        hint: [''],
        notification: [
                    '<li class="~!:UNSEEN~!"><span class="unselectable mark-seen" id="notification-~!:ID~!">&nbsp;</span>\
                        <span class="title"><span class="unselectable icon ~!:TYPE~!">&nbsp;</span>~!:TOPIC~!</span>\
                        ~!:NOTICE~!\
                    </li>'
        ],
        notification_empty: [
                    '<li class="empty">\
                        '+App.Constants.NOTIFICATIONS_EMPTY+'\
                    </li>'
        ]
    },

    // file manager
    //

    FM: {
        reload_in_time: [
                    '<div id="reload-in-time" class="warning-box reload">\
                        <div class="message-small">'+App.Constants.FM_HIT+' <span>F5</span> '+App.Constants.FM_TO_RELOAD_THE_PAGE+'</div>\
                    </div>'
        ],
        entry_line: ['<li class="dir">\
                        <span class="marker">\
                        </span>\
                        <span class="icon ~!:ITEM_TYPE~!" ></span>\
                        <input type="hidden" class="source" value=\'~!:SOURCE~!\'/>\
                        <span class="filename-holder"><div class="filename ripple" ~!:CL_ACTION_1~!>~!:NAME~!</div></span>\
                        <span class="mode">~!:PERMISSIONS~!</span>\
                        <span class="owner">~!:OWNER~!</span>\
                        <span class="size-unit">~!:SIZE_UNIT~!</span>\
                        <span class="size-value">~!:SIZE_VALUE~!</span>\
                        <span class="date">~!:DATE~!</span>\
                        <span class="time">~!:TIME~!</span>\
                        <!-- span class="subcontext-control ~!:SUBMENU_CLASS~!" onClick="FM.toggleSubContextMenu(this)">&#8226;&#8226;&#8226;&nbsp;\
                        <ul class="subcontext-menu subcontext-menu-hidden"><li onClick="FM.downloadFileFromSubcontext(this);">Download</li><li onClick="FM.editFileFromSubcontext(this);">Edit</li></ul>\
                        </span -->\
                    </li>'],
        popup_alert: ['<div class="confirm-box alarm popup-box">\
                            <div class="message">~!:TEXT~!</div>\
                                <div class="controls">\
                            <p class="ok" onClick="FM.popupClose();">'+App.Constants.FM_CLOSE+'</p>\
                            </div>\
                        </div>'],
        popup_bulk_remove: ['<div class="confirm-box delete popup-box">\
                            <div class="message">'+App.Constants.FM_CONFIRM_DELETE_BULK+' (~!:NUMBER_OF_ITEMS~!)?</div>\
                            <div class="results"></div>\
                                <div class="controls">\
                            <p class="cancel" onClick="FM.popupClose();">'+App.Constants.FM_CANCEL+'</p>\
                            <p class="ok" onClick="FM.bulkRemoveDo();">'+App.Constants.FM_DELETE+'</p>\
                            </div>\
                        </div>'],
        popup_bulk_copy: ['<div class="confirm-box copy popup-box">\
                            <div class="message">'+App.Constants.FM_COPY_BULK+' (~!:NUMBER_OF_ITEMS~!) '+ App.Constants.FM_INTO_KEYWORD +':</div>\
                            <div class="actions">\
                                <input type="text" id="copy_dest" value="~!:DST_FILENAME~!" class="new-title">\
                            </div>\
                            <div class="results"></div>\
                            <div class="warning">'+App.Constants.FM_EXISTING_FILES_WILL_BE_REPLACED+'</div>\
                                <div class="controls">\
                            <p class="cancel" onClick="FM.popupClose();">'+App.Constants.FM_CANCEL+'</p>\
                            <p class="ok" onClick="FM.bulkCopyDo();">'+App.Constants.FM_COPY+'</p>\
                            </div>\
                        </div>'],
        popup_bulk_move: ['<div class="confirm-box copy popup-box">\
                            <div class="message">'+App.Constants.FM_MOVE_BULK+' (~!:NUMBER_OF_ITEMS~!) '+ App.Constants.FM_INTO_KEYWORD +':</div>\
                            <div class="actions">\
                                <input type="text" id="dst-name" value="~!:DST_NAME~!" class="new-title">\
                            </div>\
                            <div class="results"></div>\
                            <div class="warning">'+App.Constants.FM_EXISTING_FILES_WILL_BE_REPLACED+'</div>\
                                <div class="controls">\
                            <p class="cancel" onClick="FM.popupClose();">'+App.Constants.FM_CANCEL+'</p>\
                            <p class="ok" onClick="FM.bulkMoveDo();">'+App.Constants.FM_MOVE+'</p>\
                            </div>\
                        </div>'],
        popup_delete: ['<div class="confirm-box delete popup-box">\
                            <div class="message">'+App.Constants.FM_CONFIRM_DELETE+' <span class="title">"~!:FILENAME~!"</span>?</div>\
                            <div class="warning warning-message"></div>\
                                <div class="controls">\
                            <p class="cancel" onClick="FM.popupClose();">'+App.Constants.FM_CANCEL+'</p>\
                            <p class="ok" onClick="FM.confirmDelete();">'+App.Constants.FM_DELETE+'</p>\
                            </div>\
                        </div>'],
        popup_copy: ['<div class="confirm-box copy popup-box">\
                            <div class="message">'+App.Constants.FM_COPY+' <span class="title">"~!:SRC_FILENAME~!"</span> '+App.Constants.FM_INTO_KEYWORD+':</div>\
                            <div class="actions">\
                                <input type="text" id="copy_dest" value="~!:DST_FILENAME~!" class="new-title">\
                            </div>\
                            <div class="message"></div>\
                           <div class="warning warning-message">'+App.Constants.FM_EXISTING_FILES_WILL_BE_REPLACED+'</div>\
                                <div class="controls">\
                            <p class="cancel" onClick="FM.popupClose();">'+App.Constants.FM_CANCEL+'</p>\
                            <p class="ok" onClick="FM.confirmCopyItems();">'+App.Constants.FM_COPY+'</p>\
                            </div>\
                        </div>'],

        popup_move: ['<div class="confirm-box copy popup-box">\
                            <div class="message">'+App.Constants.FM_MOVE+' <span class="title">"~!:FILENAME~!"</span> '+App.Constants.FM_INTO_KEYWORD+':</div>\
                            <div class="actions">\
                                <input type="text" id="dst-name" value="~!:DST_NAME~!" class="new-title">\
                            </div>\
                            <div class="message"></div>\
                           <div class="warning warning-message">'+App.Constants.FM_EXISTING_FILES_WILL_BE_REPLACED+'</div>\
                                <div class="controls">\
                            <p class="cancel" onClick="FM.popupClose();">'+App.Constants.FM_CANCEL+'</p>\
                            <p class="ok" onClick="FM.confirmMove();">'+App.Constants.FM_MOVE+'</p>\
                            </div>\
                        </div>'],

        popup_rename: ['<div class="confirm-box rename warning">\
                            <div class="message">'+App.Constants.FM_RENAME+': <span class="title">"~!:FILENAME~!"</span></div>\
                            <div class="warning warning-message"></div>\
                            <div class="actions">\
                                <input type="text" id="rename-title" class="new-title"  value="~!:NEW_NAME~!" />\
                            </div>\
                            <div class="controls">\
                                <p class="cancel" onClick="FM.popupClose();">'+App.Constants.FM_CANCEL+'</p>\
                                <p class="ok" onClick="FM.confirmRename();">'+App.Constants.FM_RENAME+'</p>\
                            </div>\
                            <div class="controls replace">\
                                <p class="cancel" onClick="FM.popupClose();">'+App.Constants.FM_CANCEL+'</p>\
                                <p class="ok" onClick="FM.confirmRename();">'+App.Constants.FM_RENAME+'</p>\
                            </div>\
                        </div>'],

        popup_chmod: ['<div class="confirm-box chmod warning">\
                            <div class="message">'+App.Constants.FM_CHMOD+': <span class="title">"~!:FILENAME~!"</span></div>\
                            <div class="warning warning-message"></div>\
                            <div class="actions">\
                                <ul>\
                                    <li><label><input type="checkbox" name="read-by-owner" value="1" ~!:READ_BY_OWNER~! /> '+App.Constants.FM_READ_BY_OWNER+'</label></li>\
                                    <li><label><input type="checkbox" name="write-by-owner" value="1" ~!:WRITE_BY_OWNER~! /> '+App.Constants.FM_WRITE_BY_OWNER+'</label></li>\
                                    <li><label><input type="checkbox" name="execute-by-owner" value="1" ~!:EXECUTE_BY_OWNER~! /> '+App.Constants.FM_EXECUTE_BY_OWNER+'</label></li>\
                                </ul><ul>\
                                    <li><label><input type="checkbox" name="read-by-group" value="1" ~!:READ_BY_GROUP~! /> '+App.Constants.FM_READ_BY_GROUP+'</label></li>\
                                    <li><label><input type="checkbox" name="write-by-group" value="1" ~!:WRITE_BY_GROUP~! /> '+App.Constants.FM_WRITE_BY_GROUP+'</label></li>\
                                    <li><label><input type="checkbox" name="execute-by-group" value="1" ~!:EXECUTE_BY_GROUP~! /> '+App.Constants.FM_EXECUTE_BY_GROUP+'</label></li>\
                                </ul><ul>\
                                    <li><label><input type="checkbox" name="read-by-others" value="1" ~!:READ_BY_OTHERS~! /> '+App.Constants.FM_READ_BY_OTHERS+'</label></li>\
                                    <li><label><input type="checkbox" name="write-by-others" value="1" ~!:WRITE_BY_OTHERS~! /> '+App.Constants.FM_WRITE_BY_OTHERS+'</label></li>\
                                    <li><label><input type="checkbox" name="execute-by-others" value="1" ~!:EXECUTE_BY_OTHERS~! /> '+App.Constants.FM_EXECUTE_BY_OTHERS+'</label></li>\
                                </ul>\
                                <input type="text" name="mask" class="chmod-mask" maxlength="3" />\
                            </div>\
                            <div class="controls">\
                                <p class="cancel" onClick="FM.popupClose();">'+App.Constants.FM_CANCEL+'</p>\
                                <p class="ok" onClick="FM.confirmChmod();">'+App.Constants.FM_OK+'</p>\
                            </div>\
                        </div>'],
        popup_bulk_chmod: ['<div class="confirm-box chmod warning">\
                            <div class="message">'+App.Constants.FM_CHMOD+' (~!:NUMBER_OF_ITEMS~!)</div>\
                            <div class="warning warning-message"></div>\
                            <div class="actions">\
                                <ul>\
                                    <li><label><input type="checkbox" name="read-by-owner" value="1" checked /> '+App.Constants.FM_READ_BY_OWNER+'</label></li>\
                                    <li><label><input type="checkbox" name="write-by-owner" value="1" checked /> '+App.Constants.FM_WRITE_BY_OWNER+'</label></li>\
                                    <li><label><input type="checkbox" name="execute-by-owner" value="1" checked /> '+App.Constants.FM_EXECUTE_BY_OWNER+'</label></li>\
                                </ul><ul>\
                                    <li><label><input type="checkbox" name="read-by-group" value="1" checked /> '+App.Constants.FM_READ_BY_GROUP+'</label></li>\
                                    <li><label><input type="checkbox" name="write-by-group" value="1" checked /> '+App.Constants.FM_WRITE_BY_GROUP+'</label></li>\
                                    <li><label><input type="checkbox" name="execute-by-group" value="1" checked /> '+App.Constants.FM_EXECUTE_BY_GROUP+'</label></li>\
                                </ul><ul>\
                                    <li><label><input type="checkbox" name="read-by-others" value="1" checked /> '+App.Constants.FM_READ_BY_OTHERS+'</label></li>\
                                    <li><label><input type="checkbox" name="write-by-others" value="1" checked /> '+App.Constants.FM_WRITE_BY_OTHERS+'</label></li>\
                                    <li><label><input type="checkbox" name="execute-by-others" value="1" checked /> '+App.Constants.FM_EXECUTE_BY_OTHERS+'</label></li>\
                                </ul>\
                                <input type="text" name="mask" class="chmod-mask" maxlength="3" value="777" />\
                            </div>\
                            <div class="controls">\
                                <p class="cancel" onClick="FM.popupClose();">'+App.Constants.FM_CANCEL+'</p>\
                                <p class="ok" onClick="FM.confirmBulkChmod();">'+App.Constants.FM_OK+'</p>\
                            </div>\
                        </div>'],

        popup_pack: ['<div class="confirm-box pack warning">\
                            <div class="message">'+App.Constants.FM_PACK+' <span class="title">"~!:FILENAME~!"</span></div>\
                            <div class="actions">\
                                <input type="text" id="pack-destination" class="new-title" value="~!:DST_DIRNAME~!">\
                            </div>\
                            <div class="warning warning-message"></div>\
                            <!-- div class="actions">\
                                <label><input type="checkbox" name="overwrite" class="title" />Overwrite exising files</label>\
                            </div -->\
                            <div class="controls">\
                                <p class="cancel" onClick="FM.popupClose();">'+App.Constants.FM_CANCEL+'</p>\
                                <p class="ok" onClick="FM.confirmPackItem();">'+App.Constants.FM_PACK_BUTTON+'</p>\
                            </div>\
                        </div>'],

        popup_bulk_pack: ['<div class="confirm-box pack warning">\
                            <div class="message">'+App.Constants.FM_PACK+' (~!:NUMBER_OF_ITEMS~!)</div>\
                            <div class="actions">\
                                <input type="text" id="pack-destination" class="new-title" value="~!:DST_DIRNAME~!">\
                            </div>\
                            <div class="warning warning-message"></div>\
                            <!-- div class="actions">\
                                <label><input type="checkbox" name="overwrite" class="title" />Overwrite exising files</label>\
                            </div -->\
                            <div class="controls">\
                                <p class="cancel" onClick="FM.popupClose();">'+App.Constants.FM_CANCEL+'</p>\
                                <p class="ok" onClick="FM.confirmPackItem();">'+App.Constants.FM_PACK_BUTTON+'</p>\
                            </div>\
                        </div>'],

        popup_unpack: ['<div class="confirm-box unpack warning">\
                            <div class="message">'+App.Constants.FM_EXTRACT+' <span class="title">"~!:FILENAME~!"</span> '+App.Constants.FM_INTO_KEYWORD+':</div>\
                            <div class="actions">\
                                <input type="text" id="unpack-destination" class="new-title" value="~!:DST_DIRNAME~!">\
                            </div>\
                            <div class="warning warning-message"></div>\
                            <!-- div class="actions">\
                                <label><input type="checkbox" name="overwrite" class="title" />Overwrite exising files</label>\
                            </div -->\
                            <div class="controls">\
                                <p class="cancel" onClick="FM.popupClose();">'+App.Constants.FM_CANCEL+'</p>\
                                <p class="ok" onClick="FM.confirmUnpackItem();">'+App.Constants.FM_EXTRACT+'</p>\
                            </div>\
                        </div>'],


        popup_create_file: ['<div class="confirm-box rename warning">\
                            <div class="message">'+App.Constants.FM_CREATE_FILE+'</div>\
                            <!-- div class="warning">File <span class="title">"reading.txt"</span> already exists</div -->\
                            <div class="warning warning-message"></div>\
                            <div class="actions">\
                                <input type="text" id="rename-title" class="new-title" />\
                            </div>\
                            <div class="controls replace">\
                                <p class="cancel" onClick="FM.popupClose();">'+App.Constants.FM_CANCEL+'</p>\
                                <p class="ok" onClick="FM.confirmCreateFile();">'+App.Constants.FM_CREATE+'</p>\
                            </div>\
                        </div>'],
        popup_create_dir: ['<div class="confirm-box rename warning">\
                            <div class="message">'+App.Constants.FM_CREATE_DIRECTORY+'</div>\
                            <!-- div class="warning">File <span class="title">"reading.txt"</span> already exists</div -->\
                            <div class="warning warning-message"></div>\
                            <div class="actions">\
                                <input type="text" id="rename-title" class="new-title" />\
                            </div>\
                            <div class="controls replace">\
                                <p class="cancel" onClick="FM.popupClose();">'+App.Constants.FM_CANCEL+'</p>\
                                <p class="ok" onClick="FM.confirmCreateDir();">'+App.Constants.FM_CREATE+'</p>\
                            </div>\
                        </div>'],
        popup_no_file_selected: ['<div class="confirm-box no-file-selected">\
                            <div class="message">Please select a file</div>\
                            <div class="controls">\
                                <p class="ok" onClick="FM.confirmCreateDir();">'+App.Constants.FM_OK+'</p>\
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
