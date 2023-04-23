// List view sorting dropdown
export default function handleListSorting() {
	let state = {
		sort_par: 'sort-name',
		sort_direction: -1,
		sort_as_int: false,
	};

	// Toggle dropdown button
	document.querySelectorAll('.toolbar-sorting-toggle').forEach((toggle) => {
		toggle.addEventListener('click', (evt) => {
			evt.preventDefault();
			document.querySelector('.toolbar-sorting-menu').classList.toggle('u-hidden');
		});
	});

	// "Click outside" to close dropdown
	document.addEventListener('click', (event) => {
		const toggleButton = document.querySelector('.toolbar-sorting-toggle');
		const dropdown = document.querySelector('.toolbar-sorting-menu');

		if (!dropdown || !toggleButton) return;

		if (
			!dropdown.contains(event.target) &&
			!toggleButton.contains(event.target) &&
			!dropdown.classList.contains('u-hidden')
		) {
			dropdown.classList.add('u-hidden');
		}
	});

	// Inner dropdown sorting behavior
	document.querySelectorAll('.toolbar-sorting-menu span').forEach((span) => {
		span.addEventListener('click', function () {
			const menu = document.querySelector('.toolbar-sorting-menu');
			menu.classList.toggle('u-hidden');

			if (this.classList.contains('active')) return;

			document
				.querySelectorAll('.toolbar-sorting-menu span')
				.forEach((s) => s.classList.remove('active'));
			this.classList.add('active');
			const parentLi = this.closest('li');
			state.sort_par = parentLi.getAttribute('entity');
			state.sort_as_int = !!parentLi.getAttribute('sort_as_int');
			state.sort_direction = this.classList.contains('up') ? 1 : -1;

			const toggle = document.querySelector('.toolbar-sorting-toggle');
			toggle.querySelector('b').innerHTML = parentLi.querySelector('.name').innerHTML;
			const fas = toggle.querySelector('.fas');
			fas.classList.remove('fa-arrow-up-a-z', 'fa-arrow-down-a-z');
			fas.classList.add(this.classList.contains('up') ? 'fa-arrow-up-a-z' : 'fa-arrow-down-a-z');

			const units = Array.from(document.querySelectorAll('.units .l-unit')).sort((a, b) => {
				const aAttr = a.getAttribute(state.sort_par);
				const bAttr = b.getAttribute(state.sort_par);

				if (state.sort_as_int) {
					const aInt = parseInt(aAttr);
					const bInt = parseInt(bAttr);
					return aInt >= bInt ? state.sort_direction : state.sort_direction * -1;
				} else {
					return aAttr <= bAttr ? state.sort_direction : state.sort_direction * -1;
				}
			});

			const unitsContainer = document.querySelector('.units');
			units.forEach((unit) => unitsContainer.appendChild(unit));
		});
	});
}
