import { debounce } from './helpers';

// Synchronizes the "Email" input value with "Email login credentials to" input value
// based on the "Send welcome email" checkbox state on Add User page
export default function handleSyncEmailValues() {
	const emailInput = document.querySelector('.js-sync-email-input');
	const sendWelcomeEmailCheckbox = document.querySelector('.js-sync-email-checkbox');
	const emailCredentialsToInput = document.querySelector('.js-sync-email-output');

	if (!emailInput || !sendWelcomeEmailCheckbox || !emailCredentialsToInput) {
		return;
	}

	function syncEmailValues() {
		emailCredentialsToInput.value = sendWelcomeEmailCheckbox.checked ? emailInput.value : '';
	}

	emailInput.addEventListener(
		'input',
		debounce(() => syncEmailValues()),
	);
	sendWelcomeEmailCheckbox.addEventListener('change', syncEmailValues);
}
