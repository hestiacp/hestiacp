// Attaches listeners to various events and shows loading spinner overlay
export default function initLoadingSpinner() {
	document.querySelector('#vstobjects')?.addEventListener('submit', showLoader);
	document.querySelector('[x-bind="BulkEdit"]')?.addEventListener('submit', showLoader);
}

function showLoader() {
	document.querySelector('.js-fullscreen-loader').classList.add('active');
}
