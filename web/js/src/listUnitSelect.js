// Select unit behavior
export default function handleListUnitSelect() {
	const checkboxes = Array.from(document.querySelectorAll('.js-unit-checkbox'));
	const units = Array.from(document.querySelectorAll('.js-unit'));
	const selectAllCheckbox = document.querySelector('.js-toggle-all-checkbox');

	if (checkboxes.length === 0 || !selectAllCheckbox) {
		return;
	}

	let lastCheckedIndex = null;

	checkboxes.forEach((checkbox, index) => {
		const unit = checkbox.closest('.js-unit');

		checkbox.addEventListener('click', (event) => {
			unit.classList.toggle('selected', event.target.checked);

			// "Hold shift to select multiple" behavior
			if (!event.shiftKey || lastCheckedIndex === null) {
				lastCheckedIndex = index;
				return;
			}

			const rangeStart = Math.min(index, lastCheckedIndex);
			const rangeEnd = Math.max(index, lastCheckedIndex);
			const isChecked = checkboxes[lastCheckedIndex].checked;

			for (let i = rangeStart; i <= rangeEnd; i++) {
				checkboxes[i].checked = isChecked;
				units[i].classList.toggle('selected', isChecked);
			}

			lastCheckedIndex = index;
		});
	});

	selectAllCheckbox.addEventListener('change', (evt) => {
		toggleAll(evt, checkboxes, units);
	});
}

function toggleAll(evt, checkboxes, units) {
	const isChecked = evt.target.checked;

	checkboxes.forEach((el) => {
		el.checked = isChecked;
	});

	units.forEach((el) => {
		el.classList.toggle('selected', isChecked);
	});
}
