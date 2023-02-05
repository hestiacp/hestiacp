document.addEventListener('DOMContentLoaded', () => {
	// Refactored
	const submitLoader = document.querySelector('.button[form=vstobjects][type=submit]');
	if (submitLoader) {
		submitLoader.addEventListener('click', (evt) => {
			const loaderElement = document.createElement('div');
			loaderElement.classList.add('spinner');
			loaderElement.innerHTML =
				'<div class="spinner-inner"></div><div class="spinner-mask"></div><div class="spinner-mask-two"></div>';

			// this both gives an indication that we've clicked and is loading, also prevents double-clicking/clicking-on-something-else while loading.
			document.querySelector('.button[form=vstobjects][type=submit]').replaceWith(loaderElement);
			document.querySelector('.button').replaceWith('');
			// workaround a render bug on Safari (loading icon doesn't render without this)
			evt.preventDefault();
			document.querySelector('#vstobjects').submit();
		});
	}

	document.querySelectorAll('.toolbar-right .sort-by').forEach((el) => {
		el.addEventListener('click', () => $('.context-menu.sort-order').toggle());
	});

	// TODO: Replace with autofocus
	if (document.querySelectorAll('.ui-dialog').length == 0) {
		const input = document.querySelector(
			'#vstobjects .form-control:not([disabled]),\
			#vstobjects .form-select:not([disabled])'
		);
		if (input) {
			input.focus();
		}
	}

	// TODO Refactor or remove
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

	// SORTING
	$('.toolbar-sorting-toggle').click(function (evt) {
		evt.preventDefault();
		$('.toolbar-sorting-menu').toggleClass('u-hidden');
	});

	$('.toolbar-sorting-menu span').click(function () {
		$('.toolbar-sorting-menu').toggleClass('u-hidden');
		if ($(this).hasClass('active')) return;

		$('.toolbar-sorting-menu span').removeClass('active');
		$(this).addClass('active');
		VE.tmp.sort_par = $(this).parent('li').attr('entity');
		VE.tmp.sort_as_int = !!$(this).parent('li').attr('sort_as_int');
		VE.tmp.sort_direction = $(this).hasClass('up') * 1 || -1;

		$('.toolbar-sorting-toggle b').html($(this).parent('li').find('.name').html());
		$('.toolbar-sorting-toggle .fas').removeClass('fa-arrow-up-a-z fa-arrow-down-a-z');
		$(this).hasClass('up')
			? $('.toolbar-sorting-toggle .fas').addClass('fa-arrow-up-a-z')
			: $('.toolbar-sorting-toggle .fas').addClass('fa-arrow-down-a-z');
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
			.appendTo('.units');
	});

	$('.button.cancel').attr('title', 'ctrl+Backspace');

	VE.core.register();
});
