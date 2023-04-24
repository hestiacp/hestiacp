import { passwordStrength } from 'check-password-strength';
import { randomPassword, generateMailCredentials } from './helpers.js';

// Adds listeners to password inputs (to monitor strength) and generate password buttons
export default function handlePasswordInput() {
	// Listen for changes to password inputs and update the password strength
	document.querySelectorAll('.js-password-input').forEach((passwordInput) => {
		const updateTimeout = (evt) => {
			clearTimeout(window.frp_usr_tmt);
			window.frp_usr_tmt = setTimeout(() => {
				recalculatePasswordStrength(evt.target);
			}, 100);
		};

		passwordInput.addEventListener('keypress', updateTimeout);
		passwordInput.addEventListener('input', updateTimeout);
	});

	// Listen for clicks on all js-generate-password buttons and generate a password
	document.querySelectorAll('.js-generate-password').forEach((generatePasswordButton) => {
		generatePasswordButton.addEventListener('click', () => {
			const passwordInput =
				generatePasswordButton.parentNode.nextElementSibling.querySelector('.js-password-input');
			if (passwordInput) {
				passwordInput.value = randomPassword();
				passwordInput.dispatchEvent(new Event('input'));
				recalculatePasswordStrength(passwordInput);
				generateMailCredentials();
			}
		});
	});
}

function recalculatePasswordStrength(input) {
	const password = input.value;
	const meter = input.parentNode.querySelector('.js-password-meter');
	if (meter) {
		const strength = passwordStrength(password).id;
		meter.value = strength + 1;
	}
}
