// Page navigation methods called by shortcuts
export default function navigationMethods() {
	return {
		state: {
			active_menu: 1,
			menu_selector: '.main-menu-item',
			menu_active_selector: '.active',
		},
		enterFocused: () => {
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
		moveFocusLeft: () => {
			let index = Number.parseInt(
				$(VE.navigation.state.menu_selector).index($(VE.navigation.state.menu_selector + '.focus'))
			);
			if (index == -1)
				index = Number.parseInt(
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
				VE.navigation.switchMenu('last');
			}
		},
		moveFocusRight: () => {
			const max_index = $(VE.navigation.state.menu_selector).length - 1;
			let index = Number.parseInt(
				$(VE.navigation.state.menu_selector).index($(VE.navigation.state.menu_selector + '.focus'))
			);
			if (index == -1)
				index =
					Number.parseInt(
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
				VE.navigation.switchMenu('first');
			}
		},
		moveFocusDown: () => {
			const max_index = $('.units .l-unit:not(.header)').length - 1;
			const index = Number.parseInt($('.units .l-unit').index($('.units .l-unit.focus')));

			if (index < max_index) {
				$('.units .l-unit.focus').removeClass('focus');
				$($('.units .l-unit:not(.header)')[index + 1]).addClass('focus');

				$('html, body').animate({ scrollTop: $('.units .l-unit.focus').offset().top - 200 }, 200);
			}
		},
		moveFocusUp: () => {
			let index = Number.parseInt(
				$('.units .l-unit:not(.header)').index($('.units .l-unit.focus'))
			);

			if (index == -1) index = 0;

			if (index > 0) {
				$('.units .l-unit.focus').removeClass('focus');
				$($('.units .l-unit:not(.header)')[index - 1]).addClass('focus');

				$('html, body').animate({ scrollTop: $('.units .l-unit.focus').offset().top - 200 }, 200);
			}
		},
		switchMenu: (position) => {
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
			const action = elm[0].dataset.keyAction;
			switch (action) {
				case 'js': {
					elm[0].querySelector('.data-controls').click();
					break;
				}

				case 'href': {
					location.href = elm.find('a').attr('href');
					break;
				}

				default: {
					break;
				}
			}
		},
	};
}
