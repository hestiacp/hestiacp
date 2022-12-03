document.documentElement.classList.replace('no-js', 'js');

document.addEventListener('DOMContentLoaded', () => {
	if (document.querySelector('.body-login')) {
		document.querySelector('input').focus();
	}

	window.addEventListener('scroll', setStickyClass);
	setStickyClass();

	document.querySelectorAll('.button').forEach((el) => {
		el.addEventListener('click', (evt) => {
			const action = evt.target.dataset.action;
			const id = evt.target.dataset.id;
			if (action == 'submit' && document.querySelector(`#${id}`)) {
				evt.preventDefault();
				document.querySelector(`#${id}`).submit();
			}
		});
	});

	document.querySelectorAll('.toolbar-right .sort-by').forEach((el) => {
		el.addEventListener('click', () => $('.context-menu.sort-order').toggle());
	});

	$('.submenu-select-dropdown').each(() => {
		$(this).wrap("<span class='submenu-select-wrapper'></span>");
		$(this).after("<span class='holder'></span>");
	});
	$('.submenu-select-dropdown')
		.change(() => {
			const selectedOption = $(this).find(':selected').text();
			$(this).next('.holder').text(selectedOption);
		})
		.trigger('change');

	$('.js-to-top').on('click', () => {
		$('html, body').animate({ scrollTop: 0 }, 'normal');
	});

	// SEARCH BOX
	$('.toolbar-search .js-search-input').hover(
		() => {
			clearTimeout(VE.tmp.search_display_interval);
			clearTimeout(VE.tmp.search_hover_interval);
			VE.tmp.search_display_interval = setTimeout(() => {
				$('.js-search-input').addClass('activated');
			}, 150);
		},
		() => {
			clearTimeout(VE.tmp.search_display_interval);
			clearTimeout(VE.tmp.search_hover_interval);
			VE.tmp.search_hover_interval = setTimeout(() => {
				if (!VE.tmp.search_activated && !$('.js-search-input').val().length) {
					$('.js-search-input').removeClass('activated');
				}
			}, 600);
		}
	);

	$('.js-search-input').focus(() => {
		VE.tmp.search_activated = true;
		clearTimeout(VE.tmp.search_hover_interval);
	});
	$('.js-search-input').blur(() => {
		VE.tmp.search_activated = false;
		clearTimeout(VE.tmp.search_hover_interval);
		VE.tmp.search_hover_interval = setTimeout(() => {
			if (!$('.js-search-input').val().length) {
				$('.js-search-input').removeClass('activated');
			}
		}, 600);
	});

	// TIMER
	if ($('.movement.left').length) {
		VE.helpers.refresh_timer.right = $('.movement.right');
		VE.helpers.refresh_timer.left = $('.movement.left');
		VE.helpers.refresh_timer.start();

		$('.pause').click(() => {
			VE.helpers.refresh_timer.stop();
			$('.pause').addClass('u-hidden');
			$('.play').removeClass('u-hidden');
			$('.refresh-timer').addClass('paused');
		});

		$('.play').click(() => {
			VE.helpers.refresh_timer.start();
			$('.pause').removeClass('u-hidden');
			$('.play').addClass('u-hidden');
			$('.refresh-timer').removeClass('paused');
		});
	}

	// SORTING
	document
		.querySelectorAll('#vstobjects input, #vstobjects select, #vstobjects textarea')
		.forEach((el) => {
			el.addEventListener('change', () => {
				VE.tmp.form_changed = true;
			});
		});

	$('.sort-order span').click(() => {
		$('.context-menu.sort-order').toggle();
		if ($(this).hasClass('active')) return;

		$('.sort-order span').removeClass('active');
		$(this).addClass('active');
		VE.tmp.sort_par = $(this).parent('li').attr('entity');
		VE.tmp.sort_as_int = !!$(this).parent('li').attr('sort_as_int');
		VE.tmp.sort_direction = $(this).hasClass('up') * 1 || -1;

		$('.toolbar .sort-by b').html($(this).parent('li').find('.name').html());
		$('.toolbar .sort-by i').removeClass('fa-arrow-up-a-z fa-arrow-down-a-z');
		$(this).hasClass('up')
			? $('.toolbar .sort-by i').addClass('fa-arrow-up-a-z')
			: $('.toolbar .sort-by i').addClass('fa-arrow-down-a-z');
		$('.units .l-unit')
			.sort((a, b) => {
				if (VE.tmp.sort_as_int)
					return parseInt($(a).attr(VE.tmp.sort_par)) >= parseInt($(b).attr(VE.tmp.sort_par))
						? VE.tmp.sort_direction
						: VE.tmp.sort_direction * -1;
				else
					return $(a).attr(VE.tmp.sort_par) <= $(b).attr(VE.tmp.sort_par)
						? VE.tmp.sort_direction
						: VE.tmp.sort_direction * -1;
			})
			.appendTo('.l-center.units');
	});

	$('#objects').submit((evt) => {
		if (!evt.originalEvent) {
			return;
		}
		evt.preventDefault();
		$('.ch-toggle').each(() => {
			if ($(this).prop('checked')) {
				const key = this.name;
				const div = $('<input type="hidden" name="' + key + '" value="' + this.value + '">');
				$('#objects').append(div);
			}
		});

		$('#objects').submit();
		return false;
	});

	$(document).click((evt) => {
		//close notification popup
		if (
			!$(evt.target).hasClass('js-notifications') &&
			$(evt.target).parents('ul.notification-container').length == 0
		) {
			$('.notification-container').addClass('u-hidden');
			$('.js-notifications').removeClass('active');
		}
	});

	// focusing on the first input at form
	if (location.href.indexOf('lead=') == -1 && !$('.ui-dialog').is(':visible')) {
		document
			.querySelector(
				'#vstobjects .form-control:not([disabled]), #vstobjects .form-select:not([disabled])'
			)
			.focus();
	}

	$('.js-notifications').click((_evt) => {
		if (!$('.js-notifications').hasClass('active')) {
			VE.notifications.get_list();
			$('.js-notifications').addClass('active');
		} else {
			$('.notification-container').addClass('u-hidden');
			$('.js-notifications').removeClass('active');
		}
	});

	$('.js-toggle-top-bar-menu').click((_evt) => {
		$(this).siblings('.top-bar-nav-list').toggle();
	});

	$('.button').attr('title', 'ctrl+Enter');
	$('.button.cancel').attr('title', 'ctrl+Backspace');

	VE.core.register();
	if (location.href.search(/list/) != -1) {
		$('body').finderSelect({
			children: '.l-unit',
			onFinish: (_evt) => {
				if (
					$('.l-content').find('.l-unit.selected').length == $('.l-content').find('.l-unit').length
				) {
					$('.toggle-all').addClass('clicked-on');
				}
			},
			toggleAllHook: () => {
				if ($('.l-unit').length == $('.ch-toggle:checked').length) {
					$('.l-unit.selected').removeClass('selected');
					$('.ch-toggle').prop('checked', false);
					$('#toggle-all').prop('checked', false);
				} else {
					$('.ch-toggle').prop('checked', true);
					$('#toggle-all').prop('checked', true);
				}
			},
		});

		$('table').on('mousedown', 'td', (evt) => {
			if (evt.ctrlKey) {
				evt.preventDefault();
			}
		});
	}

	//
	$('form#objects').on('submit', (_evt) => {
		$('.l-unit').find('.ch-toggle').prop('checked', false);
		$('.l-unit.selected').find('.ch-toggle').prop('checked', true);
	});

	document
		.querySelector('.button[data-id=vstobjects][data-action=submit]')
		.addEventListener('click', (evt) => {
			const loaderElement = document.createElement('div');
			loaderElement.classList.add('spinner');
			loaderElement.innerHTML =
				'<div class="spinner-inner"></div><div class="spinner-mask"></div><div class="spinner-mask-two"></div>';

			// this both gives an indication that we've clicked and is loading, also prevents double-clicking/clicking-on-something-else while loading.
			document
				.querySelector('.button[data-id=vstobjects][data-action=submit]')
				.replaceWith(loaderElement);
			document.querySelector('.button').replaceWith('');
			// workaround a render bug on Safari (loading icon doesn't render without this)
			evt.preventDefault();
			document.querySelector('#vstobjects').submit();
		});
});
