import { createConfirmationDialog } from './helpers.js';

// Adds listeners to .js-confirm-action links and intercepts them with a confirmation dialog
export default function handleConfirmationDialogs() {
	document.querySelectorAll('.js-confirm-action').forEach((triggerLink) => {
		triggerLink.addEventListener('click', (evt) => {
			evt.preventDefault();

			const title = triggerLink.dataset.confirmTitle;
			const message = triggerLink.dataset.confirmMessage;
			const targetUrl = triggerLink.getAttribute('href');

			createConfirmationDialog({ title, message, targetUrl });
		});
	});
}
