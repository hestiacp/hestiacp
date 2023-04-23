// Select all checkbox on list view pages
export default function handleListSelectAll() {
	const selectAllCheckbox = document.querySelector('.js-toggle-all');
	if (selectAllCheckbox) {
		selectAllCheckbox.addEventListener('change', toggleAll);
	}
}

function toggleAll(evt) {
	const isChecked = evt.target.checked;

	document.querySelectorAll('.ch-toggle').forEach((el) => {
		el.checked = isChecked;
	});

	document.querySelectorAll('.l-unit').forEach((el) => {
		el.classList.toggle('selected', isChecked);
	});
}
