import { enableUnlimitedInputs } from './unlimitedInput';
import { updateAdvancedTextarea } from './toggleAdvanced';
import { isDesktopSafari, showSpinner } from './helpers';

export default function handleFormSubmit() {
	const mainForm = document.querySelector('#main-form');
	if (mainForm) {
		mainForm.addEventListener('submit', (event) => {
			// Show loading spinner
			showSpinner();

			// Wait a bit if Desktop Safari to ensure spinner is shown
			if (isDesktopSafari()) {
				const submitButton = document.querySelector('button[type="submit"]');
				if (!submitButton.dataset.clicked) {
					event.preventDefault();
					submitButton.dataset.clicked = 'true';
					setTimeout(() => {
						mainForm.submit();
					}, 500);
				}
			}

			// Enable any disabled inputs to ensure all fields are submitted
			if (mainForm.classList.contains('js-enable-inputs-on-submit')) {
				document.querySelectorAll('input[disabled]').forEach((input) => {
					input.disabled = false;
				});
			}

			// Enable any disabled unlimited inputs and set their value to "unlimited"
			enableUnlimitedInputs();

			// Update the "advanced options" textarea with "basic options" input values
			const basicOptionsWrapper = document.querySelector('.js-basic-options');
			if (basicOptionsWrapper && !basicOptionsWrapper.classList.contains('u-hidden')) {
				updateAdvancedTextarea();
			}
		});
	}

	const bulkEditForm = document.querySelector('[x-bind="BulkEdit"]');
	if (bulkEditForm) {
		bulkEditForm.addEventListener('submit', () => {
			// Show loading spinner
			showSpinner();
		});
	}
}
