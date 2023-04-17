// List view sorting dropdown
export default function initSorting() {
	document.querySelectorAll('.toolbar-sorting-toggle').forEach((toggle) => {
		toggle.addEventListener('click', (evt) => {
			evt.preventDefault();
			document.querySelector('.toolbar-sorting-menu').classList.toggle('u-hidden');
		});
	});

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
			VE.tmp.sort_par = parentLi.getAttribute('entity');
			VE.tmp.sort_as_int = !!parentLi.getAttribute('sort_as_int');
			VE.tmp.sort_direction = this.classList.contains('up') ? 1 : -1;

			const toggle = document.querySelector('.toolbar-sorting-toggle');
			toggle.querySelector('b').innerHTML = parentLi.querySelector('.name').innerHTML;
			const fas = toggle.querySelector('.fas');
			fas.classList.remove('fa-arrow-up-a-z', 'fa-arrow-down-a-z');
			fas.classList.add(this.classList.contains('up') ? 'fa-arrow-up-a-z' : 'fa-arrow-down-a-z');

			const units = Array.from(document.querySelectorAll('.units .l-unit')).sort((a, b) => {
				const aAttr = a.getAttribute(VE.tmp.sort_par);
				const bAttr = b.getAttribute(VE.tmp.sort_par);

				if (VE.tmp.sort_as_int) {
					const aInt = parseInt(aAttr);
					const bInt = parseInt(bAttr);
					return aInt >= bInt ? VE.tmp.sort_direction : VE.tmp.sort_direction * -1;
				} else {
					return aAttr <= bAttr ? VE.tmp.sort_direction : VE.tmp.sort_direction * -1;
				}
			});

			const unitsContainer = document.querySelector('.units');
			units.forEach((unit) => unitsContainer.appendChild(unit));
		});
	});
}
