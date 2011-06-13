App.Templates.html = {
    help: {
        DNS_form: ['<h1>Some Things You Just Can\'t Explain</h1>\
A farmer was sitting in the neighborhood bar getting drunk. A man came in and asked the farmer, "Hey, why are you sitting here on this beautiful day, getting drunk?" The farmer shook his head and replied, "Some things you just can\'t explain."\
"So what happened that\'s so horrible?" the man asked as he sat down next to the farmer.\
"Well," the farmer said, "today I was sitting by my cow, milking her. Just as I got the bucket full, she lifted her left leg and kicked over the bucket."\
"Okay," said the man, "but that\'s not so bad." "Some things you just can\'t explain," the farmer replied. "So what happened then?" the man asked. The farmer said, "I took her left leg and tied it to the post on the left."\
"And then?"\
"Well, I sat back down and continued to milk her. Just as I got the bucket full, she took her right leg and kicked over the bucket."\
The man laughed and said, "Again?" The farmer replied, "Some things you just can\'t explain." "So, what did you do then?" the man asked.\
"I took her right leg this time and tied it to the post on the right."\
"And then?"\
"Well, I sat back down and began milking her again. Just as I got the bucket full, the stupid cow knocked over the bucket with her tail."\
"Hmmm," the man said and nodded his head. "Some things you just can\'t explain," the farmer said.\
"So, what did you do?" the man asked.\
"Well," the farmer said, "I didn\'t have anymore rope, so I took off my belt and tied her tail to the rafter. In that moment, my pants fell down and my wife walked in ... Some things you just can\'t explain."']
    },
    general: {
        loading: ['<div id="loading" style="font-size:19px;font-weight: bol;position:absolute;width: 150px; background-color:yellow;z-index: 9999; padding: 8px;left: 50%;margin-left:-75px;">\
                <center>Loading...</center>\
                </div>'],
        popup: ['<div class="black_overlay" id="popup-bg"></div>\
                <div class="popup_content" id="popup"><button class="do_action_close_popup">close</button>~!:content~!</div>'],
    },
    popup: {
        error: ['<div class="error"><center><h1 style="color: red;">Important: An Error Has Occured.</h1><hr></center>&nbsp;&nbsp;&nbsp;&nbsp;Something went wrong and some of your actions can be not saved in system. Mostly, it happens when you have network connection errors.<br>,&nbsp;&nbsp;&nbsp;&nbsp;However, please notify us about the situation. It would be helpfull if you will write us approximate time the error occured and last actions you were performing. You send your petition on <a href="mail_to">this email: BLABLA</a>,<br><br><center><span style="color: rgb(92, 92, 92);">Sorry for inconvinience. (We recommend you to reload the page)</span></center></div>']
    },
    dates: {
        'lock_plan_date' : ['<button class="do.savePlanDate(~!:task_id~!)">Lock plan dates</button><button class="do.lockPlanDate(~!:task_id~!)">Lock plan dates</button>'],
        'save_forecasted_date' : ['<button class="do.saveForecastedDate(~!:task_id~!)">save forecasted dates</button>']
    },
    dns: {
        FORM: [
            '<div style="margin-top: 25px;" class="b-new-entry b-new-entry_dns" id="~!:id~!">\
                <input type="hidden" name="source" class="source" value=\'~!:source~!\'>\
                    <input type="hidden" name="target" class="target" value=\'\'>\
                    <div class="entry-header">~!:title~!</div>\
                    <div class="form-row cc">\
                            <label for="#" class="field-label">domain:</label>\
                            <input type="text" name="DOMAIN" value="~!:DOMAIN~!" class="text-field DOMAIN">\
                    </div>\
                    <div class="form-row cc">\
                            <label for="#" class="field-label">ip address:</label>\
                            <div class="autocomplete-box">\
                                    <input type="text" name="IP" value="~!:IP~!" class="text-field IP">\
                                    <!-- i class="arrow">&nbsp;</i -->\
                            </div>\
                    </div>\
                    <div class="form-row dns-template-box cc">\
                            <label for="#" class="field-label">template:</label>\
                            <select name="template" class="styled">\
                                    ~!:TPL~!\
                            </select>\
                            <span class="context-settings do_action_embed_subform">View template settings</span>\
                    </div>\
                    <div class="form-row buttons-row cc">\
                            <input type="submit" value="~!:save_button~!" class="add-entry-btn do_action_save_dns_form">\
                            <span class="cancel-btn do_action_cancel_form">Cancel</span>\
                            <span class="help-btn do_action_form_help">Help</span>\
                    </div>\
            </div>'
        ],
        SUSPENDED_TPL_ENABLED : ['<span class="ip-status-info ip-enabled-status"><span class="ip-status-text">enabled</span></span>\
                                <span class="delete-entry"><span class="delete-entry-text do_action_delete_ip">delete</span></span>'],
        SUSPENDED_TPL_DISABLED : ['<span class="ip-status-info ip-suspended-status do_action_delete_dns"><span class="ip-status-text">suspended</span></span>'],
        ENTRIES_WRAPPER: ['<div class="dns-list">~!:content~!</div>'],
        ENTRY: ['<div class="row dns-details-row ~!:CHECKED~!">\
                            <input type="hidden" name="source" class="source" value=\'~!:source~!\'>\
                            <input type="hidden" class="target" name="target" value="" />\
                            <div class="row-actions-box cc">\
                                        <div class="check-this check-control"></div>\
                                        <div class="row-operations">\
                                                ~!:SUSPENDED_TPL~!\
                                                <span class="delete-entry"><span class="delete-entry-text">delete</span></span>\
                                        </div>\
                                </div>\
                                <div class="row-meta">\
                                        <div class="entry-created">~!:DATE~!</div>\
                                </div>\
                                <div class="row-details cc">\
                                        <div class="props-main">\
                                                <div class="names">\
                                                        <strong class="domain-name primary do_action_edit">~!:DNS_DOMAIN~!</strong>\
                                                </div>\
                                                <div class="show-records">Show records</div>\
                                        </div>\
                                        <div class="props-additional">\
                                                <div class="ip-adr-box">\
                                                        <span class="ip-adr">~!:IP~!</span>\
                                                        <span class="prop-box template-box">\
                                                                <span class="prop-title">template:</span>\
                                                                <span class="prop-value">~!:TPL~!</span>\
                                                        </span>\
                                                </div>\
                                        </div>\
                                        <div class="props-ext">\
                                                <span class="prop-box ttl-box">\
                                                        <span class="prop-title">ttl:</span>\
                                                        <span class="prop-value">~!:TTL~!</span>\
                                                </span>\
                                                <span class="prop-box soa-box">\
                                                        <span class="prop-title">soa:</span>\
                                                        <span class="prop-value">~!:SOA~!</span>\
                                                </span>\
                                        </div>\
                                </div><!-- // .row-details -->\
                        </div>']
    },
    ip: {
        FORM: ['\
            <div class="b-new-entry b-new-entry_ip" id="~!:id~!">\
                <input type="hidden" name="source" class="source" value=\'~!:source~!\'>\
                <div class="entry-header">~!:title~!</div>\
                <div class="form-row cc">\
                        <label for="#" class="field-label">ip address:</label>\
                        <input type="text" value="~!:IP_ADDRESS~!" name="IP_ADDRESS" class="text-field">\
                </div>\
                <div class="form-row cc">\
                        <label for="#" class="field-label">owner:</label>\
                        <!-- span class="select" id="selectownership">vesta</span -->\
                        <select name="OWNER" class="styled owner">\
                                ~!:owner_options~!\
                        </select>\
                </div>\
                <div class="form-row cc">\
                        <label for="#" class="field-label">status:</label>\
                        <!-- span class="select" id="select">shared</span -->\
                        <select class="styled status" name="STATUS">\
                                ~!:status_options~!\
                        </select>\
                </div>\
                <div class="form-row cc">\
                        <label for="#" class="field-label">name:</label>\
                        <input type="text" name="NAME" value="" class="text-field">\
                </div>\
                <div class="form-row cc">\
                        <label for="#" class="field-label">interface:</label>\
                        <!-- span class="select" id="select">eth1</span -->\
                        <select class="styled interface" name="INTERFACE">\
                                ~!:interface_options~!\
                        </select>\
                </div>\
                <div class="form-row cc">\
                        <label for="#" class="field-label">netmask:</label>\
                        <div class="autocomplete-box">\
                                <input type="text" value="~!:NETMASK~!" name="NETMASK" class="text-field">\
                        </div>\
                </div>\
                <div class="form-row buttons-row cc">\
                        <input type="submit" value="~!:save_button~!" class="add-entry-btn do_action_save_ip_form">\
                        <span class="cancel-btn do_action_cancel_ip_form">Cancel</span>\
                        <span class="help-btn">Help</span>\
                </div>\
        </div>\
         '],
        DOT: ['<span class="dot">.</span>'],
        ENTRY: ['\
            <div class="row first-row ip-details-row">\
                <input type="hidden" class="source" name="source" value=\'~!:source~!\' />\
                <input type="hidden" class="target" name="target" value="" />\
                <div class="row-actions-box cc">\
                        <div class="check-this"></div>\
                        <div class="row-operations">\
                        ~!:SUSPENDED_TPL~!\
                        </div>\
                </div>\
                <div class="row-meta">\
                        <div class="ip-created">~!:DATE~!</div>\
                </div>\
                <div class="row-details cc">\
                        <div class="ip-props-main">\
                                <div class="ip-adr-box">\
                                        <span class="ip-adr">~!:IP_ADDRESS~!</span>\
                                </div>\
                                <span class="prop-box">\
                                        <span class="prop-title">netmask:</span>\
                                        <span class="prop-value">~!:NETMASK~!</span>\
                                </span>\
                                <span class="prop-box">\
                                        <span class="prop-title">interface:</span>\
                                        <span class="prop-value">~!:INTERFACE~!</span>\
                                </span>\
                                <span class="prop-box">\
                                        <span class="prop-title">name:</span>\
                                        <span class="prop-value">~!:NAME~!</span>\
                                </span>\
                        </div>\
                        <div class="ip-props-additional">\
                                <span class="prop-box">\
                                        <span class="prop-title">owner:</span>\
                                        <span class="prop-value">~!:OWNER~!</span>\
                                </span>\
                                <span class="prop-box">\
                                        <span class="prop-title">status:</span>\
                                        <span class="prop-value">~!:STATUS~!</span>\
                                </span>\
                        </div>\
                        <div class="ip-props-ext">\
                                <span class="prop-box">\
                                        <span class="prop-title">sys users:</span>\
                                        <span class="prop-value">~!:U_SYS_USERS~!</span>\
                                </span>\
                                <span class="prop-box">\
                                        <span class="prop-title">web domains:</span>\
                                        <span class="prop-value">~!:U_WEB_DOMAINS~!</span>\
                                </span>\
                        </div>\
                </div><!-- // .row-details -->\
        </div>\
        '],
        ENTRIES_WRAPPER: ['<div class="ip-list">~!:content~!</div>'],
        SUSPENDED_TPL_ENABLED : ['<span class="ip-status-info ip-enabled-status"><span class="ip-status-text">enabled</span></span>\
                                <span class="delete-entry"><span class="delete-entry-text do_action_delete_ip">delete</span></span>'],
        SUSPENDED_TPL_DISABLED : ['<span class="ip-status-info ip-suspended-status do_action_delete_ip"><span class="ip-status-text">suspended</span></span>']
    }
}



// Internals
var Tpl = App.Templates;

var Templator = function(){
    var init = function(){
        fb.info('Templator work');
        Templator.splitThemAll();
        Templator.freezeTplIndexes();        
    };


    /**
     * Split the tpl strings into arrays
     */
    Templator.splitThemAll = function(){
        fb.info('splitting tpls');
        $.each(App.Templates.html, function(o){
            var tpls = App.Templates.html[o];
            $.each(tpls, function(t){
                tpls[t] = tpls[t][0].split('~!');
            });
        });
    },

    /**
     * Iterates tpls
     */
    Templator.freezeTplIndexes = function(){
        fb.info('freezing tpl keys');
        $.each(App.Templates.html, Templator.cacheTplIndexes);
    },

    /**
     * Grab the tpl group key and process it
     */
    Templator.cacheTplIndexes = function(key){
        var tpls = App.Templates.html[key];

        $.each(tpls, function(o){
            var tpl = tpls[o];
            Templator.catchIndex(key, o, tpl);
        });
    },

    /**
     * Set the indexes
     */
    Templator.catchIndex = function(key, ref_key, tpl){
        'undefined' == typeof App.Templates._indexes[key] ? App.Templates._indexes[key] = {} : false;
        'undefined' == typeof App.Templates._indexes[key][ref_key] ? App.Templates._indexes[key][ref_key] = {} : false;

        $(tpl).each(function(index, o){
            if(':' == o.charAt(0)){
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