import { delay, isDesktopSafari, showSpinner } from './helpers';
import { updateAdvancedTextarea } from './toggleAdvanced';
import { enableUnlimitedInputs } from './unlimitedInput';

export default function handleFormSubmit() {
	const mainForm = document.querySelector('#main-form');
	if (mainForm) {
		mainForm.addEventListener('submit', async (event) => {
			event.preventDefault();
			showSpinner();

			// Wait if Desktop Safari to ensure spinner is shown
			if (isDesktopSafari()) {
				await delay(500);
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

			mainForm.submit();
		});
	}

	const bulkEditForm = document.querySelector('[x-bind="BulkEdit"]');
	if (bulkEditForm) {
		bulkEditForm.addEventListener('submit', () => {
			showSpinner();
		});
	}
}
