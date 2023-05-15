import { createConfirmationDialog } from './helpers';

// Listen to .js-confirm-action links and intercept clicks with a confirmation dialog
export default function handleConfirmAction() {
	document.querySelectorAll('.js-confirm-action').forEach((triggerLink) => {
		triggerLink.addEventListener('click', (evt) => {
			evt.preventDefault();

			const title = triggerLink.dataset.confirmTitle;
			const message = triggerLink.dataset.confirmMessage;
			const targetUrl = triggerLink.getAttribute('href');

			createConfirmationDialog({ title, message, targetUrl, spinner: true });
		});
	});
}
