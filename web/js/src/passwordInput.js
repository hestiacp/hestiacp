import { randomPassword, generateMailCredentials } from './helpers.js';

// Adds listeners to password inputs (to monitor strength) and generate password buttons
export default function initPasswordInput() {
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

// TODO: Switch to zxcvbn module or something to determine password strength?
function recalculatePasswordStrength(input) {
	const password = input.value;
	const meter = input.parentNode.querySelector('.js-password-meter');
	if (meter) {
		const validations = [
			password.length >= 8, // Min length of 8
			password.search(/[a-z]/) > -1, // Contains 1 lowercase letter
			password.search(/[A-Z]/) > -1, // Contains 1 uppercase letter
			password.search(/\d/) > -1 || password.search(/[^\dA-Za-z]/) > -1, // Contains 1 number or special character
		];
		const strength = validations.reduce((acc, cur) => acc + cur, 0);
		meter.value = strength;
	}
}
