import { passwordStrength } from 'check-password-strength';
import { randomPassword, debounce } from './helpers';

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
			const passwordLength =
				document.getElementById('v_password_length') &&
				document.getElementById('v_password_length').value
					? document.getElementById('v_password_length').value
					: 16;
			const passwordOptions = document.querySelector(
				'input.password_options_radios[type=radio]:checked',
			)
				? document.querySelector('input.password_options_radios[type=radio]:checked').value
				: null;
			const passwordSymbols = document.getElementById('v_password_options_symbols')
				? document.getElementById('v_password_options_symbols').value
				: null;
			if (passwordInput) {
				passwordInput.value = randomPassword(+passwordLength, passwordOptions, passwordSymbols);
				passwordInput.dispatchEvent(new Event('input'));
			}
		});
	});

	// Listen for clicks on toggle password options
	document.querySelectorAll('.js-toggle-generate-password').forEach((toggleBtn) => {
		toggleBtn.addEventListener('click', () => {
			const caret = toggleBtn.querySelector('i.fas');
			const panel = document.querySelector('div.password-options');
			if (panel.classList.contains('u-hidden')) {
				panel.classList.add('animate__animated', 'animate__fadeIn');
				panel.classList.remove('u-hidden');
				caret.classList.remove('fa-caret-down');
				caret.classList.add('fa-caret-up');
			} else {
				panel.classList.remove('animate__animated', 'animate__fadeIn');
				panel.classList.add('u-hidden');
				caret.classList.add('fa-caret-down');
				caret.classList.remove('fa-caret-up');
			}
		});
	});
}

function recalculatePasswordStrength(input) {
	const password = input.value;
	const meter = input.parentNode.querySelector('.js-password-meter');

	if (meter) {
		if (password === '') {
			return (meter.value = 0);
		}

		meter.value = passwordStrength(password).id + 1;
	}
}
