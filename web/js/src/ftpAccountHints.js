import { debounce } from './helpers';

// Attach event listeners to FTP account "Username" and "Path" fields to update their hints
export default function handleFtpAccountHints() {
	addHintListeners('.js-ftp-user', updateFtpUsernameHint);
	addHintListeners('.js-ftp-path', updateFtpPathHint);
}

function addHintListeners(selector, updateHintFunction) {
	document.querySelectorAll(selector).forEach((inputElement) => {
		const currentValue = inputElement.value.trim();

		if (currentValue !== '') {
			updateHintFunction(inputElement, currentValue);
		}

		inputElement.addEventListener(
			'input',
			debounce((event) => updateHintFunction(event.target, event.target.value)),
		);
	});
}

function updateFtpUsernameHint(usernameInput, username) {
	const inputWrapper = usernameInput.parentElement;
	const hintElement = inputWrapper.querySelector('.js-ftp-user-hint');

	// Remove special characters
	const sanitizedUsername = username.replace(/[^\w\d]/gi, '');

	if (sanitizedUsername !== username) {
		usernameInput.value = sanitizedUsername;
	}

	hintElement.textContent = Alpine.store('globals').USER_PREFIX + sanitizedUsername;
}

function updateFtpPathHint(pathInput, path) {
	const inputWrapper = pathInput.parentElement;
	const hintElement = inputWrapper.querySelector('.js-ftp-path-hint');
	const normalizedPath = normalizePath(path);

	hintElement.textContent = normalizedPath;
}

function normalizePath(path) {
	// Add leading slash
	if (path[0] !== '/') {
		path = `/${path}`;
	}

	// Remove double slashes
	return path.replace(/\/(\/+)/g, '/');
}
