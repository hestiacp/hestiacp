import { customAlphabet } from 'nanoid';

// Generates a random password that always passes password requirements
export function randomPassword(length = 16) {
	const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	const lowercase = 'abcdefghijklmnopqrstuvwxyz';
	const numbers = '0123456789';
	const symbols = '!@#$%^&*()_+-=[]{}|;:/?';
	const allCharacters = uppercase + lowercase + numbers + symbols;
	const generate = customAlphabet(allCharacters, length);

	let password;
	do {
		password = generate();
		// Must contain at least one uppercase letter, one lowercase letter, and one number
	} while (!(/[a-z]/.test(password) && /[A-Z]/.test(password) && /\d/.test(password)));

	return password;
}

// Debounces a function to avoid excessive calls
export function debounce(func, wait = 100) {
	let timeout;
	return function (...args) {
		clearTimeout(timeout);
		timeout = setTimeout(() => func.apply(this, args), wait);
	};
}

// Returns the value of a CSS variable
export function getCssVariable(variableName) {
	return getComputedStyle(document.documentElement).getPropertyValue(variableName).trim();
}

// Shows the loading spinner overlay
export function showSpinner() {
	document.querySelector('.js-spinner').classList.add('active');
}

// Parses and sorts IP lists from HTML
export function parseAndSortIpLists(ipListsData) {
	const ipLists = JSON.parse(ipListsData || '[]');
	return ipLists.sort((a, b) => a.name.localeCompare(b.name));
}

// Posts data to the given URL and returns the response
export async function post(url, data, headers = {}) {
	const requestOptions = {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
			...headers,
		},
		body: JSON.stringify(data),
	};

	const response = await fetch(url, requestOptions);
	if (!response.ok) {
		throw new Error(`HTTP error ${response.status}: ${response.statusText}`);
	}

	return response.json();
}

// Creates a confirmation <dialog> on the fly
export function createConfirmationDialog({
	title,
	message = 'Are you sure?',
	targetUrl,
	spinner = false,
}) {
	// Create the dialog
	const dialog = document.createElement('dialog');
	dialog.classList.add('modal');

	// Create and insert the title
	if (title) {
		const titleElement = document.createElement('h2');
		titleElement.innerHTML = title;
		titleElement.classList.add('modal-title');
		dialog.append(titleElement);
	}

	// Create and insert the message
	const messageElement = document.createElement('p');
	messageElement.innerHTML = message;
	messageElement.classList.add('modal-message');
	dialog.append(messageElement);

	// Create and insert the options
	const optionsElement = document.createElement('div');
	optionsElement.classList.add('modal-options');

	const confirmButton = document.createElement('button');
	confirmButton.type = 'submit';
	confirmButton.classList.add('button');
	confirmButton.textContent = 'OK';
	optionsElement.append(confirmButton);

	const cancelButton = document.createElement('button');
	cancelButton.type = 'button';
	cancelButton.classList.add('button', 'button-secondary', 'u-ml5');
	cancelButton.textContent = 'Cancel';
	if (targetUrl) {
		optionsElement.append(cancelButton);
	}

	dialog.append(optionsElement);

	// Define named functions to handle the event listeners
	const handleConfirm = () => {
		if (targetUrl) {
			if (spinner) {
				showSpinner();
			}
			window.location.href = targetUrl;
		}

		handleClose();
	};

	const handleCancel = () => handleClose();
	const handleClose = () => {
		confirmButton.removeEventListener('click', handleConfirm);
		cancelButton.removeEventListener('click', handleCancel);
		dialog.removeEventListener('close', handleClose);
		dialog.remove();
	};

	// Add event listeners
	confirmButton.addEventListener('click', handleConfirm);
	cancelButton.addEventListener('click', handleCancel);
	dialog.addEventListener('close', handleClose);

	// Add to DOM and show
	document.body.append(dialog);
	dialog.showModal();
}
