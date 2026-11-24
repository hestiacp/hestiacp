import { passwordStrength } from 'check-password-strength';
import { debounce, randomPassword } from './helpers';

const initializedElements = new WeakSet();
const initializedDocuments = new WeakSet();

// Adds listeners to password inputs (to monitor strength and toggle visibility) and generate password buttons
export default function handlePasswordInput() {
	// Listen for changes to password inputs and add visibility toggle buttons
	document.querySelectorAll('.js-password-input').forEach((passwordInput) => {
		if (!initializedElements.has(passwordInput)) {
			initializedElements.add(passwordInput);
			passwordInput.addEventListener(
				'input',
				debounce((evt) => recalculatePasswordStrength(evt.target)),
			);
		}

		addPasswordVisibilityToggle(passwordInput);
	});

	// Listen for clicks on visibility toggle buttons (delegated to support cloned templates)
	if (!initializedDocuments.has(document)) {
		initializedDocuments.add(document);
		document.addEventListener('click', (evt) => {
			const toggleButton = evt.target?.closest?.('.js-password-toggle');
			if (!toggleButton?.parentElement) {
				return;
			}

			const passwordInput = toggleButton.parentElement.querySelector('.js-password-input');
			if (!passwordInput) {
				return;
			}

			const showPassword = passwordInput.type === 'password';
			passwordInput.type = showPassword ? 'text' : 'password';
			updatePasswordToggleState(toggleButton, showPassword);

			const passwordOutput = document.querySelector('.js-password-output');
			if (passwordOutput && passwordInput === document.querySelector('.js-password-input')) {
				passwordOutput.type = passwordInput.type;
			}
		});
	}

	// Listen for clicks on generate password buttons and set a new random password
	document.querySelectorAll('.js-generate-password').forEach((generatePasswordButton) => {
		if (initializedElements.has(generatePasswordButton)) {
			return;
		}
		initializedElements.add(generatePasswordButton);

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

function addPasswordVisibilityToggle(passwordInput) {
	const wrapper = passwordInput.parentElement;
	if (!wrapper || wrapper.querySelector('.js-password-toggle')) {
		return;
	}

	wrapper.classList.add('u-pos-relative');

	const toggleButton = document.createElement('button');
	toggleButton.type = 'button';
	toggleButton.className = 'password-toggle js-password-toggle';

	const icon = document.createElement('i');
	icon.setAttribute('aria-hidden', 'true');
	toggleButton.append(icon);

	updatePasswordToggleState(toggleButton, passwordInput.type !== 'password');
	wrapper.append(toggleButton);
}

function updatePasswordToggleState(toggleButton, isVisible) {
	const label = isVisible ? 'Hide password' : 'Show password';
	toggleButton.title = label;
	toggleButton.setAttribute('aria-label', label);

	const icon = toggleButton.querySelector('i');
	if (icon) {
		icon.className = isVisible ? 'fas fa-eye-slash' : 'fas fa-eye';
	}
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
