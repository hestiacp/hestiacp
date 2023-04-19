// Select all checkbox on list view pages
export default function initListSelectAll() {
	const toggleAll = document.querySelector('.js-toggle-all');
	if (toggleAll) {
		toggleAll.addEventListener('change', handleToggleAllChange);
	}
}

function handleToggleAllChange(evt) {
	const isChecked = evt.target.checked;

	document.querySelectorAll('.ch-toggle').forEach((el) => {
		el.checked = isChecked;
	});

	document.querySelectorAll('.l-unit').forEach((el) => {
		el.classList.toggle('selected', isChecked);
	});
}
