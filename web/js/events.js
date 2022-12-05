const VE = {
	core: {
		/**
		 * Main method that invokes further event processing
		 * @param root is root HTML DOM element that. Pass HTML DOM Element or css selector
		 * @param event_type (eg: click, mouseover etc..)
		 */
		register: (root, event_type) => {
			root = !root ? 'body' : root; // if elm is not passed just bind events to body DOM Element
			event_type = !event_type ? 'click' : event_type; // set event type to "click" by default
			$(root).bind(event_type, (evt) => {
				VE.core.dispatch(evt, $(evt.target), event_type); // dispatch captured event
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
			const classes = $(elm).attr('class');
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
			custom_config = custom_config ?? {};
			const default_config = {
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
					const buttonGroup = $(this).closest('.ui-dialog').find('.ui-dialog-buttonset');
					buttonGroup.find('button:first').addClass('button submit');
					buttonGroup.find('button:last').addClass('button button-secondary cancel');
				},
			};

			const reference_copied = $(elm[0]).clone();
			const config = { ...default_config, ...custom_config };
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
			let index = parseInt(
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
			const max_index = $(VE.navigation.state.menu_selector).length - 1;
			let index = parseInt(
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
			const max_index = $('.units .l-unit:not(.header)').length - 1;
			let index = parseInt($('.units .l-unit').index($('.units .l-unit.focus')));

			if (index < max_index) {
				$('.units .l-unit.focus').removeClass('focus');
				$($('.units .l-unit:not(.header)')[index + 1]).addClass('focus');

				$('html, body').animate({ scrollTop: $('.units .l-unit.focus').offset().top - 200 }, 200);
			}
		},
		move_focus_up: () => {
			let index = parseInt($('.units .l-unit:not(.header)').index($('.units .l-unit.focus')));

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
					const max_index = $(VE.navigation.state.menu_selector).length - 1;
					$($(VE.navigation.state.menu_selector)[max_index]).addClass('focus');
				}
			}
		},
		shortcut: (elm) => {
			/** @type {'js' | 'href'} */
			const action = elm.attr('key-action');

			switch (action) {
				case 'js':
					VE.core.dispatch(true, elm.find('.data-controls'), 'click');
					break;

				case 'href':
					location.href = elm.find('a').attr('href');
					break;

				default:
					break;
			}
		},
	},
	callbacks: {
		click: {
			do_suspend: (evt, elm) => {
				const ref = elm.hasClass('actions-panel') ? elm : elm.parents('.actions-panel');
				const url = $('input[name="suspend_url"]', ref).val();
				const dialog_elm = ref.find('.js-confirm-dialog-suspend');
				VE.helpers.createConfirmationDialog(dialog_elm, $(elm).parent().attr('title'), url);
			},
			do_unsuspend: (evt, elm) => {
				const ref = elm.hasClass('actions-panel') ? elm : elm.parents('.actions-panel');
				const url = $('input[name="unsuspend_url"]', ref).val();
				const dialog_elm = ref.find('.js-confirm-dialog-suspend');
				VE.helpers.createConfirmationDialog(dialog_elm, $(elm).parent().attr('title'), url);
			},
			do_delete: (evt, elm) => {
				const ref = elm.hasClass('actions-panel') ? elm : elm.parents('.actions-panel');
				const url = $('input[name="delete_url"]', ref).val();
				const dialog_elm = ref.find('.js-confirm-dialog-delete');
				VE.helpers.createConfirmationDialog(dialog_elm, $(elm).parent().attr('title'), url);
			},
			do_servicerestart: (evt, elm) => {
				const ref = elm.hasClass('actions-panel') ? elm : elm.parents('.actions-panel');
				const url = $('input[name="servicerestart_url"]', ref).val();
				const dialog_elm = ref.find('.js-confirm-dialog-servicerestart');
				VE.helpers.createConfirmationDialog(dialog_elm, $(elm).parent().attr('title'), url);
			},
			do_servicestop: (evt, elm) => {
				const ref = elm.hasClass('actions-panel') ? elm : elm.parents('.actions-panel');
				const url = $('input[name="servicestop_url"]', ref).val();
				const dialog_elm = ref.find('.js-confirm-dialog-servicestop');
				VE.helpers.createConfirmationDialog(dialog_elm, $(elm).parent().attr('title'), url);
			},
		},
	},
	helpers: {
		warn: (msg) => {
			alert('WARNING: ' + msg);
		},
		extendPasswordFields: () => {
			const references = ['.js-password-input'];

			$(document).ready(() => {
				$(references).each((i, ref) => {
					VE.helpers.initAdditionalPasswordFieldElements(ref);
				});
			});
		},
		initAdditionalPasswordFieldElements: (ref) => {
			const enabled = $.cookie('hide_passwords') == '1' ? true : false;
			if (enabled) {
				$.cookie('hide_passwords', '1', { expires: 365, path: '/' });
				$(ref).prop('type', 'password');
			}

			$(ref).prop('autocomplete', 'off');

			const html =
				'<span class="toggle-password"><i class="toggle-psw-visibility-icon fas fa-eye-slash ' +
				enabled
					? ''
					: 'show-passwords-enabled-action' +
					  '" onclick="VE.helpers.toggleHiddenPasswordText(\'' +
					  ref +
					  '\', this)"></i></span>';
			$(ref).after(html);
		},
		toggleHiddenPasswordText: (ref, triggering_elm) => {
			$(triggering_elm).toggleClass('show-passwords-enabled-action');

			if ($(ref).prop('type') == 'text') {
				$.cookie('hide_passwords', '1', { expires: 365, path: '/' });
				$(ref).prop('type', 'password');
			} else {
				$.cookie('hide_passwords', '0', { expires: 365, path: '/' });
				$(ref).prop('type', 'text');
			}
		},
	},
	tmp: {
		sort_par: 'sort-name',
		sort_direction: -1,
		sort_as_int: false,
	},
};

VE.helpers.extendPasswordFields();
