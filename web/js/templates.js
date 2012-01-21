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
        logged_as: ['<div id="logged-in-as">You (<i>~!:YOU_ARE~!</i>) are viewing <strong style="font-weight: bold; color: #2A8FBD;">~!:USER~!</strong> interface. Exit it to return to your own.</div>'],
        over_bar: ['<span style="width:~!:OVER_PERCENTS~!%;right:-~!:OVER_PERCENTS_2~!%;" class="bar_overdraft"></span>'],
        loading: ['<div id="loading" style="border-radius: 0 0 6px 6px;top: 0;font-size:19px;font-weight: bol;position:fixed;width: 150px; background-color:#6E6E62;z-index: 9999; padding: 8px;left: 50%;margin-left:-75px;">\
                <center><div style="width: 105px; height:30px;background-image: url(\'../images/loading.png\');"></center>\
                </div>'],
        popup: ['<div class="black_overlay" id="popup-bg"></div>\
                <div class="popup_content" id="popup"><button class="do_action_close_popup">close</button>~!:content~!</div>'],
        inner_popup: ['<div id="inner-popup" style="left:~!:LEFT~!px;top:~!:TOP~!px;z-index:1000;display:block;" class="d-popup d-popup-ns-list">\
            <div class="d-popup-inner">\
                <span class="close do_action_close_inner_popup">×</span>\
                <div class="d-popup-title">~!:POPUP_TITLE~!</div>\
                <div class="d-popup-content">\
                    ~!:CONTENT~!\
                </div>\
            </div>\
        </div>'],
        select_option: ['<option ~!:SELECTED~! value="~!:VALUE~!">~!:TEXT~!</option>'],
        error_elm: ['<div class="error-box">~!:ERROR~!</div>'],
        SUSPENDED_TPL_NOT_SUSPENDED : ['<span class="ip-status-info ip-enabled-status"><span class="ip-status-text">enabled</span></span>'],
        SUSPENDED_TPL_SUSPENDED : ['<span class="ip-status-info ip-suspended-status"><span class="ip-status-text">suspended</span></span>'],
        DELETE_ACTION: ['<span class="delete-btn do_action_delete_entry">Delete</span>']
    },
    popup: {
        error: ['<div class="error"><center><h1 style="color: red;">Important: An Error Has Occured.</h1><hr></center>&nbsp;&nbsp;&nbsp;&nbsp;Something went wrong and some of your actions can be not saved in system. Mostly, it happens when you have network connection errors.<br>,&nbsp;&nbsp;&nbsp;&nbsp;However, please notify us about the situation. It would be helpfull if you will write us approximate time the error occured and last actions you were performing. You send your petition on <a href="mail_to">this email: BLABLA</a>,<br><br><center><span style="color: rgb(92, 92, 92);">Sorry for inconvinience. (We recommend you to reload the page)</span></center></div>'],
        change_psw: [
            '<div id="change-psw-block" class="page2">\
                <div class="b-auth-form">\
                    <div class="b-auth-form-wrap">\
                        <img width="72" height="24" alt="" src="~!:LOGO_URL~!" class="vesta-logo">\
                        <span style="color: #5E696B; float: right; margin-top: -48px;">~!:VERSION~!</span>\
                        <div class="b-client-title">\
                            <span class="client-title-wrap">~!:PRODUCT_NAME~!<i class="planets">&nbsp;</i></span>\
                        </div>\
                        <form id="change_psw-form" method="post" action="#" class="auth">\
                            <div class="form-row cc">\
                                <label for="change-email" class="field-label">Email</label>\
                                <input type="text" tabindex="1" id="change-email" class="field-text">\
                            </div>\
                            <div class="form-row cc">\
                                <label for="captcha" class="field-label">Captcha</label>\
                                <label class="captcha"><img id="captcha-img" width="127px;" src="~!:CAPTCHA_URL~!"  style="cursor: pointer; float: left; margin-top: -7px; padding-left: 20px;" onClick="this.src = \'~!:CAPTCHA_URL_2~!?\'+Math.floor(Math.random() * 9999)"/></label>\
                                <input type="text" id="captcha" class="field-text" style="margin-left: 11px; width: 132px; margin-bottom: 27px;">\
                            </div>\
                            <div id="change-psw-success" class="success-box hidden"></div>\
                            <div id="change-psw-error" class="error-box hidden"></div>\
                            <div class="form-row cc" style="width: 438px">\
                                <div class="b-remember">\
                                    <span class="remember-me">&nbsp;</span>\
                                </div>\
                                <input type="submit" tabindex="4" value="Send confirmation" class="sumbit-btn do_action_do_change_password">\
                            </div>\
                        </form>\
                        <p class="forgot-pwd"><a href="#" class="forgot-pwd-url do_action_back_to_login">Back to login?</a></p>\
                        <div class="footnotes cc">\
                            <p class="additional-info">For questions please contact <a href="mailto:~!:EMAIL_REAL~!" class="questions-url">~!:EMAIL~!</a></p>\
                            <address class="imprint">&copy; ~!:YEAR~! Vesta Control Panel</address>\
                        </div>\
                    </div>\
                 </div>\
            </div>'
        ],
        login: ['<div id="auth-block" class="page2">\
        <div class="b-auth-form">\
            <div class="b-auth-form-wrap">\
                <img width="72" height="24" alt="" src="~!:LOGO_URL~!" class="vesta-logo">\
                <span style="color: #5E696B; float: right; margin-top: -48px;">~!:VERSION~!</span>\
                <div class="b-client-title">\
                    <span class="client-title-wrap">~!:PRODUCT_NAME~!<i class="planets">&nbsp;</i></span>\
                </div>\
                <form id="login-form" method="post" action="#" class="auth">\
                    <div class="form-row cc">\
                        <label for="email" class="field-label">Login</label>\
                        <input type="text" tabindex="1" id="authorize-login" autocomplete="on" class="field-text" name="login">\
                    </div>\
                    <div class="form-row cc">\
                        <label for="password" class="field-label">Password</label>\
                        <input type="password" tabindex="2" id="authorize-password" autocomplete="on" class="field-text" name="password">\
                    </div>\
                    <div id="auth-error" class="error-box hidden"></div>\
                    <div class="form-row last-row cc">\
                        <div class="b-remember">\
                            <input type="checkbox" tabindex="3" value="1" name="remember_me" id="remember-me" class="remember-me ui-helper-hidden-accessible">\
                            <label for="remember-me" class="remember-label ui-checkbox">remember me</label>\
                        </div>\
                        <input type="submit" tabindex="4" value="enter" class="sumbit-btn do_action_do_authorize">\
                    </div>\
                </form>\
                <p class="forgot-pwd"><a href="#" class="forgot-pwd-url do_action_change_password">forgot password?</a></p>\
                <div class="footnotes cc">\
                    <p class="additional-info">For questions please contact <a href="mailto:~!:EMAIL_REAL~!" class="questions-url">~!:EMAIL~!</a></p>\
                    <address class="imprint">&copy; ~!:YEAR~! Vesta Control Panel</address>\
                </div>\
            </div>\
        </div>\
    </div>']
    },
    dates: {
        'lock_plan_date' : ['<button class="do.savePlanDate(~!:task_id~!)">Lock plan dates</button><button class="do.lockPlanDate(~!:task_id~!)">Lock plan dates</button>'],
        'save_forecasted_date' : ['<button class="do.saveForecastedDate(~!:task_id~!)">save forecasted dates</button>']
    },
    dns: {
        FORM: [
            '<div style="margin-top: 25px;" class="b-new-entry b-new-entry_dns form ~!:FORM_SUSPENDED~!" id="~!:id~!">\
                <input type="hidden" name="source" class="source" value=~!:source~!>\
                    <input type="hidden" name="target" class="target" value=\'\'>\
                    <div class="entry-header">~!:title~!</div>\
                    <div class="form-error hidden">\
                    </div>\
                    <div class="form-row cc">\
                            <input type="hidden" value="~!:DATE~!" name="DATE">\
                            <label for="#" class="field-label">Domain:</label>\
                            <input type="text" name="DNS_DOMAIN" value="~!:DNS_DOMAIN~!" class="text-field rule-required rule-domain">\
                    </div>\
                    <div class="form-row cc">\
                            <label for="#" class="field-label">IP:</label>\
                            <div class="autocomplete-box">\
                                    <input type="text" name="IP" value="~!:IP~!" class="text-field rule-required rule-ip">\
                                    <i class="arrow">&nbsp;</i>\
                            </div>\
                    </div>\
                    <div class="form-row dns-template-box cc">\
                            <label for="#" class="field-label">Template:</label>\
                            <span class="select" id="selecttemplate">~!:TPL_DEFAULT_VALUE~! t </span>\
                                <select name="TPL" class="styled tpl-item">\
                                       ~!:TPL~!\
                                </select>\
                            <span class="context-settings do_action_view_dns_template_settings">View template settings</span>\
                    </div>\
                    <!-- div class="form-row cc">\
                            <label for="#" class="field-label">TTL:</label>\
                            <input type="text" value="~!:TTL~!" name="TTL" class="text-field ttl-field rule-required rule-numeric">\
                    </div>\
                    <div class="form-row cc">\
                            <label for="#" class="field-label">SOA:</label>\
                            <input type="text" value="~!:SOA~!" name="SOA" class="text-field rule-required rule-ns">\
                    </div -->\
                    <div class="form-row suspended cc">\
							<label for="#" class="field-label">Suspended:</label>\
							<input type="checkbox" ~!:SUSPENDED_CHECKED~! value="~!:SUSPENDED_VALUE~!" class="styled do_action_toggle_suspend" name="SUSPEND" />\
					</div>\
                    <div class="form-row buttons-row cc">\
                       <input class="add-entry-btn do_action_save_form" type="submit" value="~!:save_button~!"/>\
                       <span class="cancel-btn do_action_cancel_form">Cancel</span>\
                       ~!:DELETE_ACTION~!\
                    </div>\
            </div>'
        ],        
        ENTRIES_WRAPPER: ['<div class="dns-list items-list">~!:content~!</div>'],
        ENTRY: ['<div class="row dns-details-row ~!:CHECKED~! ~!:SUSPENDED_CLASS~!">\
                            <input type="hidden" name="source" class="source" value=~!:source~! />\
                            <input type="hidden" class="target" name="target" value=\'\' />\
                            <div class="row-actions-box cc">\
                                        <div class="check-this check-control"></div>\
                                        <div class="row-operations">\
                                                ~!:SUSPENDED_TPL~!\
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
                                                <div class="show-records do_action_show_subform">Show records</div>\
                                        </div>\
                                        <div class="props-additional">\
                                                <div class="ip-adr-box">\
                                                        <span class="ip-adr">~!:IP~!</span>\
                                                        <span class="prop-box template-box">\
                                                                <span class="prop-title">template:</span>\
                                                                <input type="hidden" class="tpl-item" value="~!:TPL_VAL~!"/>\
                                                                <span class="prop-value do_action_view_dns_template_settings">~!:TPL~!</span>\
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
                        </div>'],
        SUBFORM: ['<div class="b-new-entry b-records-list subform" style="margin-top: -20px;">\
                    <div class="entry-header">\
                            <div class="hide-records do_action_close_subform">Hide records</div>\
                    </div>\
                    <div class="errors">\
                    </div>\
                    <div class="form-row add-box cc">\
                            <a href="javascript:void(0)" class="add-btn do_action_add_subrecord_dns"><i class="add-btn-icon">&nbsp;</i>add dns record</a>\
                    </div>\
                    ~!:SUBRECORDS~!\
                    <div class="form-row buttons-row cc">\
                            <input type="submit" value="save" class="add-entry-btn do_action_save_dns_subrecords">\
                            <span class="cancel-btn do_action_close_subform">Cancel</span>\
                            <!-- span class="help-btn">Help</span -->\
                    </div>\
            </div>'],
        SUBENTRY: ['<div class="subrow form-row form-row-line cc">\
                            <input type="hidden" name="RECORD_ID" value="~!:RECORD_ID~!">\
                            <div class="field-box dns-record-box">\
                                    <label for="#" class="field-label">record:</label>\
                                    <div class="field-box-inner cc">\
                                            <input type="text" value="~!:RECORD~!" name="RECORD" class="text-field">\
                                            <div class="field-devider">in</div>\
                                    </div>\
                            </div>\
                            <div class="field-box dns-type-box">\
                                    <label for="#" class="field-label">type:</label>\
                                    <!-- span class="select RECORD_TYPE" id="selectRECORD_TYPE">~!:RECORD_TYPE_VALUE~!</span -->\
                                    <select name="RECORD_TYPE" class="styled">\
                                        ~!:RECORD_TYPE~!\
                                    </select>\
                            </div>\
                            <div class="field-box dns-value-box">\
                                    <label for="#" class="field-label">value:</label>\
                                    <input type="text" value="~!:RECORD_VALUE~!" name="RECORD_VALUE" class="text-field">\
                            </div>\
                            <!-- div class="delete-record do_action_delete_subentry"></div -->\
                    </div>']
    },
    ip: {
        FORM: ['\
            <div class="b-new-entry b-new-entry_ip form ~!:FORM_SUSPENDED~!" id="~!:id~!">\
                <input type="hidden" name="source" class="source" value=\'~!:source~!\'>\
                <input type="hidden" name="target" class="target" value=\'~!:target~!\'>\
                <div class="entry-header">~!:title~!</div>\
                <div class="form-error hidden">\
						</div>\
                <div class="form-row cc">\
                        <label for="#" class="field-label">IP Address:</label>\
                        <input type="text" value="~!:IP_ADDRESS~!" name="IP_ADDRESS" class="text-field rule-required rule-ip">\
                </div>\
                <div class="form-row cc">\
                        <label for="#" class="field-label">Netmask:</label>\
                        <div class="_autocomplete-box">\
                                <input type="text" value="~!:NETMASK~!" name="NETMASK" class="text-field">\
                        </div>\
                </div>\
                <div class="form-row cc">\
                        <label for="#" class="field-label">Interface:</label>\
                        <!-- span class="select" id="select">eth1</span -->\
                        <select class="styled interface" name="INTERFACE">\
                                ~!:interface_options~!\
                        </select>\
                </div>\
                <div class="form-row cc">\
                        <label for="#" class="field-label">Owner:</label>\
                        <select name="OWNER" class="styled OWNER">\
                                ~!:owner_options~!\
                        </select>\
                </div>\
                <div class="form-row cc">\
                        <label for="#" class="field-label">Status:</label>\
                        <!-- span class="select" id="select">shared</span -->\
                        <select class="styled status" name="STATUS">\
                                ~!:status_options~!\
                        </select>\
                </div>\
                <div class="form-row cc">\
                        <label for="#" class="field-label">Associated DNS Name:</label>\
                        <input type="text" name="NAME" value="~!:NAME~!" class="text-field rule-domain">\
                </div>\
                <!-- div class="form-row suspended cc">\
							<label for="#" class="field-label">Suspended:</label>\
							<input type="checkbox" ~!:SUSPENDED_CHECKED~! value="~!:SUSPENDED_VALUE~!" class="styled do_action_toggle_suspend" name="SUSPEND"/>\
					</div -->\
				<div class="form-row buttons-row cc">\
                   <input class="add-entry-btn do_action_save_form" type="submit" value="~!:save_button~!"/>\
                   <span class="cancel-btn do_action_cancel_form">Cancel</span>\
                   ~!:DELETE_ACTION~!\
                </div>\
        </div>\
         '],
        DOT: ['<span class="dot">.</span>'],
        ENTRY: ['\
            <div class="row ip-details-row ~!:SUSPENDED_CLASS~!">\
                <input type="hidden" class="source" name="source" value=\'~!:source~!\' />\
                <input type="hidden" class="target" name="target" value="" />\
                <div class="row-actions-box cc">\
                        <div class="check-this check-control"></div>\
                        <div class="row-operations">\
                            ~!:SUSPENDED_TPL~!\
                        </div>\
                </div>\
                <div class="row-meta">\
                        <div class="entry-created">~!:DATE~!</div>\
                </div>\
                <div class="row-details cc">\
                        <div class="ip-props-main">\
                                <div class="ip-adr-box">\
                                        <span class="ip-adr  do_action_edit">~!:IP_ADDRESS~!</span>\
                                </div>\
                                <span class="prop-box">\
                                        <span class="prop-title">netmask:</span>\
                                        <span class="prop-value">~!:NETMASK~!</span>\
                                </span>\
                                <span class="prop-box">\
                                        <span class="prop-title">name:</span>\
                                        <span class="prop-value">~!:NAME~!</span>\
                                </span>\
                                <span class="prop-box">\
                                        <span class="prop-title">interface:</span>\
                                        <span class="prop-value">~!:INTERFACE~!</span>\
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
                                        <span class="prop-title">users:</span>\
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
        ENTRIES_WRAPPER: ['<div class="ip-list items-list">~!:content~!</div>'],
        SUSPENDED_TPL_ENABLED : ['<span class="ip-status-info ip-enabled-status"><span class="ip-status-text">enabled</span></span>\
                                <span class="delete-entry"><span class="delete-entry-text do_action_delete_ip">delete</span></span>'],
        SUSPENDED_TPL_DISABLED : ['<span class="ip-status-info ip-suspended-status do_action_delete_ip"><span class="ip-status-text">suspended</span></span>']
    },
    
    
    user: {
        WEB_TPL_MINIMIZED: ['~!:WEB_TPL_MINI~!\
                        <span class="group-switcher">\
                            <span class="group-values-count do_action_view_full_web_templates">~!:MORE_NUMBER~! more</span>\
                        </span><span class="hidden ns-full-list"><span class="group-values">~!:WEB_TPL_FULL~!</span></span>'],
        WEB_TPL: ['<span class="prop-value">~!:NAME~!</span>'],
        NS_MINIMIZED: ['~!:NS_MINI~!\
                        <span class="group-switcher">\
                            <span class="group-values-count do_action_view_full_ns_list">~!:MORE_NUMBER~! more</span>\
                        </span><span class="hidden ns-full-list"><span class="group-values">~!:NS_FULL~!</span></span>'],        
        NS_RECORD: ['<span class="prop-value">~!:NAME~!</span>'],
        NS_INPUT: ['<div class="form-row ns-entry cc">\
							<label for="#" class="field-label">~!:NS_LABEL~!:</label>\
							<input type="text" value="~!:NAME~!" name="" class="text-field ns-input rule-required rule-ns">\
							<b class="do_action_delete_ns delete-record"></b>\
						</div>'],
        PLUS_ONE_NS: ['<div class="form-row cc do_action_add_form_ns additional-ns-add">\
							<a href="javascript:void(0);" class="add-ns do_action_add_form_ns">\
								<i class="icon do_action_add_form_ns">&nbsp;</i>\
								<span class="btn-title do_action_add_form_ns">Add additional Name Server</span>\
							</a>\
						</div>'],
        ENTRIES_WRAPPER: ['<div class="users-list items-list">~!:content~!</div>'], 
        FORM:  ['<div id="~!:id~!" class="b-new-entry b-new-entry_user form ~!:FORM_SUSPENDED~!">\
                        <input type="hidden" name="source" class="source" value=\'~!:source~!\'>\
                        <input type="hidden" name="target" class="target" value=\'\'>\
						<div class="entry-header">~!:title~!</div>\
						<div class="form-error hidden">\
						</div>\
						<div class="form-row cc">\
							<label for="#" class="field-label">Username:</label>\
							<input type="text" class="text-field rule-required rule-username" value="~!:LOGIN_NAME~!" name="LOGIN_NAME">\
						</div>\
						<div class="form-row pwd-box cc">\
							<label for="#" class="field-label">Password:</label>\
							<input type="text" class="text-field password rule-required" name="PASSWORD" value="~!:PASSWORD~!">\
							<span class="generate-pwd do_action_generate_pass">Generate</span>\
						</div>\
						<div class="form-row cc">\
							<label for="#" class="field-label">Package:</label>\
							<select name="PACKAGE" class="styled disabled" >\
								~!:PACKAGE_OPTIONS~!\
							</select>\
						</div>\
                        <div class="form-row shell-entry cc">\
							<label for="#" class="field-label">Shell:</label>\
							<select class="styled disabled" name="SHELL">\
								~!:SHELL_OPTIONS~!\
							</select>\
						</div>\
						<!-- div class="form-row cc">\
							<label for="#" class="field-label">role:</label>\
							<select class="styled" name="ROLE">\
								~!:ROLE_OPTIONS~!\
							</select>\
						</div -->\
						<div class="form-row cc">\
							<label for="#" class="field-label">Email:</label>\
							<input type="text" name="CONTACT" class="text-field rule-email rule-required" value="~!:CONTACT~!">\
						</div>\
						<div class="form-row ~!:REPORTS_ENABLED_EDITABLE~! cc">\
							<label for="#" class="field-label">Reports:</label>\
							<input type="checkbox" name="REPORTS_ENABLED" ~!:CHECKED~! class="styled" value="~!:REPORTS_ENABLED~!">\
						</div>\
                    		<div class="form-row cc">\
							<label for="#" class="field-label">First name:</label>\
							<input type="text" name="FNAME" class="text-field rule-abc  rule-required" value="~!:FNAME~!">\
						</div>\
						<div class="form-row cc">\
							<label for="#" class="field-label">Last name:</label>\
							<input type="text" name="LNAME" class="text-field rule-abc rule-required" value="~!:LNAME~!">\
						</div>\
                        <div class="form-row ns-entry cc">\
							<label for="#" class="field-label">Name Server #1:</label>\
							<input type="text" value="~!:NS1~!" name="NS1" class="text-field rule-required rule-ns">\
						</div>\
                        <div class="form-row ns-entry cc">\
							<label for="#" class="field-label">Name Server #2:</label>\
							<input type="text" value="~!:NS2~!" name="NS2" class="text-field rule-required rule-ns">\
						</div>\
                        ~!:NS~!\
                        <div class="form-row suspended cc">\
                            <label for="#" class="field-label">Suspended:</label>\
                            <input type="checkbox" ~!:SUSPENDED_CHECKED~! value="~!:SUSPENDED_VALUE~!" class="styled do_action_toggle_suspend" name="SUSPEND"/>\
                        </div>\
                        <div class="form-row buttons-row cc">\
                           <input class="add-entry-btn do_action_save_form" type="submit" value="~!:save_button~!"/>\
                           <span class="cancel-btn do_action_cancel_form">Cancel</span>\
                           ~!:DELETE_ACTION~!\
                        </div>\
					</div>'],
        ENTRY: ['<div class="row user-details-row ~!:SUSPENDED_CLASS~!">\
                    <input type="hidden" class="source" name="source" value=\'~!:source~!\' />\
                    <input type="hidden" class="target" name="target" value="" />\
						<div class="row-actions-box cc">\
							<div class="check-this check-control"></div>\
							<div class="row-operations">\
								~!:SUSPENDED_TPL~!\
							</div>\
						</div>\
						<div class="row-meta">\
							<div class="entry-created">~!:DATE~!</div>\
						</div>\
						<div class="row-details cc">\
							<div class="props-main">\
								<div class="user-wrap">\
									<div class="username-box">\
										<span class="user">\
											<span class="nickname do_action_edit">~!:LOGIN_NAME~!</span>\
                                            <!-- span class="role">(~!:ROLE~!)</span -->\
										</span>\
                                        <span class="prop-box user-name">\
                                            <span class="prop-value">~!:FULLNAME~!</span>\
                                        </span>\									</div>\
									<div class="user-details-box">\
                                        <!-- span class="prop-box prop-box_group-values cc user-details do_action_login_as">\
                                            <span class="prop-value login-as do_action_login_as">login as</span>\
                                        </span -->\
                                        <span class="prop-box prop-box_group-values cc user-details">\
                                            <span class="prop-title">email:</span>\
                                            <span class="group-values">\
                                                <span class="prop-value user-email">~!:CONTACT~!</span>\
                                                <span class="prop-value user-reports">(reports ~!:REPORTS_ENABLED~!)</span>\
                                                </span>\
                                        </span>\
										<span class="prop-box template-box">\
											<span class="prop-title">package:</span>\
											<span class="prop-value do_action_view_template_info">~!:PACKAGE~!</span>\
										</span>\
									</div>\
								</div>\
                                \
								<!-- stats block -->\
                                \
                                <div class="b-stats-box">\
									<div class="stats-box-title">stats</div>	\
                                    <!-- disk usage block -->\
                                    <div class="b-usage-box2 disk-usage cc">\
                                        <span class="prop-title">disk usage:</span>\
                                        <div class="usage-box ~!:OVER_DRAFT_VALUE~!">\
                                            <div class="value-box">\
                                                <div class="graph">\
                                                    <span style="left:~!:U_DISK_PERCENTAGE_2~!%;" class="value">~!:U_DISK_PERCENTAGE~!% <span class="value-size">(~!:U_DISK~! ~!:DISK_QUOTA_MEASURE~!)</span></span>\
                                                    <span style="width:~!:U_DISK_PERCENTAGE_3~!%;" class="bar"></span>\
                                                    ~!:OVER_BAR~!\
                                                </div>\
                                            </div>\
                                            <div class="max-size">~!:DISK_QUOTA~! <span class="units">~!:DISK_QUOTA_MEASURE_2~!</span></div>\
                                        </div>\
                                    </div><!-- // disk usage block -->\
									<div class="b-usage-box2 bandwidth-box cc">\
										<span class="prop-title">bandwidth:</span>\
										<div class="usage-box">\
											<div class="value-box ~!:OVER_DRAFT_VALUE_2~!">\
												<div class="graph">\
													<span style="left:~!:U_BANDWIDTH_PERCENTAGE_2~!%;" class="value">~!:U_BANDWIDTH_PERCENTAGE~!% <span class="value-size">(~!:U_BANDWIDTH~! ~!:BANDWIDTH_MEASURE~!)</span></span>\
													<span style="width:~!:U_BANDWIDTH_PERCENTAGE_3~!%;" class="bar"></span>\
													~!:OVER_BAR_2~!\
												</div>\
											</div>\
											<div class="max-size">~!:BANDWIDTH~! <span class="units">~!:BANDWIDTH_MEASURE_2~!</span></div>\
										</div>\
									</div>\
								</div><!-- // stats block -->\
                                \
                            </div>\
							<div class="props-additional">\
								<span class="prop-box webdomains-box">\
									<span class="prop-title">web domains:</span>\
									<span class="prop-value">~!:U_WEB_DOMAINS~! (~!:WEB_DOMAINS~!)</span>\
								</span>\
								<span class="prop-box websl-box">\
									<span class="prop-title">web ssl:</span>\
									<span class="prop-value">~!:U_WEB_SSL~! (~!:WEB_SSL~!)</span>\
								</span>\
								<span class="prop-box webalias-box">\
									<span class="prop-title">web alias:</span>\
									<span class="prop-value">~!:WEB_ALIASES~! per domain</span>\
								</span>\
								<span class="prop-box prop-box_group-values cc webtpl-box">\
									<span class="prop-title">web templates:</span>\
									<span class="group-values">\
										~!:WEB_TPL~!\
									</span>\
								</span>\
								<span class="prop-box db-box">\
									<span class="prop-title">databases:</span>\
									<span class="prop-value">~!:U_DATABASES~! (~!:DATABASES~!)</span>\
								</span>\
								<span class="prop-box ip-box">\
									<span class="prop-title">Dedicated IP\'s:</span>\
									<span class="prop-value">~!:IP_OWNED~!</span>\
								</span>\
								<span class="prop-box cron-box">\
									<span class="prop-title">cron jobs:</span>\
									<span class="prop-value">~!:U_CRON_JOBS~!</span>\
								</span>\
							</div>\
							<div class="props-ext">\
								<span class="prop-box maildomains-box">\
									<span class="prop-title">mail domains:</span>\
									<span class="prop-value">~!:U_MAIL_DOMAINS~! (~!:MAIL_DOMAINS~!)</span>\
								</span>\
								<span class="prop-box mailboxes-box">\
									<span class="prop-title">mail accounts:</span>\
									<span class="prop-value">~!:MAIL_BOXES~! per domain</span>\
								</span>\
								<span class="prop-box mailfwds-box">\
									<span class="prop-title">mail forwarders:</span>\
									<span class="prop-value">~!:MAIL_FORWARDERS~! per domain</span>\
								</span>\
                                <span class="prop-box dnsdomains-box">\
									<span class="prop-title">dns domains:</span>\
									<span class="prop-value">~!:U_DNS_DOMAINS~! (~!:DNS_DOMAINS~!)</span>\
								</span>\
								<span class="prop-box prop-box_group-values cc ns-list-box">\
									<span class="prop-title">name servers:</span>\
									<span class="group-values">\
									~!:NS~!</span>\
								</span>\
								<span class="prop-box shell-box">\
									<span class="prop-title">shell:</span>\
									<span class="prop-value">~!:SHELL~!</span>\
								</span>\
								<span class="prop-box backups-box">\
									<span class="prop-title">backups:</span>\
									<span class="prop-value">retention ~!:BACKUPS~!</span>\
								</span>\							</div>\
						</div><!-- // .row-details -->\
					</div>']
    },
    web_domain: {        
        FORM: ['<div id="~!:id~!"  class="b-new-entry b-new-entry_domain form ~!:FORM_SUSPENDED~!">\
                        <input type="hidden" class="source" name="source" value=\'~!:source~!\' />\
                        <input type="hidden" class="target" name="target" value="" />\
						<div class="entry-header">~!:title~!</div>\
						<div class="form-error hidden">\
						</div>\
                        <div class="form-row cc">\
							<label for="#" class="field-label">Domain:</label>\
							<input type="text" name="DOMAIN" class="text-field rule-required rule-ns" value="~!:DOMAIN~!">\
						</div>\
						<div class="form-row cc">\
							<label for="#" class="field-label">IP:</label>\
							<div class="">\
								<select name="IP" class="styled">\
                                ~!:IP_OPTIONS~!\
                                </select>\
							</div>\
						</div>\
						<div class="form-row cc adv_opts">\
								<label for="#" class="field-label">Template:</label>\
								<select class="styled" name="TPL">\
								~!:TPL_OPTIONS~!\
								</select>\
							</div>\
                        <div class="form-row suspended cc">\
                            <label for="#" class="field-label">Suspended:</label>\
                            <input type="checkbox" ~!:SUSPENDED_CHECKED~! value="~!:SUSPENDED_VALUE~!" class="styled do_action_toggle_suspend" name="SUSPEND"/>\
                        </div>\
                        <!-- advanced options -->\
						<div class="form-options-group">\
							<div class="group-header cc collapsed">\
								<span class="group-title-outer do_action_toggle_section">\
									<span class="group-title-inner">\
                                        <span class="group-title do_action_toggle_section">Advanced options</span>\
                                    </span>\
                                </span>\
							</div>\
                            <div class="sub_section hidden">\
							<div class="form-row cc">\
								<label for="#" class="field-label">CGI Support:</label>\
								<input type="checkbox" value="~!:CGI~!" ~!:CHECKED_CGI~! name="CGI" class="styled">\
							</div>\
                            <div class="form-row cc">\
								<label for="#" class="field-label">Error Logging:</label>\
								<input type="checkbox" value="~!:ELOG~!" ~!:CHECKED_ELOG~! name="ELOG" class="styled">\
							</div>\
                            <div class="form-row cc">\
								<label for="#" class="field-label">Domain Aliases:</label>\
								<textarea name="ALIAS" class="textarea rule-alias">~!:ALIAS~!</textarea>\
							</div>\
							<!-- div class="form-row cc">\
								<label for="#" class="field-label">Nginx extensions:</label>\
								<textarea name="NGINX_EXT" class="textarea rule-list">~!:NGINX_EXT~!</textarea>\
							</div -->\
							<div class="form-row cc">\
								<label for="#" class="field-label">Statistics:</label>\
								<select name="STAT" class="styled">~!:STAT_OPTIONS~!</select>\
							</div>\
							<div class="stats-settings">\
								<div class="form-row cc">\
									<label for="#" class="field-label">Password Protection:</label>\
									<input id="stats-auth-enable" type="checkbox" name="STATS_AUTH" ~!:stats_auth_checked~!="" value="~!:STATS_AUTH~!" class="styled do_action_toggle_stats_block">\
								</div>\
								<div class="form-row stats-block ~!:ACTIVE_LOGIN~! cc">\
									<label for="#" class="field-label">stats login:</label>\
									<input type="text" class="text-field rule-statslogin" name="STATS_LOGIN" value="~!:STATS_LOGIN~!">\
								</div>\
								<div class="form-row pwd-box ~!:ACTIVE_PASSWORD~! stats-block cc">\
									<label for="#" class="field-label">password:</label>\
									<input type="text" value="~!:STATS_PASSWORD~!" name="STATS_PASSWORD" class="text-field rule-statspassword password">\
									<span class="generate-pwd do_action_generate_pass">Generate</span>\
								</div>\
							</div><!-- // stats settings -->\
							<div class="form-row cc">\
								<label for="#" class="field-label">SSL Support:</label>\
								<input type="checkbox" name="SSL" class="styled do_action_toggle_ssl_support ssl_support" ~!:ssl_checked~! value="~!SSL~!">\
							</div>\
							<div class="form-row cc ssl-crtfct-box">\
								<label for="#" class="field-label">SSL Shared DocRoot:</label>\
								<input type="checkbox" name="SSL_HOME" class="styled" ~!:ssl_home_checked~! value="~!SSL_HOME~!">\
							</div>\
                            <div class="form-row ssl-crtfct-box cc">\
								<label for="#" class="field-label">SSL Crtificate: <span class="remark">(upload file or paste as text)</span></label>\
								<span class="ssl-cert-input-dummy">...</span>\
								<textarea name="SSL_CRT" class="textarea ssl-cert">~!:SSL_CRT~!</textarea>\
							</div>\
							<div class="form-row ssl-crtfct-box cc">\
								<label for="#" class="field-label">SSL Certificate Key: <span class="remark">(upload file or paste as text)</span></label>\
								<span class="ssl-key-input-dummy">...</span>\
								<textarea name="SSL_KEY" class="textarea ssl-key">~!:SSL_KEY~!</textarea>\
							</div>\
                            <div class="form-row ssl-crtfct-box cc">\
								<label for="#" class="field-label">SSL Certificate CA: <span class="remark">(upload file or paste as text)</span></label>\
								<span class="ssl-ca-input-dummy">...</span>\
								<textarea name="SSL_CA" class="textarea ssl-key">~!:SSL_CA~!</textarea>\
							</div>\
						</div><!-- // advanced options -->\
						</div>\
                        <div class="form-options-group hidden">\
							<div class="group-header cc collapsed">\
								<span class="group-title-outer do_action_toggle_section">\
									<span class="group-title do_action_toggle_section">DNS options</span>\
								</span>									\
							</div>\
							<div class="sub_section hidden">\
                            <div class="form-row cc">\
								<label for="#" class="field-label">create DNS domain:</label>\
								<input type="checkbox" value="~!:DNS~!" ~!:CHECKED_DNS~! name="DNS" class="styled">\
							</div>\
						</div><!-- DNS options -->\
						<div class="form-options-group hidden">\
							<div class="group-header cc collapsed">\
								<span class="group-title-outer do_action_toggle_section">\
									<span class="group-title do_action_toggle_section">Mail options</span>\
								</span>									\
							</div>\
							<div class="sub_section hidden">\
                                <div class="form-row cc">\
                                    <label for="#" class="field-label">create mail domain:</label>\
                                    <input type="checkbox" value="~!:MAIL~!" ~!:CHECKED_MAIL~! name="MAIL" class="styled">\
                                </div>\
                            </div>\
                        </div>\
                        </div><!-- Mail options -->\
    					<div class="form-row cc">\
        					<label for="#" class="field-label">Create DNS domain also:</label>\
							<input type="checkbox" value="" name="DNS_DOMAIN" class="styled">\
						</div>\
						<div class="form-row buttons-row cc">\
                           <input class="add-entry-btn do_action_save_form" type="submit" value="~!:save_button~!"/>\
                           <span class="cancel-btn do_action_cancel_form">Cancel</span>\
                           ~!:DELETE_ACTION~!\
                        </div>\
					</div>'],
        ENTRIES_WRAPPER: ['<div class="domains-list items-list">~!:content~!</div>'],
        ENTRY: ['<div class="row domain-details-row ~!:SUSPENDED_CLASS~!">\
                        <input type="hidden" class="source" name="source" value=\'~!:source~!\' />\
                        <input type="hidden" class="target" name="target" value="" />\
						<div class="row-actions-box cc">\
							<div class="check-this check-control"></div>\
							<div class="row-operations">\
								~!:SUSPENDED_TPL~!\
							</div>\
						</div>\
						<div class="row-meta">\
							<div class="entry-created">~!:DATE~!</div>\
						</div>\
						<div class="row-details cc">\
							<div class="names">\
								<strong class="domain-name primary do_action_edit">~!:DOMAIN~!</strong>\
								~!:ALIAS~!\
							</div>\
							<div class="props-main">\
								<div class="ip-adr-box">\
									<span class="ip-adr">~!:IP~!</span>\
									<span class="prop-box template-box">\
										<span class="prop-title">template:</span>\
										<span class="prop-value tpl-item do_action_view_template_settings">~!:TPL~!</span>\
									</span>\
								</div>\
								\
								<!-- stats block -->\
								<div class="b-stats-box">\
									<div class="stats-box-title">stats</div>	\
                                    <!-- disk usage block -->\
                                    <div class="b-usage-box2 disk-usage cc">\
                                        <span class="prop-title">disk usage:</span>\
                                        <div class="usage-box ~!:OVER_DRAFT_VALUE~!">\
                                            <div class="value-box">\
                                                <div class="graph">\
                                                    <span style="left:~!:U_DISK_PERCENTAGE_2~!%;" class="value">~!:U_DISK_PERCENTAGE~!% <span class="value-size">(~!:U_DISK~! ~!:DISK_QUOTA_MEASURE~!)</span></span>\
                                                    <span style="width:~!:U_DISK_PERCENTAGE_3~!%;" class="bar"></span>\
                                                    ~!:OVER_BAR~!\
                                                </div>\
                                            </div>\
                                            <div class="max-size">~!:DISK_QUOTA~! <span class="units">~!:DISK_QUOTA_MEASURE_2~!</span></div>\
                                        </div>\
                                    </div><!-- // disk usage block -->\
									<!-- bandwidth block -->\
									<div class="b-usage-box2 bandwidth-box cc">\
                                        <span class="prop-title">bandwidth:</span>\
                                        <div class="usage-box">\
                                            <div class="value-box ~!:OVER_DRAFT_VALUE_2~!">\
                                                <div class="graph">\
                                                    <span style="left:~!:U_BANDWIDTH_PERCENTAGE_2~!%;" class="value">~!:U_BANDWIDTH_PERCENTAGE~!% <span class="value-size">(~!:U_BANDWIDTH~! ~!:BANDWIDTH_MEASURE~!)</span></span>\
                                                    <span style="width:~!:U_BANDWIDTH_PERCENTAGE_3~!%;" class="bar"></span>\
                                                    ~!:OVER_BAR_2~!\
                                                </div>\
                                            </div>\
                                            <div class="max-size">~!:BANDWIDTH~! <span class="units">~!:BANDWIDTH_MEASURE_2~!</span></div>\
                                        </div>\
                                    </div><!-- // bandwidth block -->\
								</div><!-- // stats block -->\
                                \
							</div>\
							<div class="props-additional">\
								<span class="prop-box php-box">\
									<span class="prop-title">php:</span>\
									<span class="prop-value">~!:PHP~!</span>\
								</span>\
								<span class="prop-box cgi-box">\
									<span class="prop-title">cgi:</span>\
									<span class="prop-value">~!:CGI~!</span>\
								</span>\
								<span class="prop-box elog-box">\
									<span class="prop-title">elog:</span>\
									<span class="prop-value">~!:ELOG~!</span>\
								</span>\
								<span class="prop-box stats-box">\
									<span class="prop-title">stats:</span>\
									<span class="prop-value">~!:STAT~!</span>\
									<span class="stats-auth stats-auth-on">\
										<span class="stats-auth-text">~!:STATS_AUTH~!</span>\
									</span>\
								</span>\
							</div>\
							<div class="props-ext">\
								<span class="prop-box ssl-box">\
									<span class="prop-title">ssl:</span>\
									<span class="prop-value">~!:SSL~!</span>\
								</span>\
								<span class="prop-box nginx-box">\
									<span class="prop-title">nginx:</span>\
									<span class="prop-value">~!:NGINX~!</span>\
									<span class="nginx-ext-list do_action_view_nginx_extensions">extension list</span>\
								</span>\
							</div>\
						</div><!-- // .row-details -->\
					</div>']
    },
    db: {
        USER_ITEMS_WRAPPER: ['<div class="db-user-box cc">~!:CONTENT~!</div>'],
        USER_ITEM: ['<span class="db-user-wrap">\
                        <span class="db-user">~!:NAME~!</span>\
                    </span>\
                    <span class="change-pwd do_action_change_db_user_password">change password</span>'],
        DIVIDER: ['<div class="db-devider">\
						<span class="db-devider-title">\
							<span class="db-devider-outer">\
								<span class="db-devider-inner">~!:TYPE~!</span>\
							</span>\
						</span>\
					</div>'],
        ENTRIES_WRAPPER: ['<div class="db-list">~!:content~!</div>'],
        FORM: ['<div id="~!:id~!"  class="b-new-entry b-new-entry_db form ~!:FORM_SUSPENDED~!">\
						<input type="hidden" name="source" class="source" value=\'~!:source~!\'>\
                        <input type="hidden" name="target" class="target" value=\'\'>\
                        <div class="entry-header">~!:title~!</div>\
						<div class="form-error hidden">\
						</div>\
						<div class="form-row cc">\
							<label for="#" class="field-label">Type:</label>\
							<select name="TYPE" class="styled">~!:TYPE_OPTIONS~!</select>\
						</div>\
						<div class="form-row cc">\
							<label for="#" class="field-label">DB name:</label>\
							<input type="text" class="text-field" name="DB" value="~!:DB~!">\
						</div>\
						<div class="db-credentials ">\
							<div class="form-row cc user">\
								<label for="#" class="field-label">Username:</label>\
								<input type="text" name="USER" class="text-field" value="~!:USER~!">\
							</div>\
							<div class="form-row pwd-box cc psw">\
								<label for="#" class="field-label">Password:</label>\
								<input type="text" name="PASSWORD" class="text-field password" value="~!:PASSWORD~!">\
								<span class="generate-pwd do_action_generate_pass">Generate</span>\
							</div>\
						</div>\
						<div class="form-row cc">\
							<label for="#" class="field-label">Host:</label>\
							<select name="HOST" class="styled">~!:HOST_OPTIONS~!</select>\
						</div>\
						<div class="form-row cc">\
							<label for="#" class="field-label">Character Set:</label>\
							<select name="CHARSET" class="styled">~!:CHARSET_OPTIONS~!</select>\
						</div>\
						<div class="form-row suspended cc">\
                            <label for="#" class="field-label">Suspended:</label>\
                            <input type="checkbox" ~!:SUSPENDED_CHECKED~! value="~!:SUSPENDED_VALUE~!" class="styled do_action_toggle_suspend" name="SUSPEND" />\
                        </div>\
                        <div class="form-row buttons-row cc">\
                           <input class="add-entry-btn do_action_save_form" type="submit" value="~!:save_button~!"/>\
                           <span class="cancel-btn do_action_cancel_form">Cancel</span>\
                           ~!:DELETE_ACTION~!\
                        </div>\
					</div>'],
        ENTRY: ['<div class="row db-details-row ~!:SUSPENDED_CLASS~!">\
						<input type="hidden" name="source" class="source" value=\'~!:source~!\'>\
                        <input type="hidden" name="target" class="target" value=\'\'>\
                        <div class="row-actions-box cc">\
							<div class="check-this check-control"></div>\
							<div class="row-operations">\
								~!:SUSPENDED_TPL~!\
							</div>\
						</div>\
						<div class="row-meta">\
							<div class="ownership">\
								<span class="prop-box">\
									<span class="prop-value">~!:OWNER~!</span>\
								</span>\
							</div>\
							<div class="entry-created">~!:DATE~!</div>\
						</div>\
						<div class="row-details cc">\
							<div class="props-main">\
								<div class="db-name-box">\
									<span class="db-name do_action_edit">~!:DB~!</span>\
								</div>\
							</div>\
							<div class="props-additional hidden">\
								<div class="db-user-box cc">\
									<span class="db-user-wrap backup-db do_action_open_inner_popup">\
										Users: ~!:USERS~!\
                                    </span>\
									<textarea class="inner-popup-html hidden">~!:USER_LIST~!</textarea>\
								</div>\
								<span class="add-db-user do_action_add_db_user">Add user</span>\
							</div>\
							<div class="props-ext">\
								<!-- span class="backup-db do_action_backup_db">backup</span-->\
									<span class="prop-box">\
										<span class="prop-title">Character Set:</span>\
										<span class="prop-value">~!:CHARSET~!</span>\
									</span>\
								<!-- disk usage block -->\
								<div class="b-usage-box disk-usage cc">\
									<div class="usage-box">\
										<div class="value-box">\
											<span class="value">~!:U_DISK~! Mb</span>\
											<div class="graph middle">\
												<span style="width:~!:U_DISK_PERCENTAGE~!%;" class="bar"></span>\
											</div>\
										</div>\
										<div class="max-size">~!:DISK~! <span class="units">~!:DISK_MEASURE~!</span></div>\
									</div>\
								</div><!-- // disk usage block -->\
							</div>\
						</div><!-- // .row-details -->\
					</div>']
    },
    cron: {
        FORM: ['<div class="b-new-entry b-new-entry_cron form ~!:FORM_SUSPENDED~!" id="~!:id~!" >\
						<input type="hidden" name="source" class="source" value=\'~!:source~!\'>\
                        <input type="hidden" name="target" class="target" value=\'\'>\
                        <div class="entry-header">~!:title~!</div>\
						<div class="form-error hidden">\
							<div class="error-box">\
							</div>\
						</div>\
						<div class="form-row form-row-line run-at-box cc">\
							<span class="row-header">Schedule Time:</span>\
							<div class="field-box cron-minute-box">\
								<label for="#" class="field-label ">Minute:<br>(0&mdash;59)</label>\
								<div class="field-box-inner cc">\
									<input type="text" value="~!:MIN~!" name="MIN" class="text-field rule-required rule-cronminute">\
								</div>\
							</div>\
							<div class="field-box cron-hour-box">\
								<label for="#" class="field-label">Hour:<br>(0&mdash;23)</label>\
								<div class="field-box-inner cc">\
									<input type="text" value="~!:HOUR~!" name="HOUR" class="text-field rule-required rule-cronhour">\
								</div>\
							</div>\
							<div class="field-box cron-day-box">\
								<label for="#" class="field-label">Day of Month:<br>(1&mdash;31)</label>\
								<div class="field-box-inner cc">\
									<input type="text" value="~!:DAY~!" name="DAY" class="text-field rule-required rule-cronday">\
								</div>\
							</div>\
							<div class="field-box cron-month-box">\
								<label for="#" class="field-label">Month:<br>(1&mdash;12) (Jan&mdash;Dec)</label>\
								<div class="field-box-inner cc">\
									<input type="text" value="~!:MONTH~!" name="MONTH" class="text-field rule-required rule-cronmonth">\
								</div>\
							</div>\
							<div class="field-box cron-week-box">\
								<label for="#" class="field-label">Day of Week:<br>(1&mdash;7) (Sun&mdash;Sat)</label>\
								<div class="field-box-inner cc">\
									<input type="text" value="~!:WDAY~!" name="WDAY" class="text-field rule-required rule-cronwday">\
								</div>\
							</div>\
						</div>\
						<div class="form-row cc">\
							<label for="#" class="field-label">Command:</label>\
							<textarea class="textarea rule-required" name="CMD">~!:CMD~!</textarea>\
						</div>\
						<div class="form-row cc hidden">\
							<label for="#" class="field-label">report to: <span class="remark">(devide by comma ",")</span></label>\
							<textarea class="textarea" name="REPORT_TO"></textarea>\
						</div>\
						<div class="form-row suspended cc">\
                            <label for="#" class="field-label">Suspended:</label>\
                            <input type="checkbox" ~!:SUSPENDED_CHECKED~! value="~!:SUSPENDED_VALUE~!" class="styled do_action_toggle_suspend" name="SUSPEND"/>\
                        </div>\
                        <div class="form-row buttons-row cc">\
                           <input class="add-entry-btn do_action_save_form" type="submit" value="~!:save_button~!"/>\
                           <span class="cancel-btn do_action_cancel_form">Cancel</span>\
                           ~!:DELETE_ACTION~!\
                        </div>\
					</div>'],
        ENTRIES_WRAPPER: ['<div class="cron-list">~!:content~!</div>'],
        ENTRY: ['<div class="row cron-details-row ~!:SUSPENDED_CLASS~!">\
						<input type="hidden" name="source" class="source" value=\'~!:source~!\'>\
                        <input type="hidden" name="target" class="target" value=\'\'>\
                        <div class="row-actions-box cc">\
							<div class="check-this check-control"></div>\
							<div class="row-operations">\
								~!:SUSPENDED_TPL~!\
							</div>\
						</div>\
						<div class="row-meta">\
							<div class="entry-created">~!:DATE~!</div>\
						</div>\
						<div class="row-details cc">\
							<div class="cron-meta">\
								<span class="prop-box cron-min">\
									<span class="prop-title">min</span>\
									<span class="prop-value">~!:MIN~!</span>\
								</span>\
								<span class="prop-box cron-hour">\
									<span class="prop-title">hour</span>\
									<span class="prop-value">~!:HOUR~!</span>\
								</span>\
								<span class="prop-box cron-day">\
									<span class="prop-title">day of Month</span>\
									<span class="prop-value">~!:DAY~!</span>\
								</span>\
								<span class="prop-box cron-month">\
									<span class="prop-title">Month</span>\
									<span class="prop-value">~!:MONTH~!</span>\
								</span>\
								<span class="prop-box cron-week">\
									<span class="prop-title">day of Week</span>\
									<span class="prop-value">~!:WDAY~!</span>\
								</span>\
							</div>\
							<div class="cron-command-box">\
								<strong class="cron-command-line do_action_edit">~!:CMD~!</strong>\
							</div>\
							<div class="cron-reported-to hidden">\
								<span class="prop-box cron-report-box">\
									<span class="prop-title">reported to:</span>\
									<span class="prop-value">naumov.socolov@gmail.com,</span>\
                                    <span class="prop-value">naumov.socolov@gmail.com,</span>\
								</span>\
							</div>\
						</div><!-- // .row-details -->\
					</div>']
    },
    backup: {
		WRAPPER: ['<div class="backups-list">~!:CONTENT~!</div>'],
		ENTRY:   ['<div class="backups-list">\
					<!-- row 1 -->\
					<div class="row first-row backup-details-row">\
						<div class="row-meta">\
							<div class="ownership">\
								<span class="prop-box">\
									<span class="prop-title">owner:</span>\
									<span class="prop-value">~!:OWNER~!</span>\
								</span>\
							</div>\
						</div>\
						<div class="row-details cc">\
							<div class="props-main">\
								<span class="prop-box entry-created">\
									<span class="backup-date">\
										<span class="backup-day">~!:CREATED_AT~!</span>\
										<span class="backup-time">~!:CREATED_AT_TIME~!</span>\
									</span>\
									<span class="backup-weekday">~!:CREATED_AT_WDAY~!</span>\
								</span>\
								<span class="prop-box generation-time">\
									<span class="prop-title">Generation time:</span>\
									<span class="prop-value">~!:GENERATION_TIME~!</span>\
								</span>\
							</div>\
							<div class="props-additional">\
								<span class="backup-size">\
									<span class="backup-size-inner">~!:SIZE~!</span>\
								</span>\
								<a class="backup-url" href="return alert(\'Not available at the time\');">download</a>\
							</div>\
							<div class="props-ext">\
								<!-- div class="backup-actions">\
									<a class="backup-actions-url restore-url" href="return alert(\'Not available at the time\');">restore</a>\
									<a class="backup-actions-url detailed-restore-url" href="return alert(\'Not available at the time\');">\
										<span class="detailed-restore-title">detailed</span>\
										<span class="detailed-restore-ext">restore</span>\
									</a>\
								</div -->\
							</div>							\
						</div><!-- // .row-details -->\
					</div><!-- // .row 1 -->']
	},
    stats: {
        WRAPPER: ['<div class="stats-list">~!:CONTENT~!</div>'],
        ENTRY: ['<div class="stats-block" style="min-height: 252px">\
                <h2 class="stats-block-header">~!:HEADER~!</h2>\
                <div class="stats-block-wrap">\
                    <img class="stats-graph" src="~!:IMG_SRC~!" alt="" />\
                </div>\
            </div>'],
        SUBMENU: ['<span class="stats-subbar"><span class="today sub-active" onClick="App.Actions.loadStats(\'today\')">today</span>\
                <span class="week" onClick="App.Actions.loadStats(\'week\')">week</span>\
                <span class="month" onClick="App.Actions.loadStats(\'month\')">month</span>\
                <span class="year" onClick="App.Actions.loadStats(\'year\')">year</span></div>']
    }
}


// Internals
var Tpl = App.Templates;
var Templator = function()
{
    var init = function() {
        fb.info('Templator work');
        Templator.splitThemAll();
        Templator.freezeTplIndexes();        
    };

    /**
     * Split the tpl strings into arrays
     */
    Templator.splitThemAll = function()
    {
        fb.info('splitting tpls');
        $.each(App.Templates.html, function(o) {
            var tpls = App.Templates.html[o];
            $.each(tpls, function(t) {
                tpls[t] = tpls[t][0].split('~!');
            });
        });
    },

    /**
     * Iterates tpls
     */
    Templator.freezeTplIndexes = function()
    {
        fb.info('freezing tpl keys');
        $.each(App.Templates.html, Templator.cacheTplIndexes);
    },

    /**
     * Grab the tpl group key and process it
     */
    Templator.cacheTplIndexes = function(key)
    {
        var tpls = App.Templates.html[key];
        $.each(tpls, function(o) {
            var tpl = tpls[o];
            Templator.catchIndex(key, o, tpl);
        });
    },

    /**
     * Set the indexes
     */
    Templator.catchIndex = function(key, ref_key, tpl)
    {
        'undefined' == typeof App.Templates._indexes[key]          ? App.Templates._indexes[key]          = {} : false;
        'undefined' == typeof App.Templates._indexes[key][ref_key] ? App.Templates._indexes[key][ref_key] = {} : false;
        $(tpl).each(function(index, o) {
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
Templator.getTemplate = function(ns, key)
{
    return [
        App.Templates._indexes[ns][key],
        App.Templates.html[ns][key].slice(0)
    ];
}
// init templator
Tpl.Templator = Templator();
Tpl.get = function(key, group)
{
    return Tpl.Templator.getTemplate(group, key);
}
