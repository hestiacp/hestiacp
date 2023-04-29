import { debounce } from './helpers';

// Monitor "Account" and "Password" inputs on "Add/Edit Mail Account"
// page and update the sidebar "Account" and "Password" output
export default function handleCopyCreds() {
	monitorAndUpdate('.js-account-input', '.js-account-output');
	monitorAndUpdate('.js-password-input', '.js-password-output');
}

function monitorAndUpdate(inputSelector, outputSelector) {
	const inputElement = document.querySelector(inputSelector);
	const outputElement = document.querySelector(outputSelector);

	if (!inputElement || !outputElement) {
		return;
	}

	function updateOutput(value) {
		outputElement.textContent = value;
		generateMailCredentials();
	}

	inputElement.addEventListener(
		'input',
		debounce((evt) => updateOutput(evt.target.value))
	);
	updateOutput(inputElement.value);
}

// Update hidden input field with values from cloned email info panel
function generateMailCredentials() {
	const mailInfoPanel = document.querySelector('.js-mail-info');

	if (!mailInfoPanel) {
		return;
	}

	const formattedCredentials = emailCredentialsAsPlainText(mailInfoPanel.cloneNode(true));
	document.querySelector('.js-hidden-credentials').value = formattedCredentials;
}

// Reformats cloned DOM email credentials into plain text
function emailCredentialsAsPlainText(element) {
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
