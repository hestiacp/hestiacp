// Select unit behavior
export default function handleListUnitSelect() {
	const checkboxes = Array.from(document.querySelectorAll('.js-unit-checkbox'));
	const units = checkboxes.map((checkbox) => checkbox.closest('.js-unit'));
	const selectAllCheckbox = document.querySelector('.js-toggle-all-checkbox');

	if (checkboxes.length === 0 || !selectAllCheckbox) {
		return;
	}

	let lastCheckedIndex = null;

	checkboxes.forEach((checkbox, index) => {
		checkbox.addEventListener('click', (event) => {
			const isChecked = checkbox.checked;
			updateUnitSelection(units[index], isChecked);

			if (event.shiftKey && lastCheckedIndex !== null) {
				handleMultiSelect(checkboxes, units, index, lastCheckedIndex, isChecked);
			}

			lastCheckedIndex = index;
		});
	});

	selectAllCheckbox.addEventListener('change', () => {
		const isChecked = selectAllCheckbox.checked;
		checkboxes.forEach((checkbox) => (checkbox.checked = isChecked));
		units.forEach((unit) => updateUnitSelection(unit, isChecked));
	});
}

function updateUnitSelection(unit, isChecked) {
	unit.classList.toggle('selected', isChecked);
}

function handleMultiSelect(checkboxes, units, index, lastCheckedIndex, isChecked) {
	const rangeStart = Math.min(index, lastCheckedIndex);
	const rangeEnd = Math.max(index, lastCheckedIndex);

	for (let i = rangeStart; i <= rangeEnd; i++) {
		checkboxes[i].checked = isChecked;
		updateUnitSelection(units[i], isChecked);
	}
}
