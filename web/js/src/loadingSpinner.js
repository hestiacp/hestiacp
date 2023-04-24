// Attaches listeners to various events and shows loading spinner overlay
export default function handleLoadingSpinner() {
	const pageForm = document.querySelector('#vstobjects');
	if (pageForm) {
		pageForm.addEventListener('submit', showLoader);
	}

	const bulkEditForm = document.querySelector('[x-bind="BulkEdit"]');
	if (bulkEditForm) {
		bulkEditForm.addEventListener('submit', showLoader);
	}
}

function showLoader() {
	document.querySelector('.js-fullscreen-loader').classList.add('active');
}
