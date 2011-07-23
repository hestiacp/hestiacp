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
        loading: ['<div id="loading" style="top: 0;font-size:19px;font-weight: bol;position:absolute;width: 150px; background-color:yellow;z-index: 9999; padding: 8px;left: 50%;margin-left:-75px;">\
                <center>Loading...</center>\
                </div>'],
        popup: ['<div class="black_overlay" id="popup-bg"></div>\
                <div class="popup_content" id="popup"><button class="do_action_close_popup">close</button>~!:content~!</div>'],
        select_option: ['<option ~!:SELECTED~! value="~!:VALUE~!">~!:TEXT~!</option>'],
        error_elm: ['<div class="error-box">~!:ERROR~!</div>']     
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
                    <div class="errors">\
                    </div>\
                    <div class="form-row cc">\
                            <input type="hidden" value="~!:DATE~!" name="DATE">\
                            <label for="#" class="field-label">domain:</label>\
                            <input type="text" name="DNS_DOMAIN" value="~!:DNS_DOMAIN~!" class="text-field DNS_DOMAIN">\
                    </div>\
                    <div class="form-row cc">\
                            <label for="#" class="field-label">ip address:</label>\
                            <div class="autocomplete-box">\
                                    <input type="text" name="IP" value="~!:IP~!" class="text-field IP">\
                                    <i class="arrow">&nbsp;</i>\
                            </div>\
                    </div>\
                    <div class="form-row dns-template-box cc">\
                            <label for="#" class="field-label">template:</label>\
                            <span class="select" id="selecttemplate">~!:TPL_DEFAULT_VALUE~!</span>\
                                <select name="TPL" class="styled">\
                                       ~!:TPL~!\
                                </select>\
                            <span class="context-settings do_action_view_template_settings">View template settings</span>\
                    </div>\
                    <div class="form-row cc">\
                            <label for="#" class="field-label">ttl:</label>\
                            <input type="text" value="~!:TTL~!" name="TTL" class="text-field ttl-field">\
                    </div>\
                    <div class="form-row cc">\
                            <label for="#" class="field-label">soa:</label>\
                            <input type="text" value="~!:SOA~!" name="SOA" class="text-field">\
                    </div>\
                    <div class="form-row buttons-row cc">\
                            <input type="submit" value="~!:save_button~!" class="add-entry-btn do_action_save_form" name="save">\
                            <span class="cancel-btn do_action_cancel_form">Cancel</span>\
                            <span class="help-btn do_action_form_help">Help</span>\
                    </div>\
            </div>'
        ],
        SUSPENDED_TPL_ENABLED : ['<span class="ip-status-info ip-enabled-status"><span class="ip-status-text">enabled</span></span>\
                                <span class="delete-entry"><span class="delete-entry-text do_action_delete_ip">delete</span></span>'],
        SUSPENDED_TPL_DISABLED : ['<span class="ip-status-info ip-suspended-status do_action_delete_dns"><span class="ip-status-text">suspended</span></span>'],
        ENTRIES_WRAPPER: ['<div class="dns-list items-list">~!:content~!</div>'],
        ENTRY: ['<div class="row dns-details-row ~!:CHECKED~!">\
                            <input type="hidden" name="source" class="source" value=\'~!:source~!\'>\
                            <input type="hidden" class="target" name="target" value="" />\
                            <div class="row-actions-box cc">\
                                        <div class="check-this check-control"></div>\
                                        <div class="row-operations">\
                                                ~!:SUSPENDED_TPL~!\
                                                <span class="delete-entry"><span class="delete-entry-text do_action_delete_entry">delete</span></span>\
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
                        </div>'],
        SUBFORM: ['<div class="b-new-entry b-records-list subform">\
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
                            <span class="help-btn">Help</span>\
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
                                    <span class="select RECORD_TYPE" id="selectRECORD_TYPE">~!:RECORD_TYPE_VALUE~!</span>\
                                    <select name="RECORD_TYPE" class="styled">\
                                        ~!:RECORD_TYPE~!\
                                    </select>\
                            </div>\
                            <div class="field-box dns-value-box">\
                                    <label for="#" class="field-label">value:</label>\
                                    <input type="text" value="~!:RECORD_VALUE~!" class="text-field RECORD_VALUE">\
                            </div>\
                            <div class="delete-record do_action_delete_subentry"></div>\
                    </div>']
    },
    ip: {
        FORM: ['\
            <div class="b-new-entry b-new-entry_ip" id="~!:id~!">\
                <input type="hidden" name="source" class="source" value=\'~!:source~!\'>\
                <div class="entry-header">~!:title~!</div>\
                <div class="errors">\
		</div>\
                <div class="form-row cc">\
                        <label for="#" class="field-label">ip address:</label>\
                        <input type="text" value="~!:IP_ADDRESS~!" name="IP_ADDRESS" class="text-field">\
                </div>\
                <div class="form-row cc">\
                        <label for="#" class="field-label">owner:</label>\
                        <span class="select" id="select2">vesta</span>\
                        <select name="OWNER" class="styled OWNER">\
                                ~!:owner_options~!\
                        </select>\
                </div>\
                <div class="form-row cc">\
                        <label for="#" class="field-label">status:</label>\
                        <span class="select" id="select">shared</span>\
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
                        <span class="select" id="select">eth1</span>\
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
                        <input type="submit" value="~!:save_button~!" name="save" class="add-entry-btn do_action_save_form">\
                        <span class="cancel-btn do_action_cancel_form">Cancel</span>\
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
        ENTRIES_WRAPPER: ['<div class="ip-list items-list">~!:content~!</div>'],
        SUSPENDED_TPL_ENABLED : ['<span class="ip-status-info ip-enabled-status"><span class="ip-status-text">enabled</span></span>\
                                <span class="delete-entry"><span class="delete-entry-text do_action_delete_ip">delete</span></span>'],
        SUSPENDED_TPL_DISABLED : ['<span class="ip-status-info ip-suspended-status do_action_delete_ip"><span class="ip-status-text">suspended</span></span>']
    },
    user: {
        ENTRIES_WRAPPER: ['<div class="users-list items-list">~!:content~!</div>'],
        FORM:  ['<div id="~!:id~!" class="b-new-entry b-new-entry_user">\
                        <input type="hidden" name="source" class="source" value=\'~!:source~!\'>\
                        <input type="hidden" name="target" class="target" value=\'\'>\
						<div class="entry-header">~!:title~!</div>\
						<div class="form-error hidden">\
						</div>\
						<div class="form-row cc">\
							<label for="#" class="field-label">username:</label>\
							<input type="text" class="text-field" value="~!:LOGIN_NAME~!" name="LOGIN_NAME">\
						</div>\
						<div class="form-row pwd-box cc">\
							<label for="#" class="field-label">password:</label>\
							<input type="text" class="text-field password" name="PASSWORD" value="~!:PASSWORD~!">\
							<span class="generate-pwd do_action_generate_pass">Generate</span>\
						</div>\
						<div class="form-row cc">\
							<label for="#" class="field-label">package:</label>\
							<select name="PACKAGE" class="not-styled">\
								~!:PACKAGE_OPTIONS~!\
							</select>\
						</div>\
                        <div class="form-row cc">\
							<label for="#" class="field-label">shell:</label>\
							<select class="not-styled" name="SHELL">\
								~!:SHELL_OPTIONS~!\
							</select>\
						</div>\
						<div class="form-row cc">\
							<label for="#" class="field-label">role:</label>\
							<select class="not-styled" name="ROLE">\
								~!:ROLE_OPTIONS~!\
							</select>\
						</div>\
						<div class="form-row cc">\
							<label for="#" class="field-label">contact email:</label>\
							<input type="text" name="CONTACT" class="text-field" value="~!:CONTACT~!">\
						</div>\
						<div class="form-row cc">\
							<label for="#" class="field-label">reports:</label>\
							<input type="checkbox" name="REPORTS_ENABLED" class="not-styled" value="~!:REPORTS_ENABLED~!">\
						</div>\
						<div class="form-row cc">\
							<label for="#" class="field-label">ns1:</label>\
							<input type="text" name="NS" class="text-field" value="~!:NS~!">\
						</div>\
						<!-- div class="form-row cc">\
							<label for="#" class="field-label">ns2:</label>\
							<input type="text" value="custom-edge-cdn2.digital-photography-school.com" class="text-field">\
						</div -->\
                        <div class="form-row buttons-row cc">\
							<input type="submit" value="~!:save_button~!" name="save" class="add-entry-btn do_action_save_form">\
							<span class="cancel-btn do_action_cancel_form">Cancel</span>\
							<a class="help-btn" href="http://vestacp.com/docs/user/" target="_blank">Help</a>\
						</div>\
					</div>'],
        ENTRY: ['<div class="row first-row user-details-row">\
                    <input type="hidden" class="source" name="source" value=\'~!:source~!\' />\
                    <input type="hidden" class="target" name="target" value="" />\
						<div class="row-actions-box cc">\
							<div class="check-this check-control"></div>\
							<div class="row-operations">\
								~!:SUSPENDED_TPL~!\
								<span class="delete-entry do_action_delete_entry"><span class="delete-entry-text do_action_delete_entry">delete</span></span>\
							</div>\
						</div>\
						<div class="row-meta">\
							<div class="entry-created">~!:DATE~!</div>\
							<div class="ownership">\
								<span class="prop-box">\
									<span class="prop-title">owner:</span>\
									<span class="prop-value">~!:OWNER~!</span>\
								</span>\
							</div>\
						</div>\
						<div class="row-details cc">\
							<div class="props-main">\
								<div class="user-wrap">\
									<div class="username-box">\
										<span class="user">\
											<span class="nickname do_action_edit">~!:LOGIN_NAME~!</span>\
											<span class="role">(~!:ROLE~!)</span>\
										</span>\
										<span class="prop-box template-box">\
											<span class="prop-title">package:</span>\
											<span class="prop-value">~!:PACKAGE~!</span>\
										</span>\
									</div>\
									<div class="user-details-box">\
                                        <span class="prop-box user-name">\
                                            <span class="prop-title">name:</span>\
                                            <span class="prop-value">~!:FULLNAME~!</span>\
                                        </span>\
                                        <span class="prop-box prop-box_group-values cc user-details">\
                                            <span class="prop-title">email:</span>\
                                            <span class="group-values">\
                                                <span class="prop-value user-email">~!:CONTACT~!</span>\
                                                <span class="prop-value user-reports">(reports ~!:REPORTS_ENABLED~!)</span>\
                                                </span>\
                                        </span>\
                                        <span class="prop-box childs-box">\
                                        <span class="prop-title">childs:</span>\
                                            <span class="prop-value prop-value-collapsed-childs">~!:U_CHILDS~! (~!:MAX_CHILDS~!)</span>\                                        </span>\
									</div>\
								</div>\
								<!-- disk usage block -->\
								<div class="b-usage-box disk-usage cc">\
									<span class="prop-title">disk usage:</span>\
									<div class="usage-box">\
										<div class="value-box">\
											<span class="value">~!:U_DISK~!</span>\
											<div class="graph low">\
												<span style="width:~!:U_DISK_PERCENTAGE~!%;" class="bar"></span>\
											</div>\
										</div>\
										<div class="max-size">~!:DISK_QUOTA~!<span class="units">~!:DISK_QUOTA_MEASURE~!</span></div>\
									</div>\
								</div><!-- // disk usage block -->\
								<!-- bandwidth usage block -->\
								<div style="margin-bottom:25px;" class="b-usage-box bandwidth-box cc">\
									<span class="prop-title">bandwidth:</span>\
									<div class="usage-box">\
										<div class="value-box">\
											<span class="value">~!:U_BANDWIDTH~!</span>\
											<div class="graph low">\
												<span style="width:30%;" class="bar"></span>\
											</div>\
										</div>\
										<div class="max-size">~!:BANDWIDTH~!<span class="units">~!:BANDWIDTH_MEASURE~!</span></div>\
									</div>\
								</div><!-- // bandwidth usage block -->\
								<!-- disk usage block -->\
								 <!-- // disk usage block -->\
								<!-- bandwidth usage block -->\
								 <!-- // bandwidth usage block -->\
                                 </div>\
							<div class="props-additional">\
								<span class="prop-box webdomains-box">\
									<span class="prop-title">web domains:</span>\
									<span class="prop-value">~!:U_DNS_DOMAINS~! (~!:WEB_DOMAINS~!)</span>\
								</span>\
								<span class="prop-box websl-box">\
									<span class="prop-title">web ssl:</span>\
									<span class="prop-value">~!:U_WEB_SSL~! (~!:WEB_SSL~!)</span>\
								</span>\
								<span class="prop-box webalias-box">\
									<span class="prop-title">web alias:</span>\
									<span class="prop-value prop-value-collapsed-childs">(~!:WEB_ALIASES~!) per domain</span>\
								</span>\
								<span class="prop-box prop-box_group-values cc webtpl-box">\
									<span class="prop-title">web templates:</span>\
									<span class="group-values group-values__bullet">\
										~!:WEB_TPL~!\
									</span>\
								</span>\
								<span class="prop-box db-box">\
									<span class="prop-title">databases:</span>\
									<span class="prop-value">~!:U_DATABASES~! (~!:DATABASES~!)</span>\
								</span>\
								<span class="prop-box shell-box">\
									<span class="prop-title">shell:</span>\
									<span class="prop-value">~!:SHELL~!</span>\
								</span>\
								<span class="prop-box backups-box">\
									<span class="prop-title">backups:</span>\
									<span class="prop-value">retention ~!:BACKUPS~!</span>\
								</span>\
							</div>\
							<div class="props-ext">\
								<span class="prop-box mailboxes-box">\
									<span class="prop-title">mailboxes:</span>\
									<span class="prop-value">~!:U_MAIL_BOXES~! (~!:MAIL_BOXES~!)</span>\
								</span>\
								<span class="prop-box mailfwds-box">\
									<span class="prop-title">mail forwarders:</span>\
									<span class="prop-value">~!:U_MAIL_FORWARDERS~! (~!:MAIL_FORWARDERS~!)</span>\
								</span>\
								<span class="prop-box maildomains-box">\
									<span class="prop-title">mail domains:</span>\
									<span class="prop-value">~!:U_MAIL_DOMAINS~! (~!:MAIL_DOMAINS~!)</span>\
								</span>\
								<span class="prop-box dnsdomains-box">\
									<span class="prop-title">dns domains:</span>\
									<span class="prop-value">~!:U_DNS_DOMAINS~! (~!:DNS_DOMAINS~!)</span>\
								</span>\
								<span class="prop-box prop-box_group-values cc ns-list-box">\
									<span class="prop-title">ns list:</span>\
									<span class="group-values group-values__bullet">~!:NS~!</span>\
								</span>\
							</div>\
						</div><!-- // .row-details -->\
					</div>']
    },
    web_domain: {
        ENTRIES_WRAPPER: ['<div class="domains-list items-list">~!:content~!</div>'],
        ENTRY: ['<div class="row first-row domain-details-row">\
						<div class="row-actions-box cc">\
							<div class="check-this check-control"></div>\
							<div class="row-operations">\
								<span class="ip-status-info ip-enabled-status"><span class="ip-status-text">enabled</span></span>\
								<span class="delete-entry"><span class="delete-entry-text">delete</span></span>\
							</div>\
						</div>\
						<div class="row-meta">\
							<div class="entry-created">~!:DATE~!</div>\
						</div>\
						<div class="row-details cc">\
							<div class="names">\
								<strong class="domain-name primary">~!:DOMAIN~!</strong>\
								<span class="alias-title">Alias:</span>\
								~!:ALIAS~!\
							</div>\
							<div class="props-main">\
								<div class="ip-adr-box">\
									<span class="ip-adr">~!:IP~!</span>\
									<span class="prop-box template-box">\
										<span class="prop-title">template:</span>\
										<span class="prop-value">~!:TPL~!</span>\
									</span>\
								</div>\
								<!-- disk usage block -->\
								<div class="b-usage-box disk-usage cc">\
									<span class="prop-title">disk usage:</span>\
									<div class="usage-box">\
										<div class="value-box">\
											<span class="value">~!:U_DISK~!</span>\
											<div class="graph low">\
												<span style="width:~!:U_DISK_PERCENTS~!%;" class="bar"></span>\
											</div>\
										</div>\
										<div class="max-size">~!:DISK~!<span class="units">Mb</span></div>\
									</div>\
								</div><!-- // disk usage block -->\
								<!-- bandwidth usage block -->\
								<div class="b-usage-box bandwidth-box cc">\
									<span class="prop-title">bandwidth:</span>\
									<div class="usage-box">\
										<div class="value-box">\
											<span class="value">~!:U_BANDWIDTH~!</span>\
											<div class="graph low">\
												<span style="width:U_BANDWIDTH_PERCENTS%;" class="bar"></span>\
											</div>\
										</div>\
										<div class="max-size">~!:BANDWIDTH~! <span class="units">Mb</span></div>\
									</div>\
								</div><!-- // bandwidth usage block -->\
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
									<span class="prop-value">~!:STATS~!</span>\
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
									<span class="nginx-ext-list">extension list</span>\
								</span>\
							</div>							\
						</div><!-- // .row-details -->\
					</div>']
    },
    db: {
        ENTRIES_WRAPPER: ['<div class="db-list">~!:content~!</div>'],
        ENTRY: ['<div class="row first-row db-details-row">\
						<div class="row-actions-box cc">\
							<div class="check-this check-control"></div>\
							<div class="row-operations">\
								<span class="delete-entry"><span class="delete-entry-text">delete</span></span>\
							</div>\
						</div>\
						<div class="row-meta">\
							<div class="ownership">\
								<span class="prop-box">\
									<span class="prop-value">Javier Henneman</span>\
								</span>\
							</div>\
							<div class="entry-created">~!:DATE~!</div>\
						</div>\
						<div class="row-details cc">\
							<div class="props-main">\
								<div class="db-name-box">\
									<span class="db-name">~!:DB_NAME~!</span>\
								</div>\
							</div>\
							<div class="props-additional">\
								<div class="db-user-box cc">\
									<span class="db-user-wrap">\
										<span class="db-user">~!:USER~!</span>\
									</span>\
									<span class="change-pwd">change password</span>										\
								</div>\
								<div class="db-user-box cc">\
									<span class="db-user-wrap">\
										<span class="db-user">socialmediaexaminer (read only)</span>\
									</span>\
									<span class="change-pwd">change password</span>										\
								</div>\
								<span class="add-db-user">Add user</span>\
							</div>\
							<div class="props-ext">\
								<span class="backup-db">backup</span>\
								<!-- disk usage block -->\
								<div class="b-usage-box disk-usage cc">\
									<div class="usage-box">\
										<div class="value-box">\
											<span class="value">~!:U_DISK~!</span>\
											<div class="graph middle">\
												<span style="width:55%;" class="bar"></span>\
											</div>\
										</div>\
										<div class="max-size">~!:DISK~! <span class="units">Mb</span></div>\
									</div>\
								</div><!-- // disk usage block -->\
							</div>\
						</div><!-- // .row-details -->\
					</div>']
    },
    cron: {
        ENTRIES_WRAPPER: ['<div class="db-list">~!:content~!</div>'],
        ENTRY: ['<div class="row first-row cron-details-row">\
						<div class="row-actions-box cc">\
							<div class="check-this check-control"></div>\
							<div class="row-operations">\
								<span class="ip-status-info ip-enabled-status"><span class="ip-status-text">enabled</span></span>\
								<span class="delete-entry"><span class="delete-entry-text">delete</span></span>\
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
								<strong class="cron-command-line">~!:CMD~!</strong>\
							</div>\
							<div class="cron-reported-to">\
								<span class="prop-box cron-report-box">\
									<span class="prop-title">reported to:</span>\
									<span class="prop-value">naumov.socolov@gmail.com,</span>\
									<span class="prop-value">vasysualiy.pupkin@gmail.com,</span>\
									<span class="prop-value">na-derevniu-dedushke@dachniy-poselok-za-mkadom.com,</span>\
									<span class="prop-value">vasysualiy.pupkin@gmail.com</span>\
								</span>\
							</div>\
						</div><!-- // .row-details -->\
					</div>']
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
