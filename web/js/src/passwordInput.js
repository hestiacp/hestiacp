import { passwordStrength } from 'check-password-strength';
import { debounce, randomPassword } from './helpers';

// Adds listeners to password inputs (to monitor strength) and generate password buttons
export default function handlePasswordInput() {
	// Listen for changes to password inputs and update the password strength
	document.querySelectorAll('.js-password-input').forEach((passwordInput) => {
		passwordInput.addEventListener(
			'input',
			debounce((evt) => recalculatePasswordStrength(evt.target)),
		);
	});

	// Listen for clicks on generate password buttons and set a new random password
	document.querySelectorAll('.js-generate-password').forEach((generatePasswordButton) => {
		generatePasswordButton.addEventListener('click', () => {
			const passwordInput =
				generatePasswordButton.parentNode.nextElementSibling.querySelector('.js-password-input');
			if (passwordInput) {
				passwordInput.value = randomPassword();
				passwordInput.dispatchEvent(new Event('input'));
			}
		});
	});
}

function recalculatePasswordStrength(input) {
	const password = input.value;
	const meter = input.parentNode.querySelector('.js-password-meter');

	if (meter) {
		if (password === '') {
			meter.value = 0;
			return;
		}

		meter.value = passwordStrength(password).id + 1;
	}
}
