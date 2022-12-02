const VE = {
	// core functions
	core: {
		/**
		 * Main method that invokes further event processing
		 * @param root is root HTML DOM element that. Pass HTML DOM Element or css selector
		 * @param event_type (eg: click, mouseover etc..)
		 */
		register: (root, event_type) => {
			var root = !root ? 'body' : root; // if elm is not passed just bind events to body DOM Element
			var event_type = !event_type ? 'click' : event_type; // set event type to "click" by default
			$(root).bind(event_type, (evt) => {
				var elm = $(evt.target);
				VE.core.dispatch(evt, elm, event_type); // dispatch captured event
			});
		},
		/**
		 * Dispatch event that was previously registered
		 * @param evt related event object
		 * @param elm that was catched
		 * @param event_type (eg: click, mouseover etc..)
		 */
		dispatch: (evt, elm, event_type) => {
			if ('undefined' == typeof VE.callbacks[event_type]) {
				return VE.helpers.warn(
					'There is no corresponding object that should contain event callbacks for "' +
						event_type +
						'" event type'
				);
			}
			// get class of element
			var classes = $(elm).attr('class');
			// if no classes are attached, then just stop any further processings
			if (!classes) {
				return; // no classes assigned
			}
			// split the classes and check if it related to function
			$(classes.split(/\s/)).each((i, key) => {
				VE.callbacks[event_type][key] && VE.callbacks[event_type][key](evt, elm);
			});
		},
	},
	// menu and element navigation functions
	navigation: {
		state: {
			active_menu: 1,
			menu_selector: '.main-menu-item',
			menu_active_selector: '.active',
		},
		/**
		 * Create dialog box on the fly
		 * @param elm Element which contains the dialog contents
		 * @param dialog_title
		 * @param confirmed_location_url URL that will be redirected to if user hit "OK"
		 * @param custom_config Custom configuration parameters passed to dialog initialization (optional)
		 */
		createConfirmationDialog: (elm, dialog_title, confirmed_location_url, custom_config) => {
			var custom_config = !custom_config ? {} : custom_config;
			var config = {
				modal: true,
				//autoOpen: true,
				resizable: false,
				width: 360,
				title: dialog_title,
				close: function () {
					$(this).dialog('destroy');
				},
				buttons: {
					OK: function (event, ui) {
						location.href = confirmed_location_url;
					},
					Cancel: function () {
						$(this).dialog('close');
					},
				},
				create: function () {
					var buttonGroup = $(this).closest('.ui-dialog').find('.ui-dialog-buttonset');
					buttonGroup.find('button:first').addClass('button submit');
					buttonGroup.find('button:last').addClass('button button-secondary cancel');
				},
			};

			var reference_copied = $(elm[0]).clone();
			config = $.extend(config, custom_config);
			$(reference_copied).dialog(config);
		},
		enter_focused: () => {
			if ($('.units').hasClass('active')) {
				location.href = $(
					'.units.active .l-unit.focus .actions-panel__col.actions-panel__edit a'
				).attr('href');
			} else {
				if ($(VE.navigation.state.menu_selector + '.focus a').attr('href')) {
					location.href = $(VE.navigation.state.menu_selector + '.focus a').attr('href');
				}
			}
		},
		move_focus_left: () => {
			var index = parseInt(
				$(VE.navigation.state.menu_selector).index($(VE.navigation.state.menu_selector + '.focus'))
			);
			if (index == -1)
				index = parseInt(
					$(VE.navigation.state.menu_selector).index($(VE.navigation.state.menu_active_selector))
				);

			if ($('.units').hasClass('active')) {
				$('.units').removeClass('active');
				index++;
			}

			$(VE.navigation.state.menu_selector).removeClass('focus');

			if (index > 0) {
				$($(VE.navigation.state.menu_selector)[index - 1]).addClass('focus');
			} else {
				VE.navigation.switch_menu('last');
			}
		},
		move_focus_right: () => {
			var max_index = $(VE.navigation.state.menu_selector).length - 1;
			var index = parseInt(
				$(VE.navigation.state.menu_selector).index($(VE.navigation.state.menu_selector + '.focus'))
			);
			if (index == -1)
				index =
					parseInt(
						$(VE.navigation.state.menu_selector).index($(VE.navigation.state.menu_active_selector))
					) || 0;
			$(VE.navigation.state.menu_selector).removeClass('focus');

			if ($('.units').hasClass('active')) {
				$('.units').removeClass('active');
				index--;
			}

			if (index < max_index) {
				$($(VE.navigation.state.menu_selector)[index + 1]).addClass('focus');
			} else {
				VE.navigation.switch_menu('first');
			}
		},
		move_focus_down: () => {
			var max_index = $('.units .l-unit:not(.header)').length - 1;
			var index = parseInt($('.units .l-unit').index($('.units .l-unit.focus')));

			if (index < max_index) {
				$('.units .l-unit.focus').removeClass('focus');
				$($('.units .l-unit:not(.header)')[index + 1]).addClass('focus');

				$('html, body').animate({ scrollTop: $('.units .l-unit.focus').offset().top - 200 }, 200);
			}
		},
		move_focus_up: () => {
			var index = parseInt($('.units .l-unit:not(.header)').index($('.units .l-unit.focus')));

			if (index == -1) index = 0;

			if (index > 0) {
				$('.units .l-unit.focus').removeClass('focus');
				$($('.units .l-unit:not(.header)')[index - 1]).addClass('focus');

				$('html, body').animate({ scrollTop: $('.units .l-unit.focus').offset().top - 200 }, 200);
			}
		},
		switch_menu: (position) => {
			position = position || 'first'; // last

			if (VE.navigation.state.active_menu == 0) {
				VE.navigation.state.active_menu = 1;
				VE.navigation.state.menu_selector = '.main-menu-item';
				VE.navigation.state.menu_active_selector = '.active';

				if (position == 'first') {
					$($(VE.navigation.state.menu_selector)[0]).addClass('focus');
				} else {
					var max_index = $(VE.navigation.state.menu_selector).length - 1;
					$($(VE.navigation.state.menu_selector)[max_index]).addClass('focus');
				}
			}
		},
		shortcut: (elm) => {
			var action = elm.attr('key-action');

			if (action == 'js') {
				var e = elm.find('.data-controls');
				VE.core.dispatch(true, e, 'click');
			}
			if (action == 'href') {
				location.href = elm.find('a').attr('href');
			}
		},
	},
	notifications: {
		get_list: () => {
			/// TODO get notifications only once
			$.ajax({
				url: '/list/notifications/?ajax=1&token=' + $('#token').attr('token'),
				dataType: 'json',
			}).done((data) => {
				var acc = [];

				$.each(data, (i, elm) => {
					/** @type string */
					const tpl = App.Templates.notification
						.replace(':UNSEEN', elm.ACK ? 'unseen' : '')
						.replace(':ID', elm.ID)
						.replace(':TYPE', elm.TYPE)
						.replace(':TOPIC', elm.TOPIC)
						.replace(':NOTICE', elm.NOTICE)
						.replace(':TIME', elm.TIME)
						.replace(':DATE', elm.DATE);
					acc.push(tpl);
				});

				if (!Object.keys(data).length) {
					/** @type string */
					const tpl = App.Templates.notification_empty;
					acc.push(tpl);
				}

				$('.notification-container').html(acc.done()).removeClass('u-hidden');

				$('.notification-container .mark-seen').click((event) => {
					VE.notifications.delete($(event.target).attr('id').replace('notification-', ''));
				});
			});
		},
		delete: (id) => {
			$('#notification-' + id)
				.parent('li')
				.hide();
			$.ajax({
				url:
					'/delete/notification/?delete=1&notification_id=' +
					id +
					'&token=' +
					$('#token').attr('token'),
			});
			if ($('.notification-container li:visible').length == 0) {
				$('.js-notifications .status-icon').removeClass('status-icon');
				$('.js-notifications').removeClass('updates').removeClass('active');
			}
		},
	},
	// events callback functions
	callbacks: {
		click: {
			do_suspend: (evt, elm) => {
				var ref = elm.hasClass('actions-panel') ? elm : elm.parents('.actions-panel');
				var url = $('input[name="suspend_url"]', ref).val();
				var dialog_elm = ref.find('.js-confirm-dialog-suspend');
				VE.helpers.createConfirmationDialog(dialog_elm, $(elm).parent().attr('title'), url);
			},
			do_unsuspend: (evt, elm) => {
				var ref = elm.hasClass('actions-panel') ? elm : elm.parents('.actions-panel');
				var url = $('input[name="unsuspend_url"]', ref).val();
				var dialog_elm = ref.find('.js-confirm-dialog-suspend');
				VE.helpers.createConfirmationDialog(dialog_elm, $(elm).parent().attr('title'), url);
			},
			do_delete: (evt, elm) => {
				var ref = elm.hasClass('actions-panel') ? elm : elm.parents('.actions-panel');
				var url = $('input[name="delete_url"]', ref).val();
				var dialog_elm = ref.find('.js-confirm-dialog-delete');
				VE.helpers.createConfirmationDialog(dialog_elm, $(elm).parent().attr('title'), url);
			},
			do_servicerestart: (evt, elm) => {
				var ref = elm.hasClass('actions-panel') ? elm : elm.parents('.actions-panel');
				var url = $('input[name="servicerestart_url"]', ref).val();
				var dialog_elm = ref.find('.js-confirm-dialog-servicerestart');
				VE.helpers.createConfirmationDialog(dialog_elm, $(elm).parent().attr('title'), url);
			},
			do_servicestop: (evt, elm) => {
				var ref = elm.hasClass('actions-panel') ? elm : elm.parents('.actions-panel');
				var url = $('input[name="servicestop_url"]', ref).val();
				var dialog_elm = ref.find('.js-confirm-dialog-servicestop');
				VE.helpers.createConfirmationDialog(dialog_elm, $(elm).parent().attr('title'), url);
			},
		},
		mouseover: {},
		mouseout: {},
		keypress: {},
	},
	// simple handy methods
	helpers: {
		warn: (msg) => {
			alert('WARNING: ' + msg);
		},
		extendPasswordFields: () => {
			var references = ['.js-password-input'];

			$(document).ready(() => {
				$(references).each((i, ref) => {
					VE.helpers.initAdditionalPasswordFieldElements(ref);
				});
			});
		},
		initAdditionalPasswordFieldElements: (ref) => {
			var enabled = $.cookie('hide_passwords') == '1' ? true : false;
			if (enabled) {
				VE.helpers.hidePasswordFieldText(ref);
			}

			$(ref).prop('autocomplete', 'off');

			var enabled_html = enabled ? '' : 'show-passwords-enabled-action';
			var html =
				'<span class="toggle-password"><i class="toggle-psw-visibility-icon fas fa-eye-slash ' +
				enabled_html +
				'" onclick="VE.helpers.toggleHiddenPasswordText(\'' +
				ref +
				'\', this)"></i></span>';
			$(ref).after(html);
		},
		hidePasswordFieldText: (ref) => {
			$.cookie('hide_passwords', '1', { expires: 365, path: '/' });
			$(ref).prop('type', 'password');
		},
		revealPasswordFieldText: (ref) => {
			$.cookie('hide_passwords', '0', { expires: 365, path: '/' });
			$(ref).prop('type', 'text');
		},
		toggleHiddenPasswordText: (ref, triggering_elm) => {
			$(triggering_elm).toggleClass('show-passwords-enabled-action');

			if ($(ref).prop('type') == 'text') {
				VE.helpers.hidePasswordFieldText(ref);
			} else {
				VE.helpers.revealPasswordFieldText(ref);
			}
		},
	},
	tmp: {
		sort_par: 'sort-name',
		sort_direction: -1,
		sort_as_int: 0,
		form_changed: 0,
		search_activated: 0,
		search_display_interval: 0,
		search_hover_interval: 0,
	},
};

VE.helpers.extendPasswordFields();
