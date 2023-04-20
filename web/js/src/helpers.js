import { nanoid } from 'nanoid';

// Generates a random password using and ensures a number is included
export function randomPassword(length = 16) {
	return nanoid(length) + Math.floor(Math.random() * 10);
}

// Creates a confirmation <dialog> on the fly
export function createConfirmationDialog({ title, message = 'Are you sure?', targetUrl }) {
	// Create the dialog
	const dialog = document.createElement('dialog');
	dialog.classList.add('modal');

	// Create and insert the title
	if (title) {
		const titleElement = document.createElement('h2');
		titleElement.textContent = title;
		titleElement.classList.add('modal-title');
		dialog.append(titleElement);
	}

	// Create and insert the message
	const messageElement = document.createElement('p');
	messageElement.textContent = message;
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

// Monitors an input field for change and updates another selector with the value
export function monitorAndUpdate(inputSelector, outputSelector) {
	const inputElement = document.querySelector(inputSelector);
	const outputElement = document.querySelector(outputSelector);

	if (!inputElement || !outputElement) {
		return;
	}

	function updateOutput(value) {
		outputElement.textContent = value;
		VE.helpers.generateMailCredentials();
	}

	inputElement.addEventListener('input', (event) => {
		updateOutput(event.target.value);
	});
	updateOutput(inputElement.value);
}

// Updates hidden input field with values from cloned email info panel
export function generateMailCredentials() {
	const mailInfoPanel = document.querySelector('.js-mail-info');
	if (!mailInfoPanel) return;
	const formattedCredentials = emailCredentialsAsPlainText(mailInfoPanel.cloneNode(true));
	document.querySelector('.js-hidden-credentials').value = formattedCredentials;
}

// Reformats cloned DOM email credentials into plain text
export function emailCredentialsAsPlainText(element) {
	const headings = [...element.querySelectorAll('h2')];
	const lists = [...element.querySelectorAll('ul')];

	return headings
		.map((heading, index) => {
			const items = [...lists[index].querySelectorAll('li')];

			const itemText = items
				.map((item) => {
					const label = item.querySelector('.values-list-label');
					const value = item.querySelector('.values-list-value');
					const valueLink = value.querySelector('a');

					const valueText = valueLink ? valueLink.href : value.textContent;

					return `${label.textContent}: ${valueText}`;
				})
				.join('\n');

			return `${heading.textContent}\n${itemText}\n`;
		})
		.join('\n');
}
