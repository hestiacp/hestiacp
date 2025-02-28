// List view sorting dropdown
export default function handleListSorting() {
	const state = {
		sort_par: 'sort-name',
		sort_direction: -1,
		sort_as_int: false,
	};

	const toggleButton = document.querySelector('.js-toggle-sorting-menu');
	const sortingMenu = document.querySelector('.js-sorting-menu');
	const unitsContainer = document.querySelector('.js-units-container');

	if (!toggleButton || !sortingMenu || !unitsContainer) {
		return;
	}

	// Toggle dropdown button
	toggleButton.addEventListener('click', () => {
		sortingMenu.classList.toggle('u-hidden');
	});

	// "Click outside" to close dropdown
	document.addEventListener('click', (event) => {
		const isClickInside = sortingMenu.contains(event.target) || toggleButton.contains(event.target);
		if (!isClickInside && !sortingMenu.classList.contains('u-hidden')) {
			sortingMenu.classList.add('u-hidden');
		}
	});

	// Inner dropdown sorting behavior
	sortingMenu.querySelectorAll('span').forEach((span) => {
		span.addEventListener('click', function () {
			sortingMenu.classList.add('u-hidden');

			// Skip if the clicked sort is already active
			if (span.classList.contains('active')) {
				return;
			}

			// Remove 'active' class from all spans and add it to the clicked span
			sortingMenu.querySelectorAll('span').forEach((s) => {
				s.classList.remove('active');
			});
			span.classList.add('active');

			// Update state with new sorting parameters
			const parentLi = span.closest('li');
			state.sort_par = parentLi.dataset.entity;
			state.sort_as_int = Boolean(parentLi.dataset.sortAsInt);
			state.sort_direction = span.classList.contains('up') ? 1 : -1;

			// Update toggle button text and icon
			toggleButton.querySelector('span').innerHTML = parentLi.querySelector('.name').innerHTML;
			const faIcon = toggleButton.querySelector('.fas');
			faIcon.classList.remove('fa-arrow-up-a-z', 'fa-arrow-down-a-z');
			faIcon.classList.add(span.classList.contains('up') ? 'fa-arrow-up-a-z' : 'fa-arrow-down-a-z');

			// Sort units and reattach them to the DOM
			const units = Array.from(document.querySelectorAll('.js-unit')).sort((a, b) => {
				const aAttr = a.getAttribute(`data-${state.sort_par}`);
				const bAttr = b.getAttribute(`data-${state.sort_par}`);

				if (state.sort_as_int) {
					const aInt = Number.parseInt(aAttr);
					const bInt = Number.parseInt(bAttr);
					return aInt >= bInt ? state.sort_direction : state.sort_direction * -1;
				}

				return aAttr <= bAttr ? state.sort_direction : state.sort_direction * -1;
			});

			units.forEach((unit) => unitsContainer.appendChild(unit));
		});
	});
}
